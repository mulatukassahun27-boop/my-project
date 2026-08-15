<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debre Markos University - Department Selection System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }

        body { background: #f4f6f9; }

        /* ===== HEADER / NAVBAR ===== */
        header {
            background: #003366;
            color: white;
            padding: 0 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* DMU emblem using CSS */
        .dmu-emblem {
            width: 46px;
            height: 46px;
            background: #ffcc00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            color: #003366;
            flex-shrink: 0;
            border: 3px solid white;
        }

        .logo-text { line-height: 1.2; }
        .logo-text strong { font-size: 16px; display: block; }
        .logo-text small  { font-size: 11px; opacity: 0.85; }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 22px;
            font-size: 14px;
            padding: 6px 0;
            border-bottom: 2px solid transparent;
            transition: border-color 0.2s;
        }

        nav a:hover { color: #ffcc00; border-bottom-color: #ffcc00; }

        .nav-btn {
            background: #ffcc00;
            color: #003366 !important;
            padding: 8px 18px !important;
            border-radius: 5px;
            font-weight: bold;
            border-bottom: none !important;
            margin-left: 12px;
        }

        .nav-btn:hover { background: white !important; }

        /* ===== HERO SECTION ===== */
        .hero {
            position: relative;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }

        /* Background image — shows dmu.jpg if present, falls back to gradient */
        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(0,30,80,0.82), rgba(0,30,80,0.82)),
                url('assets/images/dmu.jpg') center center / cover no-repeat;
            background-color: #003366; /* solid fallback color */
        }

        /* Decorative pattern overlay */
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,204,0,0.08) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            padding: 60px 30px;
            max-width: 800px;
        }

        /* University badge inside hero */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,204,0,0.5);
            padding: 10px 20px;
            border-radius: 50px;
            margin-bottom: 28px;
            backdrop-filter: blur(4px);
        }

        .hero-badge .badge-emblem {
            width: 38px;
            height: 38px;
            background: #ffcc00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #003366;
            font-size: 13px;
            flex-shrink: 0;
        }

        .hero-badge span { font-size: 14px; color: rgba(255,255,255,0.9); }

        .hero-content h1 {
            font-size: 38px;
            font-weight: bold;
            margin-bottom: 16px;
            line-height: 1.3;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .hero-content h1 span { color: #ffcc00; }

        .hero-content p {
            font-size: 17px;
            opacity: 0.88;
            margin-bottom: 36px;
            line-height: 1.6;
        }

        .hero-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

        .btn-primary {
            padding: 14px 32px;
            background: #ffcc00;
            color: #003366;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-primary:hover { background: white; transform: translateY(-2px); }

        .btn-outline {
            padding: 14px 32px;
            background: transparent;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            border: 2px solid rgba(255,255,255,0.6);
            transition: border-color 0.2s, background 0.2s;
        }

        .btn-outline:hover { border-color: white; background: rgba(255,255,255,0.1); }

        /* ===== STATS BAR ===== */
        .stats-bar {
            background: #003366;
            display: flex;
            justify-content: center;
            gap: 0;
        }

        .stat-item {
            flex: 1;
            max-width: 220px;
            text-align: center;
            padding: 20px;
            border-right: 1px solid rgba(255,255,255,0.15);
            color: white;
        }

        .stat-item:last-child { border-right: none; }
        .stat-item h3 { font-size: 26px; color: #ffcc00; margin-bottom: 4px; }
        .stat-item p  { font-size: 13px; opacity: 0.8; }

        /* ===== UNIVERSITY IMAGE SECTION ===== */
        .uni-section {
            display: flex;
            align-items: center;
            gap: 50px;
            max-width: 1100px;
            margin: 60px auto;
            padding: 0 30px;
        }

        .uni-image-wrap {
            flex: 1;
            min-width: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            position: relative;
        }

        /* The real DMU photo — place dmu.jpg in assets/images/ */
        .uni-image-wrap img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }

        /* Fallback shown when image is missing */
        .uni-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #003366 0%, #00509e 50%, #003366 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .uni-image-placeholder .big-emblem {
            width: 90px;
            height: 90px;
            background: #ffcc00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 16px;
            border: 4px solid rgba(255,255,255,0.4);
        }

        .uni-image-placeholder h3 { font-size: 18px; margin-bottom: 6px; }
        .uni-image-placeholder p  { font-size: 13px; opacity: 0.75; }

        .uni-text { flex: 1; }
        .uni-text h2 { color: #003366; font-size: 28px; margin-bottom: 14px; }
        .uni-text p  { color: #555; line-height: 1.8; margin-bottom: 14px; }

        .uni-text .highlight {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .uni-text .highlight-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .uni-text .highlight p { margin: 0; color: #444; font-size: 14px; }

        /* ===== SERVICES SECTION ===== */
        .section {
            padding: 55px 30px;
            text-align: center;
            background: white;
        }

        .section h2 {
            color: #003366;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .section .subtitle {
            color: #6b7280;
            margin-bottom: 40px;
            font-size: 15px;
        }

        .cards {
            display: flex;
            justify-content: center;
            gap: 28px;
            flex-wrap: wrap;
            max-width: 1050px;
            margin: 0 auto;
        }

        .card {
            background: #f8fafc;
            width: 300px;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            border-top: 4px solid #003366;
            text-align: left;
        }

        .card-icon { font-size: 36px; margin-bottom: 14px; }
        .card h3   { color: #003366; margin-bottom: 10px; font-size: 18px; }
        .card p    { color: #555; font-size: 14px; line-height: 1.7; }

        /* ===== HOW IT WORKS ===== */
        .how-section {
            padding: 55px 30px;
            text-align: center;
            background: #f4f6f9;
        }

        .how-section h2 { color: #003366; font-size: 28px; margin-bottom: 8px; }
        .how-section .subtitle { color: #6b7280; margin-bottom: 40px; font-size: 15px; }

        .steps {
            display: flex;
            justify-content: center;
            gap: 0;
            flex-wrap: wrap;
            max-width: 900px;
            margin: 0 auto;
        }

        .step {
            flex: 1;
            min-width: 180px;
            max-width: 220px;
            text-align: center;
            padding: 20px;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '→';
            position: absolute;
            right: -10px;
            top: 28px;
            font-size: 24px;
            color: #003366;
            opacity: 0.4;
        }

        .step-num {
            width: 52px;
            height: 52px;
            background: #003366;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin: 0 auto 14px;
        }

        .step h4 { color: #003366; margin-bottom: 6px; }
        .step p  { color: #6b7280; font-size: 13px; }

        /* ===== FOOTER ===== */
        footer {
            background: #003366;
            color: white;
            text-align: center;
            padding: 28px 20px;
        }

        footer p { opacity: 0.85; font-size: 14px; margin-bottom: 6px; }
        footer small { opacity: 0.55; font-size: 12px; }

        /* ===== RESPONSIVE ===== */
        @media(max-width: 800px) {
            header { padding: 0 20px; }
            .hero-content h1 { font-size: 26px; }
            .uni-section { flex-direction: column; }
            .stats-bar { flex-wrap: wrap; }
            .step:not(:last-child)::after { display: none; }
            nav a { margin-left: 12px; font-size: 13px; }
        }

        @media(max-width: 500px) {
            .logo-text strong { font-size: 13px; }
            .hero-content h1  { font-size: 22px; }
        }
    </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
    <div class="logo-area">
        <div class="dmu-emblem">DMU</div>
        <div class="logo-text">
            <strong>Debre Markos University</strong>
            <small>Department Selection System</small>
        </div>
    </div>
    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="nav-btn">Register</a>
    </nav>
</header>


<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">

        <div class="hero-badge">
            <div class="badge-emblem">DMU</div>
            <span>Debre Markos University &mdash; Est. 2007</span>
        </div>

        <h1>
            Department Selection &amp;<br>
            <span>Placement Management</span> System
        </h1>

        <p>
            A secure digital platform for student department preference
            selection and automated placement management at
            Debre Markos University.
        </p>

        <div class="hero-buttons">
            <a href="login.php"    class="btn-primary">Login to Your Account</a>
            <a href="register.php" class="btn-outline">Create Student Account</a>
        </div>

    </div>
</section>


<!-- ===== STATS BAR ===== -->
<div class="stats-bar">
    <div class="stat-item">
        <h3>12+</h3>
        <p>Departments</p>
    </div>
    <div class="stat-item">
        <h3>5</h3>
        <p>Colleges</p>
    </div>
    <div class="stat-item">
        <h3>3</h3>
        <p>User Roles</p>
    </div>
    <div class="stat-item">
        <h3>100%</h3>
        <p>Online Process</p>
    </div>
</div>


<!-- ===== UNIVERSITY SECTION ===== -->
<div class="uni-section">

    <div class="uni-image-wrap">
        <?php
        // Show real image if it exists, otherwise show styled placeholder
        $imgPath = __DIR__ . '/assets/images/dmu.jpg';
        if (file_exists($imgPath)):
        ?>
            <img src="assets/images/dmu.jpg" alt="Debre Markos University Campus">
        <?php else: ?>
            <div class="uni-image-placeholder">
                <div class="big-emblem">DMU</div>
                <h3>Debre Markos University</h3>
                <p>Place dmu.jpg in assets/images/ to show the campus photo here</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="uni-text">
        <h2>About Debre Markos University</h2>
        <p>
            Debre Markos University is a public university located in
            Debre Markos, Amhara Region, Ethiopia. Established in 2007,
            the university offers undergraduate and postgraduate programs
            across multiple colleges.
        </p>

        <div class="highlight">
            <span class="highlight-icon">🎓</span>
            <p>Undergraduate and postgraduate programs across 5 colleges and 12+ departments.</p>
        </div>
        <div class="highlight">
            <span class="highlight-icon">🏛️</span>
            <p>This system digitizes the department selection and placement process for all students.</p>
        </div>
        <div class="highlight">
            <span class="highlight-icon">📊</span>
            <p>Automated CGPA-based placement algorithm with full transparency and fairness.</p>
        </div>
    </div>

</div>


<!-- ===== SERVICES ===== -->
<section class="section">
    <h2>System Portals</h2>
    <p class="subtitle">Three dedicated portals serving every user of the system</p>

    <div class="cards">

        <div class="card">
            <div class="card-icon">🎓</div>
            <h3>Student Portal</h3>
            <p>
                Register, log in, submit up to 3 department preferences,
                view your choices, and check your placement result once published.
            </p>
        </div>

        <div class="card">
            <div class="card-icon">📋</div>
            <h3>Registrar Portal</h3>
            <p>
                Manage departments, set quotas per academic year,
                run the placement algorithm, and publish results to students.
            </p>
        </div>

        <div class="card">
            <div class="card-icon">⚙️</div>
            <h3>Admin Portal</h3>
            <p>
                Manage user accounts, assign roles, configure colleges,
                academic years, system settings and view full reports.
            </p>
        </div>

    </div>
</section>


<!-- ===== HOW IT WORKS ===== -->
<section class="how-section">
    <h2>How It Works</h2>
    <p class="subtitle">Simple steps from registration to placement</p>

    <div class="steps">

        <div class="step">
            <div class="step-num">1</div>
            <h4>Register</h4>
            <p>Create your student account with your Student ID and details.</p>
        </div>

        <div class="step">
            <div class="step-num">2</div>
            <h4>Select Departments</h4>
            <p>Choose your top 3 preferred departments in order of priority.</p>
        </div>

        <div class="step">
            <div class="step-num">3</div>
            <h4>Placement Runs</h4>
            <p>The registrar runs the CGPA-based automated placement algorithm.</p>
        </div>

        <div class="step">
            <div class="step-num">4</div>
            <h4>View Result</h4>
            <p>Log in and view your department placement result after it is published.</p>
        </div>

    </div>
</section>


<!-- ===== FOOTER ===== -->
<footer>
    <p>Debre Markos University &mdash; Department Selection and Placement Management System</p>
    <p>Debre Markos, Amhara Region, Ethiopia &nbsp;|&nbsp; info@dmu.edu.et</p>
    <br>
    <small>© 2026 All rights reserved</small>
</footer>

</body>
</html>
