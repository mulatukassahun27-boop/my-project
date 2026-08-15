<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireStudent();

$userId = (int)$_SESSION['user_id'];

// =====================================================
// FETCH STUDENT DATA
// =====================================================

$stmt = $conn->prepare("
    SELECT full_name, student_id, cgpa, status
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$fullName  = $student['full_name']  ?? $_SESSION['username'];
$studentId = $student['student_id'] ?? '-';
$cgpa      = $student['cgpa']       ?? 0;

// =====================================================
// COUNTS
// =====================================================

$totalDepartments = (int)$conn->query(
    "SELECT COUNT(*) AS c FROM departments WHERE status = 'Active'"
)->fetch_assoc()['c'];

// Active academic year
$activeYear = $conn->query(
    "SELECT year_name FROM academic_years WHERE status = 'Active' LIMIT 1"
)->fetch_assoc()['year_name'] ?? 'N/A';

// Has the student submitted choices?
$choiceStmt = $conn->prepare(
    "SELECT id FROM student_choices WHERE student_id = ? LIMIT 1"
);
$choiceStmt->bind_param("i", $userId);
$choiceStmt->execute();
$hasChoices = $choiceStmt->get_result()->num_rows > 0;
$choiceStmt->close();

// Placement status for this student
$placementStmt = $conn->prepare("
    SELECT p.status, p.published, d.department_name
    FROM placements p
    LEFT JOIN departments d ON p.department_id = d.id
    WHERE p.student_id = ?
    LIMIT 1
");
$placementStmt->bind_param("i", $userId);
$placementStmt->execute();
$placement = $placementStmt->get_result()->fetch_assoc();
$placementStmt->close();

if ($placement && $placement['published'] === 'Yes') {
    $placementDisplay = $placement['status'] === 'Placed'
        ? htmlspecialchars($placement['department_name'])
        : 'Not Placed';
} else {
    $placementDisplay = 'Pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Department Selection System</title>
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

        .sidebar a:hover { background: #00509e; }

        .main { margin-left: 250px; padding: 25px; }

        .topbar {
            background: white;
            padding: 18px 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h2 { color: #003366; font-size: 20px; }

        .topbar small { color: #6b7280; }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h1 { font-size: 30px; color: #003366; margin: 8px 0 6px; }
        .card p  { color: #6b7280; font-size: 14px; }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .info-card h3 { color: #003366; margin-bottom: 16px; }

        table { width: 100%; border-collapse: collapse; }

        table th {
            background: #003366;
            color: white;
            padding: 12px;
            text-align: left;
            width: 220px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .action-bar {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-btn {
            background: #003366;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .action-btn:hover { background: #00509e; }

        .action-btn.green { background: #16a34a; }
        .action-btn.green:hover { background: #15803d; }

        @media(max-width: 900px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
        }

        @media(max-width: 600px) {
            .sidebar { width: 0; overflow: hidden; }
            .main { margin-left: 0; }
            .cards { grid-template-columns: 1fr; }
        }
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
    <a href="change_password.php">🔑 Change Password</a>
    <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="topbar">
        <div>
            <h2>Welcome, <?= htmlspecialchars($fullName) ?></h2>
            <small>Student ID: <?= htmlspecialchars($studentId) ?></small>
        </div>
    </div>

    <div class="cards">

        <div class="card">
            <p>Active Departments</p>
            <h1><?= $totalDepartments ?></h1>
        </div>

        <div class="card">
            <p>Academic Year</p>
            <h1 style="font-size:18px; padding-top:6px;"><?= htmlspecialchars($activeYear) ?></h1>
        </div>

        <div class="card">
            <p>Choices Submitted</p>
            <h1><?= $hasChoices ? '✓' : '✗' ?></h1>
        </div>

        <div class="card">
            <p>Placement</p>
            <h1 style="font-size:15px; padding-top:6px;">
                <?= htmlspecialchars($placementDisplay) ?>
            </h1>
        </div>

    </div>

    <div class="info-card">
        <h3>My Information</h3>

        <table>
            <tr>
                <th>Full Name</th>
                <td><?= htmlspecialchars($fullName) ?></td>
            </tr>
            <tr>
                <th>Student ID</th>
                <td><?= htmlspecialchars($studentId) ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?= htmlspecialchars($_SESSION['username'] ?? '') ?></td>
            </tr>
            <tr>
                <th>CGPA</th>
                <td><?= number_format((float)$cgpa, 2) ?></td>
            </tr>
            <tr>
                <th>Account Status</th>
                <td><?= htmlspecialchars($student['status'] ?? 'Active') ?></td>
            </tr>
        </table>

        <div class="action-bar">
            <?php if (!$hasChoices): ?>
                <a href="select_department.php" class="action-btn green">
                    Submit Department Choices
                </a>
            <?php else: ?>
                <a href="my_choices.php" class="action-btn">
                    View My Choices
                </a>
            <?php endif; ?>
            <a href="placement_result.php" class="action-btn">
                Placement Result
            </a>
            <a href="edit_profile.php" class="action-btn">
                Edit Profile
            </a>
        </div>
    </div>

</div>

</body>
</html>
