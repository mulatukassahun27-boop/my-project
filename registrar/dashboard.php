<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar Dashboard - Department Selection System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .header {
            background: #1e3a8a;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            font-size: 22px;
        }

        .logout {
            background: #dc2626;
            color: white;
            text-decoration: none;
            padding: 9px 16px;
            border-radius: 6px;
        }

        .logout:hover {
            background: #b91c1c;
        }

        .container {
            padding: 30px;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .welcome h1 {
            color: #1e3a8a;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }

        .card h3 {
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        .card p {
            color: #666;
            margin-bottom: 18px;
        }

        .card a {
            display: inline-block;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .card a:hover {
            background: #162d6b;
        }

        .footer {
            text-align: center;
            padding: 25px;
            color: #777;
            margin-top: 30px;
        }
    </style>
</head>

<body>

<header class="header">

    <h2>DMU Department Selection System</h2>

    <a href="../logout.php" class="logout">
        Logout
    </a>

</header>


<div class="container">

    <div class="welcome">

        <h1>Registrar Dashboard</h1>

        <p>
            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION['username'] ?? 'Registrar') ?>
            </strong>
        </p>

        <p>
            Manage students, departments, colleges, quotas and placement.
        </p>

        <?php
        $activeYear = $conn->query("SELECT year_name FROM academic_years WHERE status='Active' LIMIT 1")->fetch_assoc();
        ?>
        <?php if ($activeYear): ?>
        <p style="margin-top:10px;">
            <strong>Active Academic Year:</strong>
            <span style="background:#dbeafe;color:#1e3a8a;padding:4px 12px;border-radius:20px;font-weight:bold;">
                <?= htmlspecialchars($activeYear['year_name']) ?>
            </span>
        </p>
        <?php else: ?>
        <p style="margin-top:10px; color:#dc2626;">
            ⚠️ No active academic year set. Ask the Admin to activate one.
        </p>
        <?php endif; ?>

    </div>


    <div class="cards">

        <!-- Students -->

        <div class="card">

            <h3>Students</h3>

            <p>
                Manage registered students.
            </p>

            <a href="manage_students.php">
                Manage Students
            </a>

        </div>


        <!-- Departments -->

        <div class="card">

            <h3>Departments</h3>

            <p>
                Manage university departments.
            </p>

            <a href="manage_departments.php">
                Departments
            </a>

        </div>


        <!-- Colleges -->

        <div class="card">

            <h3>Colleges</h3>

            <p>
                Colleges are managed by the Admin.
            </p>

            <a href="../admin/manage_colleges.php" style="background:#6b7280;">
                View Colleges (Admin)
            </a>

        </div>


        <!-- Academic Year -->

        <div class="card">

            <h3>Academic Year</h3>

            <p>
                Academic years are managed by the Admin.
            </p>

            <a href="../admin/manage_academic_year.php" style="background:#6b7280;">
                View Years (Admin)
            </a>

        </div>


        <!-- Quota -->

        <div class="card">

            <h3>Department Quota</h3>

            <p>
                Manage department capacity.
            </p>

            <a href="manage_quota.php">
                Manage Quota
            </a>

        </div>


        <!-- Reports -->

        <div class="card">

            <h3>Reports</h3>

            <p>
                View selection and placement reports.
            </p>

            <a href="reports.php">
                View Reports
            </a>

        </div>

        <!-- Run Placement -->

        <div class="card">

            <h3>Run Placement</h3>

            <p>
                Execute the student placement algorithm.
            </p>

            <a href="run_placement.php">
                Run Placement
            </a>

        </div>

        <!-- Publish Results -->

        <div class="card">

            <h3>Publish Results</h3>

            <p>
                Publish placement results to students.
            </p>

            <a href="publish_results.php">
                Publish Results
            </a>

        </div>

    </div>

</div>


<footer class="footer">

    Department Selection System © 2026  
    <br>
    Debre Markos University

</footer>

</body>
</html>