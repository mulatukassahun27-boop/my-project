<?php
require_once 'config/database.php';

$message     = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $text    = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $text === '') {
        $message     = 'Please fill in all fields.';
        $messageType = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message     = 'Please enter a valid email address.';
        $messageType = 'error';

    } elseif (strlen($text) < 10) {
        $message     = 'Message is too short. Please provide more detail.';
        $messageType = 'error';

    } else {

        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $email, $subject, $text);

        if ($stmt->execute()) {
            $message     = 'Your message has been sent successfully. We will get back to you soon.';
            $messageType = 'success';
            // Clear fields on success
            $name = $email = $subject = $text = '';
        } else {
            $message     = 'Message sending failed. Please try again.';
            $messageType = 'error';
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
    <title>Contact Us - Department Selection System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        header {
            background: #003366;
            padding: 18px 50px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { font-size: 22px; font-weight: bold; }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 15px;
        }

        nav a:hover { color: #ffcc00; }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 30px;
        }

        .info-box, .form-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.09);
        }

        h2 { color: #003366; margin-bottom: 20px; }

        .info-item {
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            align-items: flex-start;
        }

        .info-icon {
            font-size: 22px;
            width: 40px;
            flex-shrink: 0;
        }

        .info-item h4 { color: #003366; margin-bottom: 4px; font-size: 15px; }
        .info-item p  { color: #555; font-size: 14px; line-height: 1.5; }

        /* Message */
        .alert {
            padding: 13px 16px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: bold;
        }

        .alert.success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Form */
        .form-group { margin-bottom: 16px; }

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
            border-color: #003366;
        }

        .form-group textarea { height: 130px; resize: vertical; }

        .send-btn {
            width: 100%;
            padding: 13px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .send-btn:hover { background: #00509e; }

        footer {
            background: #003366;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        @media(max-width: 750px) {
            .container { grid-template-columns: 1fr; }
            header { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Debre Markos University</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="container">

    <!-- Contact Info -->
    <div class="info-box">
        <h2>Contact Information</h2>

        <div class="info-item">
            <div class="info-icon">🏛️</div>
            <div>
                <h4>University</h4>
                <p>Debre Markos University</p>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📍</div>
            <div>
                <h4>Location</h4>
                <p>Debre Markos, Amhara Region, Ethiopia</p>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📧</div>
            <div>
                <h4>Email</h4>
                <p>info@dmu.edu.et</p>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📞</div>
            <div>
                <h4>Phone</h4>
                <p>+251 058 771 1570</p>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">🌐</div>
            <div>
                <h4>Website</h4>
                <p>www.dmu.edu.et</p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="form-box">
        <h2>Send a Message</h2>

        <?php if ($message !== ''): ?>
            <div class="alert <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>

            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="name"
                       value="<?= htmlspecialchars($name ?? '') ?>"
                       placeholder="Full name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($email ?? '') ?>"
                       placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject"
                       value="<?= htmlspecialchars($subject ?? '') ?>"
                       placeholder="Message subject" required>
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" placeholder="Write your message here..." required><?= htmlspecialchars($text ?? '') ?></textarea>
            </div>

            <button type="submit" name="send" class="send-btn">
                Send Message
            </button>

        </form>
    </div>

</div>

<footer>
    <p>Debre Markos University Department Selection System © 2026</p>
</footer>

</body>
</html>
