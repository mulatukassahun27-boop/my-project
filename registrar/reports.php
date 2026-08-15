<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

// =====================================================
// STATS
// =====================================================

$total_students = $conn->query(
    "SELECT COUNT(*) AS c FROM users WHERE role = 'student'"
)->fetch_assoc()['c'] ?? 0;

$placed = $conn->query(
    "SELECT COUNT(*) AS c FROM placements WHERE status = 'Placed'"
)->fetch_assoc()['c'] ?? 0;

$not_placed = $conn->query(
    "SELECT COUNT(*) AS c FROM placements WHERE status = 'Not Placed'"
)->fetch_assoc()['c'] ?? 0;

// =====================================================
// DEPARTMENT REPORT
// =====================================================

$departments = $conn->query("
    SELECT
        d.department_name,
        d.department_code,
        COUNT(p.id) AS total_placed
    FROM departments d
    LEFT JOIN placements p
        ON d.id = p.department_id
       AND p.status = 'Placed'
    GROUP BY d.id, d.department_name, d.department_code
    ORDER BY total_placed DESC
");

// =====================================================
// CHOICES SUBMITTED
// =====================================================

$choices_submitted = $conn->query(
    "SELECT COUNT(*) AS c FROM student_choices"
)->fetch_assoc()['c'] ?? 0;

$no_choices = $total_students - $choices_submitted;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Reports - Registrar</title>
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

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 { font-size: 34px; color: #1e3a8a; margin-bottom: 6px; }
        .stat-card p  { color: #6b7280; }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 { color: #1e3a8a; margin-bottom: 18px; }

        .print-btn {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .print-btn:hover { background: #162d6b; }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: #1e3a8a;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }

        tr:hover { background: #f9fafb; }

        .progress-bar-wrap {
            background: #e5e7eb;
            border-radius: 20px;
            height: 12px;
            min-width: 100px;
        }

        .progress-bar {
            background: #1e3a8a;
            height: 12px;
            border-radius: 20px;
        }

        .footer { text-align: center; padding: 25px; color: #777; }

        @media print {
            .header, .back-btn, .print-btn { display: none; }
            .container { margin: 0; }
        }

        @media(max-width: 900px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
        }

        @media(max-width: 600px) {
            .stats { grid-template-columns: 1fr; }
            .top-section { flex-direction: column; align-items: flex-start; gap: 15px; }
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
        <h1>Placement Reports</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <!-- Summary Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?= (int)$total_students ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$placed ?></h3>
            <p>Placed</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$not_placed ?></h3>
            <p>Not Placed</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$choices_submitted ?></h3>
            <p>Submitted Choices</p>
        </div>
    </div>

    <!-- Department Report -->
    <div class="card">
        <h2>Department Placement Summary</h2>

        <button class="print-btn" onclick="window.print()">
            Print Report
        </button>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Students Placed</th>
                    <th>Distribution</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($departments && $departments->num_rows > 0): ?>
                <?php $i = 1; ?>
                <?php while ($dept = $departments->fetch_assoc()): ?>
                    <?php
                    $pct = $placed > 0
                        ? round(($dept['total_placed'] / $placed) * 100)
                        : 0;
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($dept['department_name']) ?></strong></td>
                        <td><?= htmlspecialchars($dept['department_code']) ?></td>
                        <td><?= (int)$dept['total_placed'] ?></td>
                        <td>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                            <small><?= $pct ?>%</small>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:#777;">
                        No department data found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
