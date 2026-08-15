<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message     = '';
$messageType = '';

// =====================================================
// CREATE USER
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {

    $full_name  = trim($_POST['full_name']  ?? '');
    $username   = trim($_POST['username']   ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $gender     = $_POST['gender']          ?? '';
    $role       = $_POST['role']            ?? '';
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm']         ?? '';
    $student_id = trim($_POST['student_id'] ?? '');

    $allowedRoles = ['student', 'registrar', 'admin', 'department_head'];

    if ($full_name === '' || $username === '' || $email === '' || $role === '' || $password === '') {
        $message     = 'Please fill all required fields.';
        $messageType = 'error';

    } elseif (!in_array($role, $allowedRoles, true)) {
        $message     = 'Invalid role selected.';
        $messageType = 'error';

    } elseif ($password !== $confirm) {
        $message     = 'Passwords do not match.';
        $messageType = 'error';

    } elseif (strlen($password) < 8) {
        $message     = 'Password must be at least 8 characters.';
        $messageType = 'error';

    } else {

        // Check duplicate email or username
        $check = $conn->prepare("
            SELECT id FROM users
            WHERE email = ? OR username = ?
            LIMIT 1
        ");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $message     = 'Email or username already exists.';
            $messageType = 'error';

        } else {

            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $status = 'Active';

            $stmt = $conn->prepare("
                INSERT INTO users
                    (full_name, student_id, gender, email, phone, username, password, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sssssssss",
                $full_name,
                $student_id,
                $gender,
                $email,
                $phone,
                $username,
                $hash,
                $role,
                $status
            );

            if ($stmt->execute()) {
                $message     = "User \"$full_name\" created successfully as " . ucfirst($role) . ".";
                $messageType = 'success';
            } else {
                $message     = 'Failed to create user: ' . $stmt->error;
                $messageType = 'error';
            }

            $stmt->close();
        }
    }
}

// =====================================================
// FETCH RECENT NON-STUDENT USERS
// =====================================================

$recentUsers = $conn->query("
    SELECT id, full_name, username, email, role, status, created_at
    FROM users
    WHERE role != 'student'
    ORDER BY id DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; }

        /* Sidebar */
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

        /* Message */
        .message {
            padding: 14px 18px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Card */
        .card {
            background: white;
            padding: 28px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 { color: #333366; margin-bottom: 22px; }

        /* Form grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #333366;
        }

        .form-group .hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .full-width { grid-column: 1 / -1; }

        .submit-btn {
            background: #333366;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        .submit-btn:hover { background: #555599; }

        /* Role badge */
        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .role-admin       { background: #fee2e2; color: #991b1b; }
        .role-registrar   { background: #dbeafe; color: #1d4ed8; }
        .role-dept        { background: #fef3c7; color: #92400e; }
        .role-student     { background: #dcfce7; color: #166534; }

        .status-active  { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 12px; }
        .status-blocked { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 12px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }

        th {
            background: #333366;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover { background: #f9fafb; }

        .footer { text-align: center; padding: 25px; color: #777; }

        @media(max-width: 900px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="create_user.php" class="active">Create User</a>
    <a href="manage_roles.php">Manage Roles</a>
    <a href="manage_colleges.php">Manage Colleges</a>
    <a href="manage_academic_year.php">Academic Year</a>
    <a href="settings.php">Settings</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>Create User Account</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Create User Form -->
    <div class="card">
        <h2>New User Details</h2>

        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input type="text" name="full_name" placeholder="e.g. Abebe Kebede" required>
                </div>

                <div class="form-group">
                    <label>Username <span style="color:red">*</span></label>
                    <input type="text" name="username" placeholder="e.g. abebe.k" required>
                </div>

                <div class="form-group">
                    <label>Email <span style="color:red">*</span></label>
                    <input type="email" name="email" placeholder="user@dmu.edu.et" required>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="e.g. 0911234567">
                </div>

                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Role <span style="color:red">*</span></label>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="registrar">Registrar</option>
                        <option value="department_head">Department Head</option>
                        <option value="admin">Admin</option>
                        <option value="student">Student</option>
                    </select>
                    <p class="hint">Students normally self-register. Use this for staff accounts.</p>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" placeholder="Leave blank for staff">
                    <p class="hint">Required only for student accounts.</p>
                </div>

                <div class="form-group">
                    <!-- spacer -->
                </div>

                <div class="form-group">
                    <label>Password <span style="color:red">*</span></label>
                    <input type="password" name="password" placeholder="At least 8 characters" required minlength="8">
                </div>

                <div class="form-group">
                    <label>Confirm Password <span style="color:red">*</span></label>
                    <input type="password" name="confirm" placeholder="Repeat password" required minlength="8">
                </div>

            </div>

            <button type="submit" name="create_user" class="submit-btn">
                Create User
            </button>
        </form>
    </div>

    <!-- Recent Staff Accounts -->
    <div class="card">
        <h2>Staff Accounts (Recent 20)</h2>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($recentUsers && $recentUsers->num_rows > 0): ?>
                <?php $n = 1; ?>
                <?php while ($u = $recentUsers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php
                            $roleClass = match($u['role']) {
                                'admin'           => 'role-admin',
                                'registrar'       => 'role-registrar',
                                'department_head' => 'role-dept',
                                default           => 'role-student',
                            };
                            ?>
                            <span class="role-badge <?= $roleClass ?>">
                                <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['status'] === 'Active'): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-blocked"><?= htmlspecialchars($u['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px; color:#6b7280;">
                            <?= htmlspecialchars($u['created_at']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#777;">
                        No staff accounts found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
