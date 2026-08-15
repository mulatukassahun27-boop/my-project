<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

$message = '';
$messageType = '';

// =====================================================
// PUBLISH ALL RESULTS
// =====================================================

if (isset($_POST['publish'])) {

    $update = $conn->query("UPDATE placements SET published = 'Yes'");

    if ($update) {
        $message = 'Placement results published successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to publish results.';
        $messageType = 'error';
    }
}

// =====================================================
// GET RESULTS
// =====================================================

$results = $conn->query("
    SELECT
        p.id,
        u.full_name,
        u.student_id,
        u.cgpa,
        d.department_name,
        p.status,
        p.published
    FROM placements p
    JOIN users u
        ON p.student_id = u.id
    LEFT JOIN departments d
        ON p.department_id = d.id
    ORDER BY u.cgpa DESC
");

// =====================================================
// STATS
// =====================================================

$totalResult   = $conn->query("SELECT COUNT(*) AS c FROM placements")->fetch_assoc()['c'] ?? 0;
$publishedCount = $conn->query("SELECT COUNT(*) AS c FROM placements WHERE published = 'Yes'")->fetch_assoc()['c'] ?? 0;
$pendingCount  = $totalResult - $publishedCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Results - Registrar</title>
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

        .message {
            padding: 14px 18px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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

        .publish-btn {
            background: #16a34a;
            color: white;
            border: none;
            padding: 13px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .publish-btn:hover { background: #15803d; }

        .table-container { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; min-width: 750px; }

        th {
            background: #1e3a8a;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }

        tr:hover { background: #f9fafb; }

        .placed     { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 13px; }
        .not-placed { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 13px; }
        .published  { background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: 13px; }
        .pending    { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-size: 13px; }

        .footer { text-align: center; padding: 25px; color: #777; }

        @media(max-width: 700px) {
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
        <h1>Publish Placement Results</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?= (int)$totalResult ?></h3>
            <p>Total Placement Records</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$publishedCount ?></h3>
            <p>Published</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$pendingCount ?></h3>
            <p>Pending Publication</p>
        </div>
    </div>

    <!-- Publish Action -->
    <div class="card">
        <h2>Publish All Results</h2>

        <p style="margin-bottom:16px; color:#555;">
            Clicking <strong>Publish All Results</strong> will make placement
            results visible to all students. Make sure the placement has been
            run before publishing.
        </p>

        <form method="POST"
              onsubmit="return confirm('Publish all placement results to students?');">
            <button type="submit" name="publish" class="publish-btn">
                Publish All Results
            </button>
        </form>
    </div>

    <!-- Results Table -->
    <div class="card">
        <h2>Placement Records</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>CGPA</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Published</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($results && $results->num_rows > 0): ?>
                    <?php $i = 1; ?>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= number_format((float)$row['cgpa'], 2) ?></td>
                            <td><?= htmlspecialchars($row['department_name'] ?? 'Not Assigned') ?></td>
                            <td>
                                <?php if ($row['status'] === 'Placed'): ?>
                                    <span class="placed">Placed</span>
                                <?php else: ?>
                                    <span class="not-placed">Not Placed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['published'] === 'Yes'): ?>
                                    <span class="published">Published</span>
                                <?php else: ?>
                                    <span class="pending">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:#777;">
                            No placement records found. Run placement first.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
