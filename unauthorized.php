<?php
require_once 'config/session.php';

// Smart back-link based on role
$role = $_SESSION['role'] ?? '';

$backLink  = 'login.php';
$backLabel = 'Back to Login';

if ($role === 'student') {
    $backLink  = 'student/dashboard.php';
    $backLabel = 'Back to Dashboard';
} elseif ($role === 'registrar') {
    $backLink  = 'registrar/dashboard.php';
    $backLabel = 'Back to Dashboard';
} elseif ($role === 'admin') {
    $backLink  = 'admin/dashboard.php';
    $backLabel = 'Back to Dashboard';
} elseif ($role === 'department_head') {
    $backLink  = 'department/dashboard.php';
    $backLabel = 'Back to Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - Department Selection System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            width: 460px;
            max-width: 90%;
            background: white;
            padding: 45px 40px;
            text-align: center;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .icon { font-size: 60px; margin-bottom: 18px; }

        h1 { color: #dc2626; font-size: 26px; margin-bottom: 14px; }

        p {
            color: #555;
            line-height: 1.7;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .buttons {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn:hover { background: #00509e; }

        .btn.secondary {
            background: #6b7280;
        }

        .btn.secondary:hover { background: #4b5563; }
    </style>
</head>
<body>

<div class="box">

    <div class="icon">🚫</div>

    <h1>Access Denied</h1>

    <p>You do not have permission to access this page.</p>

    <p>If you believe this is an error, please contact the system administrator.</p>

    <div class="buttons">

        <a href="<?= htmlspecialchars($backLink) ?>" class="btn">
            <?= htmlspecialchars($backLabel) ?>
        </a>

        <a href="logout.php" class="btn secondary">
            Logout
        </a>

    </div>

</div>

</body>
</html>
