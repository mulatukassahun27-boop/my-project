<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];
$message = '';
$msgType = '';

if (isset($_POST['change_password'])) {

    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']       ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($current, $user['password'])) {
        $message = 'Current password is incorrect.';
        $msgType = 'error';

    } elseif (strlen($new) < 8) {
        $message = 'New password must be at least 8 characters.';
        $msgType = 'error';

    } elseif ($new !== $confirm) {
        $message = 'New passwords do not match.';
        $msgType = 'error';

    } else {

        $hash   = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hash, $user_id);

        if ($update->execute()) {
            $message = 'Password changed successfully.';
            $msgType = 'success';
        } else {
            $message = 'Password change failed. Please try again.';
            $msgType = 'error';
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Student</title>
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

        .message { padding: 14px 18px; border-radius: 7px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .card { background: white; padding: 28px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); max-width: 500px; }
        .card h2 { color: #003366; margin-bottom: 22px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 7px; color: #374151; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #003366; }
        .form-group .hint { font-size: 12px; color: #6b7280; margin-top: 4px; }

        .save-btn { width: 100%; background: #003366; color: white; border: none; padding: 13px; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; margin-top: 6px; }
        .save-btn:hover { background: #00509e; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }
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
    <a href="change_password.php" class="active">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>Change Password</h1>
        <a href="profile.php" class="back-btn">← My Profile</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Set a New Password</h2>

        <form method="POST">

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required placeholder="Enter current password">
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="At least 8 characters" minlength="8">
                <p class="hint">Must be at least 8 characters long.</p>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="Repeat new password" minlength="8">
            </div>

            <button type="submit" name="change_password" class="save-btn">
                Change Password
            </button>

        </form>
    </div>

</div>

<footer class="footer">Department Selection System © 2026 — Debre Markos University</footer>

</body>
</html>
