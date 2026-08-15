<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];

// Get submitted choices
$stmt = $conn->prepare("
    SELECT
        sc.submitted_at,
        d1.department_name AS first_department,
        d1.department_code AS first_code,
        d2.department_name AS second_department,
        d2.department_code AS second_code,
        d3.department_name AS third_department,
        d3.department_code AS third_code
    FROM student_choices sc
    JOIN departments d1 ON sc.first_choice  = d1.id
    JOIN departments d2 ON sc.second_choice = d2.id
    JOIN departments d3 ON sc.third_choice  = d3.id
    WHERE sc.student_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$choice = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get real placement status
$pStmt = $conn->prepare("
    SELECT p.status, p.published, d.department_name
    FROM placements p
    LEFT JOIN departments d ON p.department_id = d.id
    WHERE p.student_id = ?
    LIMIT 1
");
$pStmt->bind_param("i", $user_id);
$pStmt->execute();
$placement = $pStmt->get_result()->fetch_assoc();
$pStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Choices - Student</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #003366;
            position: fixed;
            left: 0; top: 0;
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

        .sidebar a:hover  { background: #00509e; }
        .sidebar a.active { background: #00509e; font-weight: bold; }

        .main { margin-left: 250px; padding: 25px; }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
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
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 22px;
            max-width: 720px;
        }

        .card h2 { color: #003366; margin-bottom: 18px; }
        .card h3 { color: #003366; margin-bottom: 12px; }

        /* Choices list */
        .choice-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
        }

        .choice-num {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #003366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }

        .choice-num.first  { background: #f59e0b; }
        .choice-num.second { background: #6b7280; }
        .choice-num.third  { background: #92400e; color: #fef3c7; }

        .choice-info h4 { color: #111827; font-size: 15px; margin-bottom: 3px; }
        .choice-info p  { color: #6b7280; font-size: 13px; }

        .priority-label {
            margin-left: auto;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .label-first  { background: #fef3c7; color: #92400e; }
        .label-second { background: #f3f4f6; color: #374151; }
        .label-third  { background: #f3f4f6; color: #374151; }

        .submitted-date {
            font-size: 13px;
            color: #6b7280;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }

        /* Status card */
        .status-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .status-placed     { background: #dcfce7; border: 1px solid #86efac; }
        .status-not-placed { background: #fee2e2; border: 1px solid #fca5a5; }
        .status-pending    { background: #fef3c7; border: 1px solid #fde68a; }

        .status-card h3 { font-size: 20px; margin-bottom: 8px; }
        .status-placed h3     { color: #166534; }
        .status-not-placed h3 { color: #991b1b; }
        .status-pending h3    { color: #92400e; }
        .status-card p { color: #555; font-size: 14px; }

        /* Empty */
        .empty-box {
            text-align: center;
            padding: 40px;
        }

        .empty-box p  { color: #6b7280; margin-bottom: 16px; }

        .btn {
            display: inline-block;
            padding: 10px 22px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            margin: 4px;
        }

        .btn:hover  { background: #00509e; }
        .btn.green  { background: #16a34a; }
        .btn.green:hover { background: #15803d; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="profile.php">👤 My Profile</a>
    <a href="select_department.php">📚 Select Department</a>
    <a href="my_choices.php" class="active">✅ My Choices</a>
    <a href="placement_result.php">🎓 Placement Result</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>My Department Choices</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($choice): ?>

        <!-- Choices Card -->
        <div class="card">
            <h2>Submitted Preferences</h2>

            <div class="choice-item">
                <div class="choice-num first">1</div>
                <div class="choice-info">
                    <h4><?= htmlspecialchars($choice['first_department']) ?></h4>
                    <p>Code: <?= htmlspecialchars($choice['first_code']) ?></p>
                </div>
                <span class="priority-label label-first">1st Choice</span>
            </div>

            <div class="choice-item">
                <div class="choice-num second">2</div>
                <div class="choice-info">
                    <h4><?= htmlspecialchars($choice['second_department']) ?></h4>
                    <p>Code: <?= htmlspecialchars($choice['second_code']) ?></p>
                </div>
                <span class="priority-label label-second">2nd Choice</span>
            </div>

            <div class="choice-item">
                <div class="choice-num third">3</div>
                <div class="choice-info">
                    <h4><?= htmlspecialchars($choice['third_department']) ?></h4>
                    <p>Code: <?= htmlspecialchars($choice['third_code']) ?></p>
                </div>
                <span class="priority-label label-third">3rd Choice</span>
            </div>

            <p class="submitted-date">
                📅 Submitted on: <?= htmlspecialchars($choice['submitted_at']) ?>
            </p>
        </div>

        <!-- Placement Status Card -->
        <div class="card">
            <h3>Placement Status</h3>

            <?php if ($placement && $placement['published'] === 'Yes'): ?>

                <?php if ($placement['status'] === 'Placed'): ?>
                    <div class="status-card status-placed">
                        <h3>✅ Placed — <?= htmlspecialchars($placement['department_name']) ?></h3>
                        <p>Your placement result has been published. View details below.</p>
                    </div>
                <?php else: ?>
                    <div class="status-card status-not-placed">
                        <h3>❌ Not Placed</h3>
                        <p>Unfortunately you were not placed in any of your chosen departments. Contact the registrar.</p>
                    </div>
                <?php endif; ?>

                <br>
                <a href="placement_result.php" class="btn green">View Full Result</a>

            <?php else: ?>

                <div class="status-card status-pending">
                    <h3>⏳ Awaiting Results</h3>
                    <p>The registrar has not published placement results yet. Please check back later.</p>
                </div>

            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="card">
            <div class="empty-box">
                <p style="font-size:48px;">📋</p>
                <p>You have not submitted your department choices yet.</p>
                <a href="select_department.php" class="btn green">Select Departments Now</a>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>
        </div>

    <?php endif; ?>

</div>

<footer class="footer">
    Department Selection System © 2026 — Debre Markos University
</footer>

</body>
</html>
