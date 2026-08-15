<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

$message      = "";
$message_type = "";

// =====================================================
// RUN PLACEMENT
// =====================================================

if (isset($_POST['run'])) {

    $conn->begin_transaction();

    try {

        // 1. Get active academic year
        $year_query = $conn->query("
            SELECT id, year_name
            FROM academic_years
            WHERE status = 'Active'
            LIMIT 1
        ");

        if (!$year_query || $year_query->num_rows == 0) {
            throw new Exception(
                "No active academic year found. Please ask Admin to activate one."
            );
        }

        $year      = $year_query->fetch_assoc();
        $year_id   = (int)$year['id'];
        $year_name = $year['year_name'];

        // 2. Restore quota seats from previous placement
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
                $dept_id = (int)$old['department_id'];
                $total   = (int)$old['total'];
                $conn->query("
                    UPDATE quotas
                    SET available_seat = available_seat + $dept_id
                    WHERE department_id = $dept_id
                      AND academic_year_id = $year_id
                ");
                // correct: add back $total not $dept_id
                $conn->query("
                    UPDATE quotas
                    SET available_seat = available_seat + $total - $dept_id
                    WHERE department_id = $dept_id
                      AND academic_year_id = $year_id
                ");
            }
        }

        // Clean restore: redo properly
        $conn->query("
            UPDATE quotas q
            JOIN (
                SELECT department_id, COUNT(*) AS cnt
                FROM placements
                WHERE academic_year_id = $year_id
                  AND status = 'Placed'
                  AND department_id IS NOT NULL
                GROUP BY department_id
            ) p ON q.department_id = p.department_id
            SET q.available_seat = q.available_seat + p.cnt
            WHERE q.academic_year_id = $year_id
        ");

        // 3. Delete previous placements for this year
        $conn->query("DELETE FROM placements WHERE academic_year_id = $year_id");

        // 4. Get all students ordered by CGPA
        $students = $conn->query("
            SELECT id, full_name, student_id, cgpa
            FROM users
            WHERE role = 'student'
            ORDER BY cgpa DESC, id ASC
        ");

        if (!$students) {
            throw new Exception("Unable to load students: " . $conn->error);
        }

        $total_students      = 0;
        $placed_students     = 0;
        $not_placed_students = 0;

        // 5. Process each student
        while ($student = $students->fetch_assoc()) {

            $total_students++;
            $student_id = (int)$student['id'];

            // Get choices
            $choice_stmt = $conn->prepare("
                SELECT first_choice, second_choice, third_choice
                FROM student_choices
                WHERE student_id = ?
                LIMIT 1
            ");
            $choice_stmt->bind_param("i", $student_id);
            $choice_stmt->execute();
            $choice = $choice_stmt->get_result()->fetch_assoc();
            $choice_stmt->close();

            // No choices submitted
            if (!$choice) {
                $stmt = $conn->prepare("
                    INSERT INTO placements
                        (student_id, academic_year_id, department_id, status, published)
                    VALUES (?, ?, NULL, 'Not Placed', 'No')
                ");
                $stmt->bind_param("ii", $student_id, $year_id);
                $stmt->execute();
                $stmt->close();
                $not_placed_students++;
                continue;
            }

            $choices  = [
                (int)$choice['first_choice'],
                (int)$choice['second_choice'],
                (int)$choice['third_choice'],
            ];
            $assigned = false;

            foreach ($choices as $dept_id) {

                if ($dept_id <= 0) continue;

                // Lock quota row
                $quota_stmt = $conn->prepare("
                    SELECT id, available_seat
                    FROM quotas
                    WHERE department_id = ?
                      AND academic_year_id = ?
                      AND available_seat > 0
                    LIMIT 1
                    FOR UPDATE
                ");
                $quota_stmt->bind_param("ii", $dept_id, $year_id);
                $quota_stmt->execute();
                $quota = $quota_stmt->get_result()->fetch_assoc();
                $quota_stmt->close();

                if ($quota) {
                    $quota_id = (int)$quota['id'];

                    $p_stmt = $conn->prepare("
                        INSERT INTO placements
                            (student_id, department_id, academic_year_id, status, published)
                        VALUES (?, ?, ?, 'Placed', 'No')
                    ");
                    $p_stmt->bind_param("iii", $student_id, $dept_id, $year_id);
                    $p_stmt->execute();
                    $p_stmt->close();

                    $q_stmt = $conn->prepare("
                        UPDATE quotas
                        SET available_seat = available_seat - 1
                        WHERE id = ? AND available_seat > 0
                    ");
                    $q_stmt->bind_param("i", $quota_id);
                    $q_stmt->execute();
                    $q_stmt->close();

                    $assigned = true;
                    $placed_students++;
                    break;
                }
            }

            if (!$assigned) {
                $stmt = $conn->prepare("
                    INSERT INTO placements
                        (student_id, department_id, academic_year_id, status, published)
                    VALUES (?, NULL, ?, 'Not Placed', 'No')
                ");
                $stmt->bind_param("ii", $student_id, $year_id);
                $stmt->execute();
                $stmt->close();
                $not_placed_students++;
            }
        }

        $conn->commit();

        $message = "Placement completed for <strong>" . htmlspecialchars($year_name) . "</strong>."
            . " Total: $total_students | Placed: $placed_students | Not Placed: $not_placed_students";
        $message_type = "success";

    } catch (Exception $e) {
        $conn->rollback();
        $message      = "Placement failed: " . htmlspecialchars($e->getMessage());
        $message_type = "error";
    }
}

// =====================================================
// ACTIVE YEAR + STATS
// =====================================================

$active_year = null;
$year_result = $conn->query("
    SELECT id, year_name FROM academic_years WHERE status = 'Active' LIMIT 1
");
if ($year_result && $year_result->num_rows > 0) {
    $active_year = $year_result->fetch_assoc();
}

$total_students  = 0;
$placed_count    = 0;
$not_placed_count = 0;

if ($active_year) {
    $year_id = (int)$active_year['id'];
    $stats   = $conn->query("
        SELECT
            COUNT(*)                      AS total,
            SUM(status = 'Placed')        AS placed,
            SUM(status = 'Not Placed')    AS not_placed
        FROM placements
        WHERE academic_year_id = $year_id
    ");
    if ($stats) {
        $row              = $stats->fetch_assoc();
        $total_students   = (int)$row['total'];
        $placed_count     = (int)$row['placed'];
        $not_placed_count = (int)$row['not_placed'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Placement - Registrar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        .header {
            background: #1e3a8a;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 { font-size: 21px; }

        .logout {
            background: #dc2626;
            color: white;
            text-decoration: none;
            padding: 9px 16px;
            border-radius: 6px;
        }

        .logout:hover { background: #b91c1c; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .top-section h1 { color: #1e3a8a; }

        .back-btn {
            background: #374151;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 { font-size: 34px; color: #1e3a8a; margin-bottom: 5px; }
        .stat-card p  { color: #6b7280; font-size: 14px; }

        .card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h2 { color: #1e3a8a; margin-bottom: 18px; }
        .card h3 { color: #1e3a8a; margin-bottom: 14px; }

        .message {
            padding: 14px 18px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .info-box {
            background: #eff6ff;
            border-left: 5px solid #1e3a8a;
            padding: 14px 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 18px;
        }

        .warning {
            background: #fffbeb;
            border-left: 5px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 0 6px 6px 0;
            color: #78350f;
            margin-bottom: 18px;
        }

        ol { margin-left: 22px; line-height: 2.2; color: #374151; margin-bottom: 22px; }

        .run-btn {
            background: #16a34a;
            color: white;
            padding: 14px 36px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .run-btn:hover { background: #15803d; }

        .links a {
            display: inline-block;
            margin-right: 10px;
            padding: 10px 18px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .links a:hover { background: #162d6b; }

        .footer { text-align: center; padding: 25px; color: #777; }

        @media(max-width: 700px) {
            .stats { grid-template-columns: 1fr; }
            .top-section { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
    </style>
</head>
<body>

<header class="header">
    <h2>DMU Department Selection System</h2>
    <a href="../logout.php" class="logout">Logout</a>
</header>

<div class="container">

    <div class="top-section">
        <h1>Run Student Placement</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?= $total_students ?></h3>
            <p>Placement Records</p>
        </div>
        <div class="stat-card">
            <h3><?= $placed_count ?></h3>
            <p>Placed Students</p>
        </div>
        <div class="stat-card">
            <h3><?= $not_placed_count ?></h3>
            <p>Not Placed</p>
        </div>
    </div>

    <!-- Placement Card -->
    <div class="card">
        <h2>Placement Algorithm</h2>

        <?php if ($message !== ''): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($active_year): ?>

            <div class="info-box">
                <strong>Active Academic Year:</strong>
                <?= htmlspecialchars($active_year['year_name']) ?>
            </div>

            <div class="warning">
                <strong>Note:</strong>
                Running placement again will reset all existing placements for
                this academic year. Quota seats will be restored before the new
                process begins.
            </div>

            <p style="margin-bottom:14px;"><strong>How the algorithm works:</strong></p>
            <ol>
                <li>Students are sorted by CGPA from highest to lowest.</li>
                <li>The system checks the student's <strong>1st choice</strong> department.</li>
                <li>If no seat is available, the <strong>2nd choice</strong> is tried.</li>
                <li>If still unavailable, the <strong>3rd choice</strong> is tried.</li>
                <li>Students with no available choice are marked <strong>Not Placed</strong>.</li>
            </ol>

            <form method="POST"
                  onsubmit="return confirm('Run placement for <?= htmlspecialchars($active_year['year_name']) ?>? This will reset existing results.');">
                <button type="submit" name="run" class="run-btn">
                    ▶ Run Placement
                </button>
            </form>

        <?php else: ?>
            <div class="message error">
                No active academic year found. Ask the Admin to activate an
                academic year before running placement.
            </div>
        <?php endif; ?>
    </div>

    <!-- Next Steps -->
    <div class="card">
        <h3>Next Steps</h3>
        <div class="links">
            <a href="manage_quota.php">Check Quota</a>
            <a href="publish_results.php">Publish Results</a>
            <a href="reports.php">View Reports</a>
        </div>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
