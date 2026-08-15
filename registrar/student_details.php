<?php

require_once '../config/database.php';
require_once '../config/session.php';

requireRegistrar();


// =====================================================
// GET STUDENT ID
// =====================================================

$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId <= 0) {
    header("Location: manage_students.php");
    exit();
}


// =====================================================
// GET STUDENT
// =====================================================

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
        u.photo,
        u.cgpa,
        u.college_id,
        u.department_id,
        u.entry_year,
        u.created_at,

        s.id AS student_record_id,
        s.first_name,
        s.middle_name,
        s.last_name,
        s.college_id AS student_college_id,
        s.cgpa AS student_cgpa,
        s.entry_year AS student_entry_year,
        s.status AS student_status

    FROM users u

    LEFT JOIN students s
        ON s.user_id = u.id

    WHERE u.id = ?
      AND u.role = 'student'

    LIMIT 1
");

$stmt->bind_param("i", $studentId);

$stmt->execute();

$result = $stmt->get_result();


// =====================================================
// CHECK STUDENT
// =====================================================

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: manage_students.php");
    exit();
}

$student = $result->fetch_assoc();

$stmt->close();


// =====================================================
// VALUES
// =====================================================

$fullName = $student['full_name'] ?? '';

$studentIdNumber = $student['student_id'] ?? '';

$gender = $student['gender'] ?? '';

$email = $student['email'] ?? '';

$phone = $student['phone'] ?? '';

$username = $student['username'] ?? '';

$cgpa = $student['cgpa'] ?? 0;

$collegeId = $student['college_id'] ?? null;

$departmentId = $student['department_id'] ?? null;

$entryYear = $student['entry_year'] ?? null;

$status = $student['status'] ?? 'Active';

$createdAt = $student['created_at'] ?? '';

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
        Student Details - Registrar
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

            max-width: 1100px;

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


        /* ================= CARD ================= */

        .card {

            background: white;

            border-radius: 12px;

            padding: 30px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.08);
        }


        .card h2 {

            color: #1e3a8a;

            margin-bottom: 20px;

            border-bottom: 2px solid #eee;

            padding-bottom: 10px;
        }


        /* ================= GRID ================= */

        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 18px;
        }


        .info-item {

            background: #f8fafc;

            padding: 15px;

            border-radius: 7px;

            border: 1px solid #e5e7eb;
        }


        .info-item label {

            display: block;

            color: #6b7280;

            font-size: 13px;

            margin-bottom: 5px;
        }


        .info-item strong {

            font-size: 15px;

            color: #111827;
        }


        /* ================= STATUS ================= */

        .active {

            background: #dcfce7;

            color: #166534;

            padding: 6px 12px;

            border-radius: 20px;

            display: inline-block;

            font-size: 13px;
        }


        .pending {

            background: #fef3c7;

            color: #92400e;

            padding: 6px 12px;

            border-radius: 20px;

            display: inline-block;

            font-size: 13px;
        }


        .blocked {

            background: #fee2e2;

            color: #991b1b;

            padding: 6px 12px;

            border-radius: 20px;

            display: inline-block;

            font-size: 13px;
        }


        /* ================= CGPA ================= */

        .cgpa-box {

            text-align: center;

            padding: 25px;

            background: #eff6ff;

            border-radius: 10px;

            margin-top: 20px;
        }


        .cgpa-box h3 {

            color: #1e3a8a;

            margin-bottom: 8px;
        }


        .cgpa-value {

            font-size: 35px;

            font-weight: bold;

            color: #047857;
        }


        /* ================= FOOTER ================= */

        .footer {

            text-align: center;

            color: #777;

            padding: 25px;
        }


        /* ================= MOBILE ================= */

        @media(max-width: 700px) {

            .info-grid {

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
            Student Details
        </h1>


        <a
            href="manage_students.php"
            class="back-btn"
        >
            ← Back to Students
        </a>

    </div>


    <!-- =================================================
         BASIC INFORMATION
    ================================================== -->

    <div class="card">

        <h2>
            Student Information
        </h2>


        <div class="info-grid">


            <div class="info-item">

                <label>
                    Student ID
                </label>

                <strong>
                    <?= htmlspecialchars($studentIdNumber) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Full Name
                </label>

                <strong>
                    <?= htmlspecialchars($fullName) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Gender
                </label>

                <strong>
                    <?= htmlspecialchars($gender) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Username
                </label>

                <strong>
                    <?= htmlspecialchars($username) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Email
                </label>

                <strong>
                    <?= htmlspecialchars($email) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Phone
                </label>

                <strong>
                    <?= htmlspecialchars($phone ?: 'Not provided') ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    College ID
                </label>

                <strong>
                    <?= htmlspecialchars(
                        $collegeId ?? 'Not Assigned'
                    ) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Department ID
                </label>

                <strong>
                    <?= htmlspecialchars(
                        $departmentId ?? 'Not Assigned'
                    ) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Entry Year
                </label>

                <strong>
                    <?= htmlspecialchars(
                        $entryYear ?? 'Not Available'
                    ) ?>
                </strong>

            </div>


            <div class="info-item">

                <label>
                    Account Status
                </label>


                <?php if ($status === 'Active'): ?>

                    <span class="active">
                        Active
                    </span>

                <?php elseif ($status === 'Pending'): ?>

                    <span class="pending">
                        Pending
                    </span>

                <?php else: ?>

                    <span class="blocked">
                        <?= htmlspecialchars($status) ?>
                    </span>

                <?php endif; ?>

            </div>


            <div class="info-item">

                <label>
                    Registered Date
                </label>

                <strong>
                    <?= htmlspecialchars(
                        $createdAt ?: 'Not Available'
                    ) ?>
                </strong>

            </div>


        </div>


        <!-- CGPA -->

        <div class="cgpa-box">

            <h3>
                Current CGPA
            </h3>


            <div class="cgpa-value">

                <?= number_format(
                    (float)$cgpa,
                    2
                ) ?>

            </div>

        </div>


    </div>


</div>


<footer class="footer">

    Department Selection System © 2026

    <br>

    Debre Markos University

</footer>


</body>

</html>