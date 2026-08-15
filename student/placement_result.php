<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$userId = (int)$_SESSION['user_id'];

// Only show result if it has been published
$stmt = $conn->prepare("
    SELECT
        p.status,
        p.published,
        p.placed_at,
        d.department_name,
        d.department_code,
        ay.year_name AS academic_year
    FROM placements p
    LEFT JOIN departments d
        ON p.department_id = d.id
    LEFT JOIN academic_years ay
        ON p.academic_year_id = ay.id
    WHERE p.student_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$placement = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Only expose result if published
$resultVisible = $placement && $placement['published'] === 'Yes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Result - Student</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #003366;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 14px;
        }

        .sidebar a:hover { background: #00509e; }

        .main { margin-left: 250px; padding: 25px; }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-section h1 { color: #003366; }

        .back-btn {
            background: #374151;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 700px;
        }

        .card h2 { color: #003366; margin-bottom: 20px; }

        /* Result Banner */
        .result-banner {
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
        }

        .result-banner.placed {
            background: #dcfce7;
            border: 2px solid #86efac;
        }

        .result-banner.not-placed {
            background: #fee2e2;
            border: 2px solid #fca5a5;
        }

        .result-banner h3 { font-size: 24px; margin-bottom: 8px; }
        .result-banner.placed h3 { color: #166534; }
        .result-banner.not-placed h3 { color: #991b1b; }

        .result-banner p { color: #555; }

        table { width: 100%; border-collapse: collapse; margin-top: 16px; }

        table td {
            padding: 13px;
            border-bottom: 1px solid #eee;
        }

        table td:first-child {
            font-weight: bold;
            color: #374151;
            width: 200px;
            background: #f8fafc;
        }

        .pending-box {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
        }

        .pending-box h3 { color: #92400e; margin-bottom: 10px; }
        .pending-box p  { color: #6b7280; }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .btn:hover { background: #00509e; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="profile.php">👤 My Profile</a>
    <a href="select_department.php">📚 Select Department</a>
    <a href="my_choices.php">✅ My Choices</a>
    <a href="placement_result.php">🎓 Placement Result</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>Placement Result</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <div class="card">

        <?php if ($resultVisible): ?>

            <?php if ($placement['status'] === 'Placed'): ?>

                <div class="result-banner placed">
                    <h3>Congratulations! You have been placed.</h3>
                    <p>Your department assignment is shown below.</p>
                </div>

                <table>
                    <tr>
                        <td>Assigned Department</td>
                        <td><strong><?= htmlspecialchars($placement['department_name'] ?? 'N/A') ?></strong></td>
                    </tr>
                    <tr>
                        <td>Department Code</td>
                        <td><?= htmlspecialchars($placement['department_code'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td>Academic Year</td>
                        <td><?= htmlspecialchars($placement['academic_year'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td>Placement Status</td>
                        <td style="color:#166534; font-weight:bold;">Placed</td>
                    </tr>
                    <?php if (!empty($placement['placed_at'])): ?>
                    <tr>
                        <td>Placement Date</td>
                        <td><?= htmlspecialchars($placement['placed_at']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

            <?php else: ?>

                <div class="result-banner not-placed">
                    <h3>Not Placed</h3>
                    <p>
                        Unfortunately, you could not be placed in any of your
                        selected departments. Please contact the registrar's office
                        for further guidance.
                    </p>
                </div>

                <table>
                    <tr>
                        <td>Academic Year</td>
                        <td><?= htmlspecialchars($placement['academic_year'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td>Placement Status</td>
                        <td style="color:#991b1b; font-weight:bold;">Not Placed</td>
                    </tr>
                </table>

            <?php endif; ?>

        <?php elseif ($placement && $placement['published'] !== 'Yes'): ?>

            <div class="pending-box">
                <h3>Results Not Yet Published</h3>
                <p>
                    The placement process has been completed but results have
                    not been published yet. Please check back later.
                </p>
            </div>

        <?php else: ?>

            <div class="pending-box">
                <h3>Placement Result Not Available</h3>
                <p>
                    The registrar has not completed the placement process yet.
                    Make sure you have submitted your department choices.
                </p>
            </div>

        <?php endif; ?>

        <a href="dashboard.php" class="btn">← Back to Dashboard</a>

    </div>

</div>

</body>
</html>
