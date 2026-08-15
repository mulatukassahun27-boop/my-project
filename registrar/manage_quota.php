<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
*/

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

/*
|--------------------------------------------------------------------------
| Delete Quota
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM quotas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manage_quota.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Add Quota
|--------------------------------------------------------------------------
*/

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $department_id = isset($_POST['department_id'])
        ? (int) $_POST['department_id']
        : 0;

    $academic_year_id = isset($_POST['academic_year_id'])
        ? (int) $_POST['academic_year_id']
        : 0;

    $total_seat = isset($_POST['total_seat'])
        ? (int) $_POST['total_seat']
        : 0;

    if ($department_id <= 0) {
        $error = "Please select a department.";
    } elseif ($academic_year_id <= 0) {
        $error = "Please select an academic year.";
    } elseif ($total_seat <= 0) {
        $error = "Total seats must be greater than 0.";
    } else {

        /*
        | Check if quota already exists
        */

        $check = $conn->prepare("
            SELECT id
            FROM quotas
            WHERE department_id = ?
            AND academic_year_id = ?
        ");

        $check->bind_param(
            "ii",
            $department_id,
            $academic_year_id
        );

        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $error = "A quota already exists for this department and academic year.";

        } else {

            /*
            | Available seats initially equal total seats
            */

            $available_seat = $total_seat;

            $stmt = $conn->prepare("
                INSERT INTO quotas
                (
                    department_id,
                    academic_year_id,
                    total_seat,
                    available_seat
                )
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "iiii",
                $department_id,
                $academic_year_id,
                $total_seat,
                $available_seat
            );

            if ($stmt->execute()) {
                $message = "Quota added successfully.";
            } else {
                $error = "Failed to add quota.";
            }

            $stmt->close();
        }

        $check->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$result = $conn->query("
    SELECT
        id,
        department_name,
        department_code
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Get Academic Years
|--------------------------------------------------------------------------
*/

$academic_years = [];

$result = $conn->query("
    SELECT
        id,
        year_name
    FROM academic_years
    WHERE status = 'Active'
    ORDER BY year_name DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $academic_years[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Get Quotas
|--------------------------------------------------------------------------
*/

$quotas = [];

$result = $conn->query("
    SELECT
        q.id,
        q.department_id,
        q.academic_year_id,
        q.total_seat,
        q.available_seat,
        q.created_at,
        d.department_name,
        d.department_code,
        ay.year_name
    FROM quotas q

    INNER JOIN departments d
        ON q.department_id = d.id

    INNER JOIN academic_years ay
        ON q.academic_year_id = ay.id

    ORDER BY q.id DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $quotas[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Quotas - Department Selection System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .header {
            background: #17365d;
            color: white;
            padding: 20px 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 25px;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #17365d;
            font-weight: bold;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h2 {
            margin-top: 0;
            color: #17365d;
        }

        .form-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        select,
        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .btn {
            margin-top: 22px;
            padding: 11px 20px;
            border: none;
            border-radius: 6px;
            background: #17365d;
            color: white;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #17365d;
            color: white;
        }

        tr:hover {
            background: #f7f7f7;
        }

        .delete {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }

        .delete:hover {
            text-decoration: underline;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #777;
            margin-top: 30px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>
        Manage Quotas
    </h1>

</div>


<div class="container">

    <a href="dashboard.php" class="back">
        ← Dashboard
    </a>


    <?php if ($message): ?>

        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- Add Quota -->

    <div class="card">

        <h2>
            Add Department Quotas
        </h2>

        <form method="POST">

            <div class="form-grid">

                <div>

                    <label>
                        Department
                    </label>

                    <select
                        name="department_id"
                        required
                    >

                        <option value="">
                            Select Department
                        </option>

                        <?php foreach ($departments as $department): ?>

                            <option
                                value="<?= (int)$department['id'] ?>"
                            >

                                <?= htmlspecialchars(
                                    $department['department_name']
                                ) ?>

                                (<?= htmlspecialchars(
                                    $department['department_code']
                                ) ?>)

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label>
                        Academic Year
                    </label>

                    <select
                        name="academic_year_id"
                        required
                    >

                        <option value="">
                            Select Academic Year
                        </option>

                        <?php foreach ($academic_years as $year): ?>

                            <option
                                value="<?= (int)$year['id'] ?>"
                            >

                                <?= htmlspecialchars(
                                    $year['year_name']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label>
                        Total Seats
                    </label>

                    <input
                        type="number"
                        name="total_seat"
                        min="1"
                        placeholder="e.g. 60"
                        required
                    >

                </div>

            </div>


            <button
                type="submit"
                class="btn"
            >

                + Add Quota

            </button>

        </form>

    </div>


    <!-- Quota List -->

    <div class="card">

        <h2>
            Quotas List
        </h2>

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Department</th>

                        <th>Code</th>

                        <th>Academic Year</th>

                        <th>Total Seats</th>

                        <th>Available Seats</th>

                        <th>Created At</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (empty($quotas)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="empty"
                            >

                                No quota records found.

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php $number = 1; ?>

                        <?php foreach ($quotas as $quota): ?>

                            <tr>

                                <td>
                                    <?= $number++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $quota['department_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $quota['department_code']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $quota['year_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int)$quota['total_seat'] ?>
                                </td>

                                <td>
                                    <?= (int)$quota['available_seat'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $quota['created_at']
                                    ) ?>
                                </td>

                                <td>

                                    <a
                                        class="delete"
                                        href="manage_quota.php?delete=<?= (int)$quota['id'] ?>"
                                        onclick="return confirm('Are you sure you want to delete this quota?');"
                                    >

                                        Delete

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<footer>

    Department Selection System © 2026<br>

    Debre Markos University

</footer>


</body>

</html>