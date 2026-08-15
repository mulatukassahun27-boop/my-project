<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$user_id = (int)$_SESSION['user_id'];
$message = '';
$msgType = '';

// Check if already submitted
$check = $conn->prepare("SELECT id FROM student_choices WHERE student_id = ? LIMIT 1");
$check->bind_param("i", $user_id);
$check->execute();
$already = $check->get_result()->num_rows > 0;
$check->close();

// Submit choices
if (isset($_POST['submit']) && !$already) {

    $choice1 = (int)($_POST['choice1'] ?? 0);
    $choice2 = (int)($_POST['choice2'] ?? 0);
    $choice3 = (int)($_POST['choice3'] ?? 0);

    if ($choice1 === 0 || $choice2 === 0 || $choice3 === 0) {
        $message = 'Please select all three department choices.';
        $msgType = 'error';
    } elseif ($choice1 === $choice2 || $choice1 === $choice3 || $choice2 === $choice3) {
        $message = 'Each department choice must be different.';
        $msgType = 'error';
    } else {
        $insert = $conn->prepare("
            INSERT INTO student_choices (student_id, first_choice, second_choice, third_choice)
            VALUES (?, ?, ?, ?)
        ");
        $insert->bind_param("iiii", $user_id, $choice1, $choice2, $choice3);

        if ($insert->execute()) {
            $message = 'Department choices submitted successfully!';
            $msgType = 'success';
            $already = true;
        } else {
            $message = 'Submission failed. Please try again.';
            $msgType = 'error';
        }
        $insert->close();
    }
}

// Load active departments
$departments = [];
$deptResult  = $conn->query("SELECT id, department_name, department_code, capacity FROM departments WHERE status = 'Active' ORDER BY department_name ASC");
while ($row = $deptResult->fetch_assoc()) {
    $departments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Department - Student</title>
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

        .sidebar a:hover        { background: #00509e; }
        .sidebar a.active       { background: #00509e; font-weight: bold; }

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
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 680px;
        }

        .card h2 { color: #003366; margin-bottom: 8px; }

        .card .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #374151;
            font-size: 15px;
        }

        .form-group label span {
            background: #003366;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-right: 6px;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }

        .form-group select:focus {
            outline: none;
            border-color: #003366;
        }

        .submit-btn {
            background: #16a34a;
            color: white;
            border: none;
            padding: 13px 32px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
        }

        .submit-btn:hover { background: #15803d; }

        .already-box {
            background: #dcfce7;
            border: 2px solid #86efac;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
        }

        .already-box h3 { color: #166534; margin-bottom: 10px; }
        .already-box p  { color: #555; margin-bottom: 16px; }

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

        .btn:hover { background: #00509e; }
        .btn.green { background: #16a34a; }
        .btn.green:hover { background: #15803d; }

        .info-note {
            background: #eff6ff;
            border-left: 4px solid #003366;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 22px;
            font-size: 14px;
            color: #374151;
        }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Student Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="profile.php">👤 My Profile</a>
    <a href="select_department.php" class="active">📚 Select Department</a>
    <a href="my_choices.php">✅ My Choices</a>
    <a href="placement_result.php">🎓 Placement Result</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>Department Selection</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">

        <?php if ($already): ?>

            <div class="already-box">
                <h3>✅ Choices Already Submitted</h3>
                <p>You have already submitted your department preferences. You cannot change them after submission.</p>
                <a href="my_choices.php" class="btn green">View My Choices</a>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

        <?php else: ?>

            <h2>Choose Your Departments</h2>
            <p class="subtitle">Select your top 3 preferred departments in order of priority.</p>

            <div class="info-note">
                ⚠️ <strong>Important:</strong> You can only submit once. Choose carefully — you cannot change your selections after submission.
            </div>

            <form method="POST">

                <div class="form-group">
                    <label><span>1</span> First Choice (Highest Priority)</label>
                    <select name="choice1" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept['id'] ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                                (<?= htmlspecialchars($dept['department_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><span>2</span> Second Choice</label>
                    <select name="choice2" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept['id'] ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                                (<?= htmlspecialchars($dept['department_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><span>3</span> Third Choice (Lowest Priority)</label>
                    <select name="choice3" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept['id'] ?>">
                                <?= htmlspecialchars($dept['department_name']) ?>
                                (<?= htmlspecialchars($dept['department_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="submit" class="submit-btn"
                        onclick="return confirm('Submit your department choices? You cannot change them after submission.');">
                    Submit My Choices
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>

<footer class="footer">
    Department Selection System © 2026 — Debre Markos University
</footer>

</body>
</html>
