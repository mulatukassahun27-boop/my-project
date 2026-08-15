<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM notifications
    WHERE user_id = ? OR user_id IS NULL
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        .sidebar { width: 250px; height: 100vh; background: #003366; position: fixed; left: 0; top: 0; overflow-y: auto; }
        .sidebar h2 { color: white; text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .sidebar a { display: block; color: white; padding: 14px 20px; text-decoration: none; font-size: 14px; }
        .sidebar a:hover  { background: #00509e; }
        .sidebar a.active { background: #00509e; font-weight: bold; }

        .main { margin-left: 250px; padding: 25px; }
        .top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .top-section h1 { color: #003366; }
        .back-btn { background: #374151; color: white; text-decoration: none; padding: 10px 16px; border-radius: 6px; }

        .notification {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-left: 4px solid #003366;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .notif-icon { font-size: 24px; flex-shrink: 0; margin-top: 2px; }
        .notif-body h4 { color: #003366; font-size: 15px; margin-bottom: 5px; }
        .notif-body p  { color: #374151; font-size: 14px; line-height: 1.6; }
        .notif-body .date { color: #6b7280; font-size: 12px; margin-top: 8px; }

        .empty-box {
            background: white;
            border-radius: 10px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .empty-box p { color: #6b7280; font-size: 15px; margin-bottom: 16px; }

        .btn { display: inline-block; padding: 10px 22px; background: #003366; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .btn:hover { background: #00509e; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 10px; }
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
    <a href="notifications.php" class="active">🔔 Notifications</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>🔔 Notifications</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($notifications->num_rows > 0): ?>

        <?php while ($row = $notifications->fetch_assoc()): ?>
            <div class="notification">
                <div class="notif-icon">📢</div>
                <div class="notif-body">
                    <h4><?= htmlspecialchars($row['title']) ?></h4>
                    <p><?= htmlspecialchars($row['message']) ?></p>
                    <p class="date">📅 <?= htmlspecialchars($row['created_at']) ?></p>
                </div>
            </div>
        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty-box">
            <p style="font-size:48px;">🔔</p>
            <p>No notifications yet. Check back later.</p>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>

    <?php endif; ?>

</div>

<footer class="footer">Department Selection System © 2026 — Debre Markos University</footer>

</body>
</html>
