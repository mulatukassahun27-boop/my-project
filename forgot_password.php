<?php
require_once 'config/database.php';

$message     = '';
$messageType = '';
$step        = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// =====================================================
// STEP 1 — Verify email + student ID
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {

    $email      = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');

    if ($email === '' || $student_id === '') {
        $message     = 'Please fill in all fields.';
        $messageType = 'error';
        $step        = 1;

    } else {

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
              AND student_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            $message     = 'No account found with that email and student ID combination.';
            $messageType = 'error';
            $step        = 1;
        } else {
            // Store verified identity in session so step 2 can use it
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['reset_email']      = $email;
            $_SESSION['reset_verified']   = true;
            $step = 2;
        }
    }
}

// =====================================================
// STEP 2 — Set new password
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_email'])) {
        $message     = 'Session expired. Please start over.';
        $messageType = 'error';
        $step        = 1;

    } else {

        $new_password     = $_POST['password']      ?? '';
        $confirm_password = $_POST['confirm']        ?? '';
        $email            = $_SESSION['reset_email'];

        if (strlen($new_password) < 8) {
            $message     = 'Password must be at least 8 characters.';
            $messageType = 'error';
            $step        = 2;

        } elseif ($new_password !== $confirm_password) {
            $message     = 'Passwords do not match.';
            $messageType = 'error';
            $step        = 2;

        } else {

            $hash   = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hash, $email);

            if ($update->execute()) {
                // Clear reset session
                unset($_SESSION['reset_email'], $_SESSION['reset_verified']);

                $message     = 'Password changed successfully. You can now log in.';
                $messageType = 'success';
                $step        = 3;
            } else {
                $message     = 'Password reset failed. Please try again.';
                $messageType = 'error';
                $step        = 2;
            }

            $update->close();
        }
    }
}

// If revisiting step 2 via GET (after POST redirect), restore step
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['reset_verified'])) {
        $step = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Department Selection System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            width: 420px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .box h2 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* Steps indicator */
        .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .step-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
        }

        .step-dot.active {
            background: #1e3a8a;
            color: white;
        }

        .step-dot.done {
            background: #16a34a;
            color: white;
        }

        .step-line {
            width: 40px;
            height: 2px;
            background: #e5e7eb;
            align-self: center;
        }

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: bold;
        }

        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Form */
        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e3a8a;
        }

        .hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 6px;
        }

        .submit-btn:hover { background: #162d6b; }

        .links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .links a { color: #1e3a8a; text-decoration: none; font-weight: bold; }
        .links a:hover { text-decoration: underline; }

        .success-icon {
            text-align: center;
            font-size: 50px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Reset Password</h2>
    <p class="subtitle">Debre Markos University</p>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step-dot <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>">1</div>
        <div class="step-line"></div>
        <div class="step-dot <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>">2</div>
        <div class="step-line"></div>
        <div class="step-dot <?= $step >= 3 ? 'done' : '' ?>">3</div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- ================================================
         STEP 1 — Verify identity
    ================================================= -->

    <?php if ($step === 1): ?>

        <p style="font-size:14px; color:#555; margin-bottom:18px;">
            Enter your registered email and student ID to verify your identity.
        </p>

        <form method="POST">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. CSE/123/16" required>
                <p class="hint">Your student ID as registered in the system.</p>
            </div>

            <button type="submit" name="verify" class="submit-btn">
                Verify Identity
            </button>

        </form>

    <!-- ================================================
         STEP 2 — Set new password
    ================================================= -->

    <?php elseif ($step === 2): ?>

        <p style="font-size:14px; color:#555; margin-bottom:18px;">
            Identity verified. Enter and confirm your new password.
        </p>

        <form method="POST">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="At least 8 characters" required minlength="8">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm" placeholder="Repeat new password" required minlength="8">
            </div>

            <button type="submit" name="reset" class="submit-btn">
                Change Password
            </button>

        </form>

    <!-- ================================================
         STEP 3 — Done
    ================================================= -->

    <?php elseif ($step === 3): ?>

        <div class="success-icon">✅</div>

        <p style="text-align:center; color:#166534; font-weight:bold; margin-bottom:16px;">
            Password changed successfully!
        </p>

        <p style="text-align:center; color:#555; font-size:14px; margin-bottom:20px;">
            You can now log in with your new password.
        </p>

    <?php endif; ?>

    <div class="links">
        <a href="login.php">← Back to Login</a>
    </div>

</div>

</body>
</html>
