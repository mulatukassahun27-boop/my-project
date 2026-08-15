<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

$message     = '';
$messageType = '';

// =====================================================
// UPDATE STUDENT (CGPA, entry year, status, college)
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {

    $id         = (int)($_POST['student_id_hidden'] ?? 0);
    $cgpa       = trim($_POST['cgpa']       ?? '');
    $entry_year = trim($_POST['entry_year'] ?? '');
    $status     = trim($_POST['status']     ?? '');
    $college_id = (int)($_POST['college_id'] ?? 0);

    $validStatuses = ['Active', 'Blocked', 'Pending'];

    if ($id <= 0) {
        $message     = 'Invalid student.';
        $messageType = 'error';

    } elseif ($cgpa === '' || !is_numeric($cgpa) || (float)$cgpa < 0 || (float)$cgpa > 4.0) {
        $message     = 'CGPA must be a number between 0.00 and 4.00.';
        $messageType = 'error';

    } elseif (!in_array($status, $validStatuses, true)) {
        $message     = 'Invalid status.';
        $messageType = 'error';

    } else {

        $cgpaVal    = number_format((float)$cgpa, 2, '.', '');
        $collegeVal = $college_id > 0 ? $college_id : null;

        $stmt = $conn->prepare("
            UPDATE users
            SET cgpa       = ?,
                entry_year = ?,
                status     = ?,
                college_id = ?
            WHERE id = ?
              AND role = 'student'
        ");
        $stmt->bind_param("dssii", $cgpaVal, $entry_year, $status, $collegeVal, $id);

        if ($stmt->execute()) {
            $message     = 'Student updated successfully.';
            $messageType = 'success';
        } else {
            $message     = 'Update failed: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// =====================================================
// BULK CGPA UPLOAD (simple CSV: student_id,cgpa)
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {

    $csvData = trim($_POST['csv_data'] ?? '');
    $lines   = array_filter(array_map('trim', explode("\n", $csvData)));
    $updated = 0;
    $errors  = 0;

    foreach ($lines as $line) {
        $parts = array_map('trim', explode(',', $line));
        if (count($parts) < 2) { $errors++; continue; }

        $sid  = $parts[0];
        $cgpa = $parts[1];

        if (!is_numeric($cgpa) || (float)$cgpa < 0 || (float)$cgpa > 4.0) {
            $errors++;
            continue;
        }

        $cgpaVal = number_format((float)$cgpa, 2, '.', '');

        $stmt = $conn->prepare("UPDATE users SET cgpa = ? WHERE student_id = ? AND role = 'student'");
        $stmt->bind_param("ds", $cgpaVal, $sid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) $updated++;
        else $errors++;

        $stmt->close();
    }

    $message     = "Bulk update done: $updated students updated, $errors errors.";
    $messageType = $errors === 0 ? 'success' : 'warning';
}

// =====================================================
// SEARCH & FILTER
// =====================================================

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all'; // all | no_cgpa | has_choices

$whereExtra = '';
if ($filter === 'no_cgpa') {
    $whereExtra = " AND (u.cgpa = 0 OR u.cgpa IS NULL)";
} elseif ($filter === 'has_choices') {
    $whereExtra = " AND EXISTS (SELECT 1 FROM student_choices sc WHERE sc.student_id = u.id)";
}

if ($search !== '') {
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare("
        SELECT u.id, u.student_id, u.full_name, u.gender, u.email,
               u.phone, u.username, u.status, u.cgpa, u.college_id, u.entry_year
        FROM users u
        WHERE u.role = 'student'
          AND (u.student_id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)
          $whereExtra
        ORDER BY u.full_name ASC
    ");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $conn->query("
        SELECT u.id, u.student_id, u.full_name, u.gender, u.email,
               u.phone, u.username, u.status, u.cgpa, u.college_id, u.entry_year
        FROM users u
        WHERE u.role = 'student' $whereExtra
        ORDER BY u.full_name ASC
    ");
}

// Counts
$totalStudents = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
$noCgpa        = (int)$conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student' AND (cgpa=0 OR cgpa IS NULL)")->fetch_assoc()['c'];
$hasChoices    = (int)$conn->query("SELECT COUNT(DISTINCT student_id) AS c FROM student_choices")->fetch_assoc()['c'];

// Colleges for dropdown
$colleges = [];
$cResult  = $conn->query("SELECT id, college_name FROM colleges ORDER BY college_name ASC");
if ($cResult) {
    while ($c = $cResult->fetch_assoc()) $colleges[] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Registrar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: Arial, sans-serif; background: #f4f6f9; color: #1f2937; }

        .header {
            background: #1e3a8a; color: white;
            padding: 18px 30px;
            display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 100;
        }

        .header h2 { font-size: 20px; }

        .logout {
            background: #dc2626; color: white; text-decoration: none;
            padding: 9px 16px; border-radius: 6px;
        }

        .logout:hover { background: #b91c1c; }

        .container { max-width: 1400px; margin: 25px auto; padding: 0 20px; }

        .top-section {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }

        .top-section h1 { color: #1e3a8a; }

        .back-btn {
            background: #374151; color: white; text-decoration: none;
            padding: 10px 16px; border-radius: 6px;
        }

        /* Stats */
        .stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 22px; }

        .stat-card {
            background: white; padding: 18px; border-radius: 10px;
            text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .stat-card h3 { font-size: 28px; color: #1e3a8a; margin-bottom: 4px; }
        .stat-card p  { color: #6b7280; font-size: 13px; }

        /* Message */
        .message { padding: 13px 18px; border-radius: 7px; margin-bottom: 18px; font-weight: bold; }

        .success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* Bulk card */
        .bulk-card {
            background: white; padding: 22px; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
        }

        .bulk-card h3 { color: #1e3a8a; margin-bottom: 10px; }
        .bulk-card p  { color: #6b7280; font-size: 13px; margin-bottom: 10px; }

        .bulk-card textarea {
            width: 100%; height: 100px; padding: 10px;
            border: 1px solid #d1d5db; border-radius: 6px;
            font-size: 13px; font-family: monospace; resize: vertical;
        }

        .bulk-btn {
            background: #1e3a8a; color: white; border: none;
            padding: 10px 22px; border-radius: 6px; cursor: pointer;
            font-size: 14px; font-weight: bold; margin-top: 10px;
        }

        .bulk-btn:hover { background: #162d6b; }

        /* Search + filter bar */
        .search-bar {
            background: white; padding: 16px 20px; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 18px;
            display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
        }

        .search-bar input {
            flex: 1; min-width: 200px; padding: 11px;
            border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;
        }

        .search-bar input:focus { outline: none; border-color: #1e3a8a; }

        .search-btn {
            background: #1e3a8a; color: white; border: none;
            padding: 11px 20px; border-radius: 6px; cursor: pointer; font-size: 14px;
        }

        .search-btn:hover { background: #162d6b; }

        .clear-btn {
            background: #6b7280; color: white; text-decoration: none;
            padding: 11px 16px; border-radius: 6px; font-size: 14px;
        }

        .filter-tabs { display: flex; gap: 8px; }

        .filter-tab {
            padding: 8px 14px; border-radius: 6px; text-decoration: none;
            font-size: 13px; font-weight: bold; border: 1px solid #d1d5db;
            background: white; color: #374151;
        }

        .filter-tab:hover  { background: #f3f4f6; }
        .filter-tab.active { background: #1e3a8a; color: white; border-color: #1e3a8a; }

        /* Table */
        .table-wrap {
            background: white; border-radius: 10px;
            overflow-x: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        table { width: 100%; border-collapse: collapse; min-width: 1100px; }

        th {
            background: #1e3a8a; color: white;
            padding: 13px 12px; text-align: left; white-space: nowrap;
        }

        td { padding: 11px 12px; border-bottom: 1px solid #eee; vertical-align: middle; }

        tr:hover { background: #f9fafb; }

        .cgpa-good  { color: #166534; font-weight: bold; }
        .cgpa-mid   { color: #92400e; font-weight: bold; }
        .cgpa-low   { color: #991b1b; font-weight: bold; }
        .cgpa-zero  { color: #9ca3af; }

        .badge-active  { background: #dcfce7; color: #166534; padding: 3px 9px; border-radius: 20px; font-size: 12px; }
        .badge-blocked { background: #fee2e2; color: #991b1b; padding: 3px 9px; border-radius: 20px; font-size: 12px; }
        .badge-pending { background: #fef3c7; color: #92400e; padding: 3px 9px; border-radius: 20px; font-size: 12px; }

        /* Inline edit form */
        .edit-row { display: none; background: #f0f4ff; }

        .edit-row td { padding: 16px; }

        .edit-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr) auto auto;
            gap: 10px; align-items: end;
        }

        .edit-grid label { display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px; font-weight: bold; }

        .edit-grid input,
        .edit-grid select {
            width: 100%; padding: 9px; border: 1px solid #d1d5db;
            border-radius: 5px; font-size: 13px;
        }

        .save-btn {
            background: #16a34a; color: white; border: none;
            padding: 9px 16px; border-radius: 5px; cursor: pointer;
            font-weight: bold; white-space: nowrap;
        }

        .save-btn:hover { background: #15803d; }

        .cancel-btn {
            background: #6b7280; color: white; border: none;
            padding: 9px 14px; border-radius: 5px; cursor: pointer;
            white-space: nowrap;
        }

        .edit-btn {
            background: #2563eb; color: white; border: none;
            padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 13px;
        }

        .edit-btn:hover { background: #1d4ed8; }

        .view-btn {
            background: #6b7280; color: white; text-decoration: none;
            padding: 6px 12px; border-radius: 5px; font-size: 13px; display: inline-block;
        }

        .view-btn:hover { background: #4b5563; }

        .no-data { text-align: center; padding: 50px; color: #777; }

        .footer { text-align: center; padding: 25px; color: #777; margin-top: 20px; }

        @media(max-width: 700px) {
            .stats { grid-template-columns: 1fr; }
            .top-section { flex-direction: column; gap: 12px; }
            .filter-tabs { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<header class="header">
    <h2>DMU Department Selection System</h2>
    <a href="../logout.php" class="logout">Logout</a>
</header>

<div class="container">

    <div class="top-section">
        <h1>Manage Students</h1>
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <h3><?= $totalStudents ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3 style="color:#dc2626;"><?= $noCgpa ?></h3>
            <p>Students Without CGPA</p>
        </div>
        <div class="stat-card">
            <h3><?= $hasChoices ?></h3>
            <p>Submitted Choices</p>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Bulk CGPA Upload -->
    <div class="bulk-card">
        <h3>📥 Bulk CGPA Update</h3>
        <p>Paste student data below — one student per line in format: <code>StudentID,CGPA</code> (e.g. <code>CSE/001/16,3.75</code>)</p>
        <form method="POST">
            <textarea name="csv_data" placeholder="CSE/001/16,3.75&#10;CSE/002/16,3.20&#10;CSE/003/16,2.90"></textarea>
            <br>
            <button type="submit" name="bulk_update" class="bulk-btn">
                Upload CGPA Data
            </button>
        </form>
    </div>

    <!-- Search + Filter -->
    <form method="GET">
        <div class="search-bar">
            <input type="text" name="search"
                   value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by Student ID, name or email...">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <button type="submit" class="search-btn">Search</button>
            <?php if ($search !== ''): ?>
                <a href="manage_students.php?filter=<?= htmlspecialchars($filter) ?>" class="clear-btn">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="filter-tabs" style="margin-bottom:16px;">
        <a href="manage_students.php?search=<?= urlencode($search) ?>&filter=all"
           class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All Students</a>
        <a href="manage_students.php?search=<?= urlencode($search) ?>&filter=no_cgpa"
           class="filter-tab <?= $filter === 'no_cgpa' ? 'active' : '' ?>">Missing CGPA (<?= $noCgpa ?>)</a>
        <a href="manage_students.php?search=<?= urlencode($search) ?>&filter=has_choices"
           class="filter-tab <?= $filter === 'has_choices' ? 'active' : '' ?>">Submitted Choices</a>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>CGPA</th>
                    <th>Entry Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($students && $students->num_rows > 0): ?>
                <?php $n = 1; ?>
                <?php while ($s = $students->fetch_assoc()): ?>

                    <!-- Student Row -->
                    <tr id="row-<?= $s['id'] ?>">
                        <td><?= $n++ ?></td>
                        <td><strong><?= htmlspecialchars($s['student_id'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['gender'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td>
                            <?php
                            $cgpa = (float)($s['cgpa'] ?? 0);
                            if ($cgpa == 0) {
                                echo "<span class='cgpa-zero'>Not set</span>";
                            } elseif ($cgpa >= 3.5) {
                                echo "<span class='cgpa-good'>" . number_format($cgpa, 2) . "</span>";
                            } elseif ($cgpa >= 2.0) {
                                echo "<span class='cgpa-mid'>" . number_format($cgpa, 2) . "</span>";
                            } else {
                                echo "<span class='cgpa-low'>" . number_format($cgpa, 2) . "</span>";
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($s['entry_year'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($s['status'] === 'Active'): ?>
                                <span class="badge-active">Active</span>
                            <?php elseif ($s['status'] === 'Blocked'): ?>
                                <span class="badge-blocked">Blocked</span>
                            <?php else: ?>
                                <span class="badge-pending"><?= htmlspecialchars($s['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="edit-btn"
                                    onclick="toggleEdit(<?= $s['id'] ?>)">
                                ✏️ Edit
                            </button>
                            <a href="student_details.php?id=<?= $s['id'] ?>" class="view-btn">
                                👁 View
                            </a>
                        </td>
                    </tr>

                    <!-- Inline Edit Row -->
                    <tr class="edit-row" id="edit-<?= $s['id'] ?>">
                        <td colspan="9">
                            <form method="POST">
                                <input type="hidden" name="student_id_hidden" value="<?= $s['id'] ?>">

                                <div class="edit-grid">

                                    <div>
                                        <label>CGPA (0.00 – 4.00)</label>
                                        <input type="number" name="cgpa" step="0.01" min="0" max="4"
                                               value="<?= number_format((float)($s['cgpa'] ?? 0), 2) ?>"
                                               required placeholder="e.g. 3.75">
                                    </div>

                                    <div>
                                        <label>Entry Year</label>
                                        <input type="text" name="entry_year"
                                               value="<?= htmlspecialchars($s['entry_year'] ?? '') ?>"
                                               placeholder="e.g. 2022">
                                    </div>

                                    <div>
                                        <label>Status</label>
                                        <select name="status">
                                            <option value="Active"  <?= ($s['status'] ?? '') === 'Active'  ? 'selected' : '' ?>>Active</option>
                                            <option value="Blocked" <?= ($s['status'] ?? '') === 'Blocked' ? 'selected' : '' ?>>Blocked</option>
                                            <option value="Pending" <?= ($s['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>College</label>
                                        <select name="college_id">
                                            <option value="0">-- Not Assigned --</option>
                                            <?php foreach ($colleges as $col): ?>
                                                <option value="<?= $col['id'] ?>"
                                                    <?= (int)($s['college_id'] ?? 0) === (int)$col['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($col['college_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div><!-- spacer --></div>

                                    <button type="submit" name="update_student" class="save-btn">
                                        💾 Save
                                    </button>

                                    <button type="button" class="cancel-btn"
                                            onclick="toggleEdit(<?= $s['id'] ?>)">
                                        Cancel
                                    </button>

                                </div>
                            </form>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="9" class="no-data">
                        <?= $search !== '' ? 'No students match your search.' : 'No students registered yet.' ?>
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

<script>
function toggleEdit(id) {
    const row = document.getElementById('edit-' + id);
    row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row';
}
</script>

</body>
</html>
