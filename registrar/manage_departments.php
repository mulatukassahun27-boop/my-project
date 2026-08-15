<?php

require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();

$message = '';
$messageType = '';


// =====================================================
// ADD DEPARTMENT
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {

    $departmentName = trim($_POST['department_name'] ?? '');
    $departmentCode = trim($_POST['department_code'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $status = $_POST['status'] ?? 'Active';


    if ($departmentName === '' || $departmentCode === '' || $capacity <= 0) {

        $message = 'Please fill all fields correctly.';
        $messageType = 'error';

    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {

        $message = 'Invalid department status.';
        $messageType = 'error';

    } else {

        // Check duplicate department code

        $check = $conn->prepare("
            SELECT id
            FROM departments
            WHERE department_code = ?
            LIMIT 1
        ");

        $check->bind_param("s", $departmentCode);

        $check->execute();

        $checkResult = $check->get_result();


        if ($checkResult->num_rows > 0) {

            $message = 'Department code already exists.';
            $messageType = 'error';

        } else {

            $stmt = $conn->prepare("
                INSERT INTO departments
                (
                    department_name,
                    department_code,
                    capacity,
                    status
                )
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssis",
                $departmentName,
                $departmentCode,
                $capacity,
                $status
            );


            if ($stmt->execute()) {

                $message = 'Department added successfully.';
                $messageType = 'success';

            } else {

                $message = 'Failed to add department: ' . $stmt->error;
                $messageType = 'error';

            }

            $stmt->close();
        }

        $check->close();
    }
}


// =====================================================
// UPDATE DEPARTMENT
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {

    $id = (int)($_POST['id'] ?? 0);

    $departmentName = trim($_POST['department_name'] ?? '');

    $departmentCode = trim($_POST['department_code'] ?? '');

    $capacity = (int)($_POST['capacity'] ?? 0);

    $status = $_POST['status'] ?? 'Active';


    if (
        $id <= 0 ||
        $departmentName === '' ||
        $departmentCode === '' ||
        $capacity <= 0
    ) {

        $message = 'Please enter valid department information.';
        $messageType = 'error';

    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {

        $message = 'Invalid status.';
        $messageType = 'error';

    } else {

        // Check duplicate code excluding current department

        $check = $conn->prepare("
            SELECT id
            FROM departments
            WHERE department_code = ?
              AND id != ?
            LIMIT 1
        ");

        $check->bind_param(
            "si",
            $departmentCode,
            $id
        );

        $check->execute();

        $checkResult = $check->get_result();


        if ($checkResult->num_rows > 0) {

            $message = 'Another department already uses this code.';
            $messageType = 'error';

        } else {

            $stmt = $conn->prepare("
                UPDATE departments
                SET
                    department_name = ?,
                    department_code = ?,
                    capacity = ?,
                    status = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssisi",
                $departmentName,
                $departmentCode,
                $capacity,
                $status,
                $id
            );


            if ($stmt->execute()) {

                $message = 'Department updated successfully.';
                $messageType = 'success';

            } else {

                $message = 'Failed to update department: ' . $stmt->error;
                $messageType = 'error';

            }

            $stmt->close();
        }

        $check->close();
    }
}


// =====================================================
// DELETE DEPARTMENT
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_department'])) {

    $id = (int)($_POST['id'] ?? 0);


    if ($id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM departments
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);


        if ($stmt->execute()) {

            $message = 'Department deleted successfully.';
            $messageType = 'success';

        } else {

            $message = 'Cannot delete department. It may already be used by students or placement records.';
            $messageType = 'error';

        }

        $stmt->close();

    }
}


// =====================================================
// FETCH DEPARTMENTS
// =====================================================

$departments = $conn->query("
    SELECT
        id,
        department_name,
        department_code,
        capacity,
        status,
        created_at
    FROM departments
    ORDER BY id DESC
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Manage Departments - Registrar
    </title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Arial, sans-serif;

            background: #f4f6f9;

            color: #1f2937;
        }


        /* ================= HEADER ================= */

        .header {

            background: #1e3a8a;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .header h2 {

            font-size: 21px;
        }


        .logout {

            background: #dc2626;

            color: white;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 6px;
        }


        .logout:hover {

            background: #b91c1c;
        }


        /* ================= CONTAINER ================= */

        .container {

            max-width: 1250px;

            margin: 30px auto;

            padding: 0 20px;
        }


        .top-section {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;
        }


        .top-section h1 {

            color: #1e3a8a;
        }


        .back-btn {

            background: #374151;

            color: white;

            text-decoration: none;

            padding: 10px 16px;

            border-radius: 6px;
        }


        /* ================= MESSAGE ================= */

        .message {

            padding: 14px 18px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-weight: bold;
        }


        .success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #86efac;
        }


        .error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fca5a5;
        }


        /* ================= CARD ================= */

        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 25px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .card h2 {

            color: #1e3a8a;

            margin-bottom: 20px;
        }


        /* ================= FORM ================= */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            align-items: end;
        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            font-size: 14px;
        }


        .form-group input,
        .form-group select {

            width: 100%;

            padding: 11px;

            border: 1px solid #d1d5db;

            border-radius: 6px;

            font-size: 14px;
        }


        .add-btn {

            width: 100%;

            background: #16a34a;

            color: white;

            border: none;

            padding: 11px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: bold;
        }


        .add-btn:hover {

            background: #15803d;
        }


        /* ================= TABLE ================= */

        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }


        th {

            background: #1e3a8a;

            color: white;

            padding: 13px;

            text-align: left;
        }


        td {

            padding: 12px;

            border-bottom: 1px solid #e5e7eb;
        }


        tr:hover {

            background: #f9fafb;
        }


        /* ================= STATUS ================= */

        .active {

            background: #dcfce7;

            color: #166534;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;

            display: inline-block;
        }


        .inactive {

            background: #fee2e2;

            color: #991b1b;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;

            display: inline-block;
        }


        /* ================= ACTION BUTTONS ================= */

        .edit-btn {

            background: #2563eb;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 5px;

            cursor: pointer;
        }


        .delete-btn {

            background: #dc2626;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 5px;

            cursor: pointer;
        }


        .edit-btn:hover {

            background: #1d4ed8;
        }


        .delete-btn:hover {

            background: #b91c1c;
        }


        /* ================= EDIT FORM ================= */

        .edit-form {

            display: none;

            margin-top: 10px;

            padding: 15px;

            background: #f8fafc;

            border-radius: 7px;
        }


        .edit-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 10px;
        }


        .edit-grid input,
        .edit-grid select {

            width: 100%;

            padding: 9px;

            border: 1px solid #ccc;

            border-radius: 5px;
        }


        .update-btn {

            background: #16a34a;

            color: white;

            border: none;

            padding: 9px 14px;

            border-radius: 5px;

            cursor: pointer;
        }


        .cancel-btn {

            background: #6b7280;

            color: white;

            border: none;

            padding: 9px 14px;

            border-radius: 5px;

            cursor: pointer;
        }


        /* ================= FOOTER ================= */

        .footer {

            text-align: center;

            padding: 25px;

            color: #777;
        }


        @media(max-width: 900px) {

            .form-grid {

                grid-template-columns: 1fr 1fr;
            }


            .edit-grid {

                grid-template-columns: 1fr 1fr;
            }

        }


        @media(max-width: 600px) {

            .form-grid {

                grid-template-columns: 1fr;
            }


            .edit-grid {

                grid-template-columns: 1fr;
            }


            .top-section {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="header">

    <h2>
        DMU Department Selection System
    </h2>


    <a
        href="../logout.php"
        class="logout"
    >
        Logout
    </a>

</header>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">


    <div class="top-section">

        <h1>
            Manage Departments
        </h1>


        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ''): ?>

        <div class="message <?= $messageType ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ADD DEPARTMENT
    ================================================== -->

    <div class="card">

        <h2>
            Add New Department
        </h2>


        <form method="POST">

            <div class="form-grid">


                <div class="form-group">

                    <label>
                        Department Name
                    </label>

                    <input
                        type="text"
                        name="department_name"
                        placeholder="e.g. Computer Science"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Department Code
                    </label>

                    <input
                        type="text"
                        name="department_code"
                        placeholder="e.g. CS"
                        maxlength="20"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Capacity
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        min="1"
                        placeholder="e.g. 60"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option value="Active">
                            Active
                        </option>

                        <option value="Inactive">
                            Inactive
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <button
                        type="submit"
                        name="add_department"
                        class="add-btn"
                    >
                        + Add Department
                    </button>

                </div>


            </div>

        </form>

    </div>


    <!-- =================================================
         DEPARTMENT LIST
    ================================================== -->

    <div class="card">

        <h2>
            Department List
        </h2>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Department Name
                        </th>

                        <th>
                            Code
                        </th>

                        <th>
                            Capacity
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Created At
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($departments && $departments->num_rows > 0): ?>


                    <?php $number = 1; ?>


                    <?php while ($department = $departments->fetch_assoc()): ?>


                        <tr>

                            <td>
                                <?= $number++ ?>
                            </td>


                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $department['department_name']
                                    ) ?>
                                </strong>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $department['department_code']
                                ) ?>
                            </td>


                            <td>
                                <?= (int)$department['capacity'] ?>
                            </td>


                            <td>

                                <?php if ($department['status'] === 'Active'): ?>

                                    <span class="active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $department['created_at']
                                ) ?>
                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="edit-btn"
                                    onclick="showEdit(<?= (int)$department['id'] ?>)"
                                >
                                    Edit
                                </button>


                                <form
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this department?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$department['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_department"
                                        class="delete-btn"
                                    >
                                        Delete
                                    </button>

                                </form>


                            </td>

                        </tr>


                        <!-- EDIT FORM -->

                        <tr
                            id="edit-<?= (int)$department['id'] ?>"
                            style="display:none;"
                        >

                            <td colspan="7">

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$department['id'] ?>"
                                    >


                                    <div class="edit-form" style="display:block;">

                                        <div class="edit-grid">


                                            <input
                                                type="text"
                                                name="department_name"
                                                value="<?= htmlspecialchars(
                                                    $department['department_name']
                                                ) ?>"
                                                required
                                            >


                                            <input
                                                type="text"
                                                name="department_code"
                                                value="<?= htmlspecialchars(
                                                    $department['department_code']
                                                ) ?>"
                                                maxlength="20"
                                                required
                                            >


                                            <input
                                                type="number"
                                                name="capacity"
                                                value="<?= (int)$department['capacity'] ?>"
                                                min="1"
                                                required
                                            >


                                            <select name="status">

                                                <option
                                                    value="Active"
                                                    <?= $department['status'] === 'Active'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Active
                                                </option>


                                                <option
                                                    value="Inactive"
                                                    <?= $department['status'] === 'Inactive'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    Inactive
                                                </option>

                                            </select>


                                            <button
                                                type="submit"
                                                name="update_department"
                                                class="update-btn"
                                            >
                                                Save Changes
                                            </button>


                                            <button
                                                type="button"
                                                class="cancel-btn"
                                                onclick="hideEdit(<?= (int)$department['id'] ?>)"
                                            >
                                                Cancel
                                            </button>


                                        </div>

                                    </div>

                                </form>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            style="text-align:center;padding:40px;color:#777;"
                        >
                            No departments found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>


</div>


<footer class="footer">

    Department Selection System © 2026

    <br>

    Debre Markos University

</footer>


<script>

function showEdit(id) {

    document.getElementById(
        'edit-' + id
    ).style.display = 'table-row';

}


function hideEdit(id) {

    document.getElementById(
        'edit-' + id
    ).style.display = 'none';

}

</script>


</body>

</html>