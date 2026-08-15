<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

$message = "";
$message_type = "";


// =====================================================
// RUN PLACEMENT
// =====================================================

if (isset($_POST['run'])) {

    // Start transaction
    $conn->begin_transaction();

    try {

        // -------------------------------------------------
        // 1. Get active academic year
        // -------------------------------------------------

        $year_query = $conn->query("
            SELECT id, year_name
            FROM academic_years
            WHERE status = 'Active'
            LIMIT 1
        ");

        if (!$year_query || $year_query->num_rows == 0) {

            throw new Exception(
                "No active academic year found. Please activate an academic year first."
            );
        }

        $year = $year_query->fetch_assoc();

        $year_id = (int)$year['id'];
        $year_name = $year['year_name'];


        // -------------------------------------------------
        // 2. Restore quota seats from previous placement
        // -------------------------------------------------

        $old_placements = $conn->query("
            SELECT department_id, COUNT(*) AS total
            FROM placements
            WHERE academic_year_id = $year_id
            AND department_id IS NOT NULL
            AND status = 'Placed'
            GROUP BY department_id
        ");

        if ($old_placements) {

            while ($old = $old_placements->fetch_assoc()) {

                $department_id = (int)$old['department_id'];
                $total = (int)$old['total'];

                $conn->query("
                    UPDATE quotas
                    SET available_seat = available_seat + $total
                    WHERE department_id = $department_id
                    AND academic_year_id = $year_id
                ");
            }
        }


        // -------------------------------------------------
        // 3. Delete previous placement results
        // -------------------------------------------------

        $conn->query("
            DELETE FROM placements
            WHERE academic_year_id = $year_id
        ");


        // -------------------------------------------------
        // 4. Get all students
        // -------------------------------------------------

        $students = $conn->query("
            SELECT
                id,
                full_name,
                student_id,
                cgpa
            FROM users
            WHERE role = 'student'
            ORDER BY cgpa DESC, id ASC
        ");

        if (!$students) {
            throw new Exception(
                "Unable to load students: " . $conn->error
            );
        }


        $total_students = 0;
        $placed_students = 0;
        $not_placed_students = 0;


        // -------------------------------------------------
        // 5. Process students according to CGPA
        // -------------------------------------------------

        while ($student = $students->fetch_assoc()) {

            $total_students++;

            $student_id = (int)$student['id'];


            // ---------------------------------------------
            // Get student's choices
            // ---------------------------------------------

            $choice_stmt = $conn->prepare("
                SELECT
                    first_choice,
                    second_choice,
                    third_choice
                FROM student_choices
                WHERE student_id = ?
                LIMIT 1
            ");

            $choice_stmt->bind_param("i", $student_id);
            $choice_stmt->execute();

            $choice_result = $choice_stmt->get_result();

            $choice = $choice_result->fetch_assoc();

            $choice_stmt->close();


            // ---------------------------------------------
            // Student did not submit choices
            // ---------------------------------------------

            if (!$choice) {

                $stmt = $conn->prepare("
                    INSERT INTO placements
                    (
                        student_id,
                        academic_year_id,
                        department_id,
                        status,
                        published
                    )
                    VALUES (?, ?, NULL, 'Not Placed', 'No')
                ");

                $stmt->bind_param(
                    "ii",
                    $student_id,
                    $year_id
                );

                $stmt->execute();
                $stmt->close();

                $not_placed_students++;

                continue;
            }


            // ---------------------------------------------
            // Create preference list
            // ---------------------------------------------

            $choices = array(
                (int)$choice['first_choice'],
                (int)$choice['second_choice'],
                (int)$choice['third_choice']
            );


            $assigned = false;


            // ---------------------------------------------
            // Try first, second and third choice
            // ---------------------------------------------

            foreach ($choices as $department_id) {

                if ($department_id <= 0) {
                    continue;
                }


                // Check available quota
                $quota_stmt = $conn->prepare("
                    SELECT
                        id,
                        available_seat
                    FROM quotas
                    WHERE department_id = ?
                    AND academic_year_id = ?
                    AND available_seat > 0
                    LIMIT 1
                    FOR UPDATE
                ");

                $quota_stmt->bind_param(
                    "ii",
                    $department_id,
                    $year_id
                );

                $quota_stmt->execute();

                $quota_result = $quota_stmt->get_result();

                $quota = $quota_result->fetch_assoc();

                $quota_stmt->close();


                // -----------------------------------------
                // Department has available seat
                // -----------------------------------------

                if ($quota) {

                    $quota_id = (int)$quota['id'];


                    // Insert placement
                    $placement_stmt = $conn->prepare("
                        INSERT INTO placements
                        (
                            student_id,
                            department_id,
                            academic_year_id,
                            status,
                            published
                        )
                        VALUES (?, ?, ?, 'Placed', 'No')
                    ");

                    $placement_stmt->bind_param(
                        "iii",
                        $student_id,
                        $department_id,
                        $year_id
                    );

                    $placement_stmt->execute();

                    $placement_stmt->close();


                    // Reduce available seat
                    $update_quota = $conn->prepare("
                        UPDATE quotas
                        SET available_seat = available_seat - 1
                        WHERE id = ?
                        AND available_seat > 0
                    ");

                    $update_quota->bind_param(
                        "i",
                        $quota_id
                    );

                    $update_quota->execute();

                    $update_quota->close();


                    $assigned = true;

                    $placed_students++;

                    break;
                }
            }


            // ---------------------------------------------
            // Student could not be placed
            // ---------------------------------------------

            if (!$assigned) {

                $stmt = $conn->prepare("
                    INSERT INTO placements
                    (
                        student_id,
                        department_id,
                        academic_year_id,
                        status,
                        published
                    )
                    VALUES (?, NULL, ?, 'Not Placed', 'No')
                ");

                $stmt->bind_param(
                    "ii",
                    $student_id,
                    $year_id
                );

                $stmt->execute();

                $stmt->close();

                $not_placed_students++;
            }
        }


        // -------------------------------------------------
        // Commit all changes
        // -------------------------------------------------

        $conn->commit();


        $message =
            "Placement completed successfully for academic year "
            . htmlspecialchars($year_name)
            . ". Total Students: "
            . $total_students
            . " | Placed: "
            . $placed_students
            . " | Not Placed: "
            . $not_placed_students;

        $message_type = "success";


    } catch (Exception $e) {

        // Cancel everything if an error occurs
        $conn->rollback();

        $message = "Placement failed: " . $e->getMessage();

        $message_type = "error";
    }
}


// =====================================================
// GET ACTIVE YEAR FOR DISPLAY
// =====================================================

$active_year = null;

$year_result = $conn->query("
    SELECT id, year_name
    FROM academic_years
    WHERE status = 'Active'
    LIMIT 1
");

if ($year_result && $year_result->num_rows > 0) {
    $active_year = $year_result->fetch_assoc();
}


// =====================================================
// GET PLACEMENT STATISTICS
// =====================================================

$total_students = 0;
$placed_count = 0;
$not_placed_count = 0;

if ($active_year) {

    $year_id = (int)$active_year['id'];

    $stats = $conn->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'Placed') AS placed,
            SUM(status = 'Not Placed') AS not_placed
        FROM placements
        WHERE academic_year_id = $year_id
    ");

    if ($stats) {

        $row = $stats->fetch_assoc();

        $total_students = (int)$row['total'];
        $placed_count = (int)$row['placed'];
        $not_placed_count = (int)$row['not_placed'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Run Placement - Registrar</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #006633;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            padding: 20px;
            margin: 0;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 14px 20px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #00994d;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            margin-bottom: 25px;
        }

        .card h2 {
            margin-top: 0;
            color: #006633;
        }

        .info-box {
            background: #f0f8f4;
            border-left: 5px solid #006633;
            padding: 15px;
            margin: 20px 0;
        }

        .warning {
            background: #fff4d6;
            border-left: 5px solid #e0a800;
            padding: 15px;
            margin: 20px 0;
        }

        .success {
            background: #e7f7ed;
            border-left: 5px solid #198754;
            color: #146c43;
            padding: 15px;
            margin-bottom: 20px;
        }

        .error {
            background: #fde8e8;
            border-left: 5px solid #dc3545;
            color: #842029;
            padding: 15px;
            margin-bottom: 20px;
        }

        .run-button {
            background: #006633;
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            cursor: pointer;
        }

        .run-button:hover {
            background: #004d26;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            color: #006633;
        }

        .stat-card p {
            margin-bottom: 0;
            color: #666;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            display: inline-block;
            margin-right: 10px;
            padding: 10px 16px;
            background: #006633;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .links a:hover {
            background: #00994d;
        }

        @media(max-width: 800px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

    <h2>Registrar Panel</h2>

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="manage_students.php">
        Manage Students
    </a>

    <a href="manage_departments.php">
        Manage Departments
    </a>

    <a href="manage_quota.php">
        Manage Quota
    </a>

    <a href="run_placement.php">
        Run Placement
    </a>

    <a href="publish_results.php">
        Publish Results
    </a>

    <a href="reports.php">
        Reports
    </a>

    <a href="../logout.php">
        Logout
    </a>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main">


    <div class="card">

        <h2>
            Run Student Placement
        </h2>

        <hr>


        <?php if ($message != ""): ?>

            <div class="<?php echo $message_type; ?>">

                <?php echo $message; ?>

            </div>

        <?php endif; ?>


        <?php if ($active_year): ?>

            <div class="info-box">

                <strong>Active Academic Year:</strong>

                <?php
                echo htmlspecialchars(
                    $active_year['year_name']
                );
                ?>

            </div>


            <div class="warning">

                <strong>Important:</strong>

                Running placement again will recalculate
                placements for the active academic year.
                Existing quota seats are restored before
                the new placement process starts.

            </div>


            <p>
                The placement algorithm works as follows:
            </p>

            <ol>

                <li>
                    Students are ordered by CGPA from
                    highest to lowest.
                </li>

                <li>
                    The system checks the student's
                    first-choice department.
                </li>

                <li>
                    If no seat is available, the
                    second choice is checked.
                </li>

                <li>
                    If the second choice is unavailable,
                    the third choice is checked.
                </li>

                <li>
                    Students without an available choice
                    are marked <strong>Not Placed</strong>.
                </li>

            </ol>


            <form method="POST"
                  onsubmit="return confirm(
                    'Are you sure you want to run placement?'
                  );">

                <button
                    type="submit"
                    name="run"
                    class="run-button">

                    Run Placement

                </button>

            </form>

        <?php else: ?>

            <div class="error">

                No active academic year is available.

                Please go to
                <strong>Manage Academic Year</strong>
                and activate an academic year first.

            </div>

        <?php endif; ?>

    </div>


    <!-- =================================================
         STATISTICS
    ================================================== -->

    <div class="stats">

        <div class="stat-card">

            <h3>
                <?php echo $total_students; ?>
            </h3>

            <p>
                Placement Records
            </p>

        </div>


        <div class="stat-card">

            <h3>
                <?php echo $placed_count; ?>
            </h3>

            <p>
                Placed Students
            </p>

        </div>


        <div class="stat-card">

            <h3>
                <?php echo $not_placed_count; ?>
            </h3>

            <p>
                Not Placed
            </p>

        </div>

    </div>


    <!-- =================================================
         QUICK LINKS
    ================================================== -->

    <div class="card">

        <h3>Next Steps</h3>

        <div class="links">

            <a href="manage_quota.php">
                Check Quota
            </a>

            <a href="publish_results.php">
                Publish Results
            </a>

            <a href="reports.php">
                View Reports
            </a>

        </div>

    </div>


</div>

</body>

</html>