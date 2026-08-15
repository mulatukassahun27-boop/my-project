<?php

// =====================================================
// START SESSION
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =====================================================
// CHECK LOGIN
// =====================================================

if (!function_exists('isLoggedIn')) {

    function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

}


// =====================================================
// GET CURRENT ROLE
// =====================================================

if (!function_exists('getUserRole')) {

    function getUserRole()
    {
        return strtolower(trim($_SESSION['role'] ?? ''));
    }

}


// =====================================================
// REQUIRE LOGIN
// =====================================================

if (!function_exists('requireLogin')) {

    function requireLogin()
    {
        if (!isLoggedIn()) {

            header("Location: ../login.php");
            exit();

        }
    }

}


// =====================================================
// REQUIRE SPECIFIC ROLE
// =====================================================

if (!function_exists('requireRole')) {

    function requireRole($requiredRole)
    {
        requireLogin();

        $currentRole = getUserRole();

        if ($currentRole !== strtolower(trim($requiredRole))) {

            header("Location: ../index.php");
            exit();

        }
    }

}


// =====================================================
// REQUIRE STUDENT
// =====================================================

if (!function_exists('requireStudent')) {

    function requireStudent()
    {
        requireRole('student');
    }

}


// =====================================================
// REQUIRE REGISTRAR
// =====================================================

if (!function_exists('requireRegistrar')) {

    function requireRegistrar()
    {
        requireRole('registrar');
    }

}


// =====================================================
// REQUIRE DEPARTMENT HEAD
// =====================================================

if (!function_exists('requireDepartmentHead')) {

    function requireDepartmentHead()
    {
        requireRole('department_head');
    }

}


// =====================================================
// REQUIRE ADVISOR
// =====================================================

if (!function_exists('requireAdvisor')) {

    function requireAdvisor()
    {
        requireRole('advisor');
    }

}


// =====================================================
// REQUIRE ADMIN
// =====================================================

if (!function_exists('requireAdmin')) {

    function requireAdmin()
    {
        requireRole('admin');
    }

}


// =====================================================
// LOGOUT
// =====================================================

if (!function_exists('logout')) {

    function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: ../login.php");
        exit();
    }

}