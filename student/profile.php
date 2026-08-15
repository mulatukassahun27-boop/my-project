<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student</title>
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

        .card { background: white; padding: 28px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); max-width: 680px; }
        .card h2 { color: #003366; margin-bottom: 20px; }

        /* Avatar */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .avatar {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: #003366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .profile-name h3 { font-size: 20px; color: #111827; }
        .profile-name p  { color: #6b7280; font-size: 14px; }

        /* Info grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px; }

        .info-item { background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .info-item label { display: block; color: #6b7280; font-size: 12px; margin-bottom: 4px; }
        .info-item strong { color: #111827; font-size: 15px; }

        .status-active  { background: #dcfce7; color: #166534; padding: 3px 10px; border-radius: 20px; font-size: 13px; }
        .status-blocked { background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 20px; font-size: 13px; }

        .btn { display: inline-block; padding: 10px 22px; background: #003366; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; margin: 4px; }
        .btn:hover { background: #00509e; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }

        @media(max-width: 600px) { .info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="profile.php" class="active">👤 My Profile</a>
    <a href="select_department.php">📚 Select Department</a>
    <a href="my_choices.php">✅ My Choices</a>
    <a href="placement_result.php">🎓 Placement Result</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>My Profile</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <div class="card">

        <div class="profile-header">
            <div class="avatar">
                <?= strtoupper(substr($user['full_name'] ?? 'S', 0, 1)) ?>
            </div>
            <div class="profile-name">
                <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="info-grid">

            <div class="info-item">
                <label>Full Name</label>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>

            <div class="info-item">
                <label>Student ID</label>
                <strong><?= htmlspecialchars($user['student_id'] ?: 'Not set') ?></strong>
            </div>

            <div class="info-item">
                <label>Gender</label>
                <strong><?= htmlspecialchars($user['gender'] ?: 'Not set') ?></strong>
            </div>

            <div class="info-item">
                <label>CGPA</label>
                <strong><?= number_format((float)($user['cgpa'] ?? 0), 2) ?></strong>
            </div>

            <div class="info-item">
                <label>Email</label>
                <strong><?= htmlspecialchars($user['email']) ?></strong>
            </div>

            <div class="info-item">
                <label>Phone</label>
                <strong><?= htmlspecialchars($user['phone'] ?: 'Not set') ?></strong>
            </div>

            <div class="info-item">
                <label>Username</label>
                <strong><?= htmlspecialchars($user['username']) ?></strong>
            </div>

            <div class="info-item">
                <label>Account Status</label>
                <span class="<?= $user['status'] === 'Active' ? 'status-active' : 'status-blocked' ?>">
                    <?= htmlspecialchars($user['status']) ?>
                </span>
            </div>

            <div class="info-item">
                <label>Registered On</label>
                <strong><?= htmlspecialchars($user['created_at']) ?></strong>
            </div>

        </div>

        <a href="edit_profile.php" class="btn">✏️ Edit Profile</a>
        <a href="change_password.php" class="btn">🔑 Change Password</a>

    </div>

</div>

<footer class="footer">Department Selection System © 2026 — Debre Markos University</footer>

</body>
</html>
