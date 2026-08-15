<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];
$message = '';
$msgType = '';

if (isset($_POST['update'])) {

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $gender    = $_POST['gender']          ?? '';

    if ($full_name === '' || $email === '' || $gender === '') {
        $message = 'Please fill all required fields.';
        $msgType = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $msgType = 'error';

    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $emailTaken = $check->get_result()->num_rows > 0;
        $check->close();

        if ($emailTaken) {
            $message = 'That email is already in use by another account.';
            $msgType = 'error';

        } else {
            $update = $conn->prepare("UPDATE users SET full_name = ?, gender = ?, email = ?, phone = ? WHERE id = ?");
            $update->bind_param("ssssi", $full_name, $gender, $email, $phone, $user_id);

            if ($update->execute()) {
                $_SESSION['full_name'] = $full_name;
                $message = 'Profile updated successfully.';
                $msgType = 'success';
            } else {
                $message = 'Update failed. Please try again.';
                $msgType = 'error';
            }
            $update->close();
        }
    }
}

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
    <title>Edit Profile - Student</title>
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

        .card { background: white; padding: 28px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); max-width: 600px; }
        .card h2 { color: #003366; margin-bottom: 22px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 7px; color: #374151; font-size: 14px; }
        .form-group input, .form-group select {
            width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #003366; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .save-btn { background: #003366; color: white; border: none; padding: 13px 30px; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; }
        .save-btn:hover { background: #00509e; }

        .cancel-btn { display: inline-block; margin-left: 10px; padding: 13px 22px; background: #6b7280; color: white; text-decoration: none; border-radius: 6px; font-size: 15px; }
        .cancel-btn:hover { background: #4b5563; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }

        @media(max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
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
        <h1>Edit Profile</h1>
        <a href="profile.php" class="back-btn">← My Profile</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Update Your Information</h2>

        <form method="POST">

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input type="text" name="full_name"
                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Gender <span style="color:red">*</span></label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <option value="Male"   <?= $user['gender'] === 'Male'   ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email <span style="color:red">*</span></label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           placeholder="e.g. 0911234567">
                </div>
            </div>

            <button type="submit" name="update" class="save-btn">Save Changes</button>
            <a href="profile.php" class="cancel-btn">Cancel</a>

        </form>
    </div>

</div>

<footer class="footer">Department Selection System © 2026 — Debre Markos University</footer>

</body>
</html>
