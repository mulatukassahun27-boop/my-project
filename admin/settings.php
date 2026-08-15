<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message     = '';
$messageType = '';

// =====================================================
// UPDATE SETTINGS
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $university = trim($_POST['university_name'] ?? '');
    $system     = trim($_POST['system_name']     ?? '');
    $email      = trim($_POST['email']           ?? '');
    $phone      = trim($_POST['phone']           ?? '');
    $address    = trim($_POST['address']         ?? '');
    $website    = trim($_POST['website']         ?? '');

    if ($university === '' || $system === '') {
        $message     = 'University name and system name are required.';
        $messageType = 'error';

    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message     = 'Please enter a valid email address.';
        $messageType = 'error';

    } else {

        $stmt = $conn->prepare("
            UPDATE settings
            SET university_name = ?,
                system_name     = ?,
                email           = ?,
                phone           = ?,
                address         = ?,
                website         = ?
            WHERE id = 1
        ");

        $stmt->bind_param(
            "ssssss",
            $university,
            $system,
            $email,
            $phone,
            $address,
            $website
        );

        if ($stmt->execute()) {
            $message     = 'Settings updated successfully.';
            $messageType = 'success';
        } else {
            $message     = 'Failed to update settings: ' . $stmt->error;
            $messageType = 'error';
        }

        $stmt->close();
    }
}

// =====================================================
// LOAD CURRENT SETTINGS
// =====================================================

$setting = $conn->query("SELECT * FROM settings WHERE id = 1 LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #333366;
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

        .sidebar a:hover { background: #555599; }
        .sidebar a.active { background: #555599; font-weight: bold; }

        .main { margin-left: 250px; padding: 30px; }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .top-section h1 { color: #333366; }

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

        .card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            max-width: 720px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 { color: #333366; margin-bottom: 22px; }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #333366;
        }

        .form-group textarea { height: 90px; resize: vertical; }

        .save-btn {
            background: #333366;
            color: white;
            border: none;
            padding: 13px 30px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .save-btn:hover { background: #555599; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="create_user.php">Create User</a>
    <a href="manage_roles.php">Manage Roles</a>
    <a href="manage_colleges.php">Manage Colleges</a>
    <a href="manage_academic_year.php">Academic Year</a>
    <a href="settings.php" class="active">Settings</a>
    <a href="contact_messages.php">Contact Messages</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>System Settings</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>University Configuration</h2>

        <form method="POST">

            <div class="form-group">
                <label>University Name <span style="color:red">*</span></label>
                <input type="text" name="university_name"
                       value="<?= htmlspecialchars($setting['university_name'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label>System Name <span style="color:red">*</span></label>
                <input type="text" name="system_name"
                       value="<?= htmlspecialchars($setting['system_name'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($setting['email'] ?? '') ?>"
                       placeholder="contact@dmu.edu.et">
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?= htmlspecialchars($setting['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address"><?= htmlspecialchars($setting['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Website</label>
                <input type="text" name="website"
                       value="<?= htmlspecialchars($setting['website'] ?? '') ?>"
                       placeholder="https://www.dmu.edu.et">
            </div>

            <button type="submit" name="update" class="save-btn">
                Save Settings
            </button>

        </form>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
