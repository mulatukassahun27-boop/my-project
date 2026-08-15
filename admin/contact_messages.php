<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

// =====================================================
// MARK AS READ
// =====================================================

if (isset($_GET['read'])) {
    $id   = (int)$_GET['read'];
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: contact_messages.php");
    exit();
}

// =====================================================
// DELETE MESSAGE
// =====================================================

if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: contact_messages.php");
    exit();
}

// =====================================================
// FILTER
// =====================================================

$filter = $_GET['filter'] ?? 'all';

if ($filter === 'unread') {
    $messages = $conn->query("SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC");
} else {
    $messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
}

// Unread count for badge
$unreadCount = $conn->query(
    "SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0"
)->fetch_assoc()['c'] ?? 0;

$totalCount = $conn->query(
    "SELECT COUNT(*) AS c FROM contact_messages"
)->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin</title>
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

        .sidebar a:hover  { background: #555599; }
        .sidebar a.active { background: #555599; font-weight: bold; }

        .badge {
            display: inline-block;
            background: #dc2626;
            color: white;
            font-size: 11px;
            border-radius: 10px;
            padding: 2px 7px;
            margin-left: 6px;
            vertical-align: middle;
        }

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

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 { font-size: 30px; color: #333366; margin-bottom: 5px; }
        .stat-card p  { color: #6b7280; font-size: 14px; }

        /* Filter tabs */
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .filter-tab {
            padding: 9px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .filter-tab:hover  { background: #f3f4f6; }
        .filter-tab.active { background: #333366; color: white; border-color: #333366; }

        /* Table card */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-container { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }

        th {
            background: #333366;
            color: white;
            padding: 13px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: top;
        }

        tr:hover { background: #f9fafb; }

        tr.unread { background: #eff6ff; }
        tr.unread:hover { background: #dbeafe; }

        .message-text {
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #374151;
        }

        .unread-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            background: #2563eb;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }

        .btn-read {
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
        }

        .btn-read:hover { background: #1d4ed8; }

        .btn-delete {
            background: #dc2626;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
            margin-left: 5px;
        }

        .btn-delete:hover { background: #b91c1c; }

        .empty {
            text-align: center;
            padding: 50px;
            color: #777;
        }

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
    <a href="settings.php">Settings</a>
    <a href="contact_messages.php" class="active">
        Contact Messages
        <?php if ($unreadCount > 0): ?>
            <span class="badge"><?= $unreadCount ?></span>
        <?php endif; ?>
    </a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">

    <div class="top-section">
        <h1>Contact Messages</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?= (int)$totalCount ?></h3>
            <p>Total Messages</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)$unreadCount ?></h3>
            <p>Unread</p>
        </div>
        <div class="stat-card">
            <h3><?= (int)($totalCount - $unreadCount) ?></h3>
            <p>Read</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="contact_messages.php?filter=all"
           class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">
            All Messages
        </a>
        <a href="contact_messages.php?filter=unread"
           class="filter-tab <?= $filter === 'unread' ? 'active' : '' ?>">
            Unread
            <?php if ($unreadCount > 0): ?>
                <span class="badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($messages && $messages->num_rows > 0): ?>
                    <?php $i = 1; ?>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                        <tr class="<?= $msg['is_read'] ? '' : 'unread' ?>">
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="unread-dot"></span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($msg['name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td>
                                <div class="message-text"
                                     title="<?= htmlspecialchars($msg['message']) ?>">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </td>
                            <td style="white-space:nowrap; color:#6b7280; font-size:13px;">
                                <?= htmlspecialchars($msg['created_at']) ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php if (!$msg['is_read']): ?>
                                    <a href="contact_messages.php?read=<?= (int)$msg['id'] ?>&filter=<?= htmlspecialchars($filter) ?>"
                                       class="btn-read">
                                        Mark Read
                                    </a>
                                <?php endif; ?>
                                <a href="contact_messages.php?delete=<?= (int)$msg['id'] ?>&filter=<?= htmlspecialchars($filter) ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Delete this message?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">
                            No messages found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<footer class="footer">
    Department Selection System © 2026<br>Debre Markos University
</footer>

</body>
</html>
