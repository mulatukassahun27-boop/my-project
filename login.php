<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter username and password.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, full_name, password, role 
             FROM users 
             WHERE username = ? 
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // Store login information
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = strtolower(trim($user['role']));

                // Role-based redirect
                switch ($_SESSION['role']) {

                    case 'student':
                        header("Location: student/dashboard.php");
                        exit();

                    case 'registrar':
                        header("Location: registrar/dashboard.php");
                        exit();

                    case 'department_head':
                    case 'department head':
                        header("Location: department_head/dashboard.php");
                        exit();

                    case 'advisor':
                    case 'academic_advisor':
                    case 'academic advisor':
                        header("Location: advisor/dashboard.php");
                        exit();

                    case 'admin':
                    case 'administrator':
                        header("Location: admin/dashboard.php");
                        exit();

                    default:
                        $error = "Your account role is not configured correctly.";
                }

            } else {
                $error = "Incorrect password.";
            }

        } else {
            $error = "Username not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Department Selection System</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 400px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #1e3a8a;
        }

        .login-container p {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e3a8a;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #1e3a8a;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-btn:hover {
            background: #162d6b;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 18px;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #1e3a8a;
            text-decoration: none;
            font-weight: bold;
        }

        .back-home {
            text-align: center;
            margin-top: 15px;
        }

        .back-home a {
            color: #555;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-container">

    <h2>Department Selection System</h2>

    <p>Login to your account</p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                required
            >
        </div>

        <button type="submit" class="login-btn">
            Login
        </button>

    </form>

    <div class="register-link">
        Don't have an account?
        <a href="register.php">Register</a>
    </div>

    <div class="back-home">
        <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <div class="back-home">
        <a href="index.php">← Back to Home</a>
    </div>

</div>

</body>
</html>