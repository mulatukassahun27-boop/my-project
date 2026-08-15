<?php

require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();


// =====================================================
// SEARCH
// =====================================================

$search = trim($_GET['search'] ?? '');


// =====================================================
// GET STUDENTS
// =====================================================

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT
            u.id,
            u.student_id,
            u.full_name,
            u.gender,
            u.email,
            u.phone,
            u.username,
            u.role,
            u.status,
            u.cgpa,
            u.college_id,
            u.department_id,
            u.entry_year,
            s.id AS student_record_id
        FROM users u
        LEFT JOIN students s
            ON s.user_id = u.id
        WHERE
            u.role = 'student'
            AND (
                u.student_id LIKE ?
                OR u.full_name LIKE ?
                OR u.email LIKE ?
                OR u.username LIKE ?
            )
        ORDER BY u.id DESC
    ");

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $students = $stmt->get_result();

} else {

    $students = $conn->query("
        SELECT
            u.id,
            u.student_id,
            u.full_name,
            u.gender,
            u.email,
            u.phone,
            u.username,
            u.role,
            u.status,
            u.cgpa,
            u.college_id,
            u.department_id,
            u.entry_year,
            s.id AS student_record_id
        FROM users u
        LEFT JOIN students s
            ON s.user_id = u.id
        WHERE u.role = 'student'
        ORDER BY u.id DESC
    ");

}

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
        Manage Students - Registrar
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


        /* ================= MAIN ================= */

        .container {
            padding: 30px;
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


        /* ================= SEARCH ================= */

        .search-box {
            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .search-form {
            display: flex;

            gap: 10px;
        }


        .search-form input {
            flex: 1;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        .search-btn {
            border: none;

            background: #1e3a8a;

            color: white;

            padding: 12px 22px;

            border-radius: 6px;

            cursor: pointer;
        }


        .search-btn:hover {
            background: #162d6b;
        }


        .clear-btn {
            background: #6b7280;

            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 6px;
        }


        /* ================= TABLE ================= */

        .table-container {
            background: white;

            border-radius: 10px;

            overflow-x: auto;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        table {
            width: 100%;

            min-width: 1200px;

            border-collapse: collapse;
        }


        th {
            background: #1e3a8a;

            color: white;

            padding: 13px;

            text-align: left;

            white-space: nowrap;
        }


        td {
            padding: 12px;

            border-bottom: 1px solid #eee;

            white-space: nowrap;
        }


        tr:hover {
            background: #f9fafb;
        }


        .name {
            font-weight: bold;
        }


        .cgpa {
            font-weight: bold;

            color: #047857;
        }


        .active {
            background: #dcfce7;

            color: #166534;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;
        }


        .pending {
            background: #fef3c7;

            color: #92400e;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;
        }


        .blocked {
            background: #fee2e2;

            color: #991b1b;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 13px;
        }


        .no-data {
            text-align: center;

            padding: 40px;

            color: #777;
        }


        /* ================= FOOTER ================= */

        .footer {
            text-align: center;

            padding: 25px;

            color: #777;
        }


        /* ================= MOBILE ================= */

        @media(max-width: 700px) {

            .container {
                padding: 15px;
            }


            .top-section {
                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }


            .search-form {
                flex-direction: column;
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
            Manage Students
        </h1>


        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- =================================================
         SEARCH
    ================================================== -->

    <div class="search-box">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search Student ID, name, email or username..."
                value="<?= htmlspecialchars($search) ?>"
            >


            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>


            <?php if ($search !== ''): ?>

                <a
                    href="manage_students.php"
                    class="clear-btn"
                >
                    Clear
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- =================================================
         STUDENT TABLE
    ================================================== -->

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>#</th>

                    <th>Student ID</th>

                    <th>Full Name</th>

                    <th>Gender</th>

                    <th>Email</th>

                    <th>Phone</th>

                    <th>Username</th>

                    <th>CGPA</th>

                    <th>College ID</th>

                    <th>Department ID</th>

                    <th>Entry Year</th>

                    <th>Status</th>

                </tr>

            </thead>


            <tbody>


            <?php if ($students && $students->num_rows > 0): ?>


                <?php $number = 1; ?>


                <?php while ($student = $students->fetch_assoc()): ?>


                    <tr>


                        <td>
                            <?= $number++ ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['student_id'] ?? ''
                            ) ?>
                        </td>


                        <td class="name">
                            <?= htmlspecialchars(
                                $student['full_name'] ?? ''
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['gender'] ?? ''
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['email'] ?? ''
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['phone'] ?? 'N/A'
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['username'] ?? ''
                            ) ?>
                        </td>


                        <td class="cgpa">
                            <?= number_format(
                                (float)($student['cgpa'] ?? 0),
                                2
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['college_id'] ?? 'Not Assigned'
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['department_id'] ?? 'Not Assigned'
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student['entry_year'] ?? 'N/A'
                            ) ?>
                        </td>


                        <td>

                            <?php
                            $status = $student['status'] ?? 'Active';

                            if ($status === 'Active'):
                            ?>

                                <span class="active">
                                    Active
                                </span>

                            <?php
                            elseif ($status === 'Pending'):
                            ?>

                                <span class="pending">
                                    Pending
                                </span>

                            <?php else: ?>

                                <span class="blocked">
                                    <?= htmlspecialchars($status) ?>
                                </span>

                            <?php endif; ?>

                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="12"
                        class="no-data"
                    >
                        No registered students found.
                    </td>

                </tr>


            <?php endif; ?>


            </tbody>

        </table>

    </div>


</div>


<footer class="footer">

    Department Selection System © 2026

    <br>

    Debre Markos University

</footer>


</body>

</html>