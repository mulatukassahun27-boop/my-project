<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();


// Total Users

$users = mysqli_query(
$conn,
"SELECT COUNT(*) AS total FROM users"
);

$total_users = mysqli_fetch_assoc($users)['total'];



// Total Students

$students = mysqli_query(
$conn,
"SELECT COUNT(*) AS total FROM users WHERE role='student'"
);

$total_students = mysqli_fetch_assoc($students)['total'];



// Total Registrar

$registrars = mysqli_query(
$conn,
"SELECT COUNT(*) AS total FROM users WHERE role='registrar'"
);

$total_registrar = mysqli_fetch_assoc($registrars)['total'];



// Total Departments

$departments = mysqli_query(
$conn,
"SELECT COUNT(*) AS total FROM departments"
);

$total_departments = mysqli_fetch_assoc($departments)['total'];

?>



<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>Admin Dashboard</title>


<style>


body{

margin:0;

font-family:Arial;

background:#f4f6f9;

}



.sidebar{

width:250px;

height:100vh;

background:#333366;

position:fixed;

left:0;

top:0;

}



.sidebar h2{

color:white;

text-align:center;

padding:20px;

}



.sidebar a{

display:block;

color:white;

padding:15px;

text-decoration:none;

}



.sidebar a:hover{

background:#555599;

}



.main{

margin-left:250px;

padding:20px;

}



.welcome{

background:white;

padding:20px;

border-radius:10px;

box-shadow:0 2px 5px rgba(0,0,0,.2);

margin-bottom:20px;

}



.cards{

display:grid;

grid-template-columns:repeat(4,1fr);

gap:20px;

}



.card{

background:white;

padding:25px;

border-radius:10px;

text-align:center;

box-shadow:0 2px 5px rgba(0,0,0,.2);

}



.card h1{

color:#333366;

}



</style>


</head>


<body>



<div class="sidebar">


<h2>
Admin Panel
</h2>


<a href="dashboard.php">
Dashboard
</a>


<a href="manage_users.php">
Manage Users
</a>

<a href="create_user.php">
Create User
</a>

<a href="manage_roles.php">
Manage Roles
</a>


<a href="manage_colleges.php">
Manage Colleges
</a>


<a href="manage_academic_year.php">
Academic Year
</a>


<a href="settings.php">
System Settings
</a>


<a href="contact_messages.php">
Contact Messages
</a>


<a href="reports.php">
Reports
</a>


<a href="../logout.php">
Logout
</a>



</div>






<div class="main">



<div class="welcome">


<h2>

Welcome Administrator

</h2>


<p>

Manage the complete Department Selection and Placement Management System.

</p>


</div>





<div class="cards">



<div class="card">


<h1>

<?php echo $total_users; ?>

</h1>


<p>

Total Users

</p>


</div>





<div class="card">


<h1>

<?php echo $total_students; ?>

</h1>


<p>

Students

</p>


</div>





<div class="card">


<h1>

<?php echo $total_registrar; ?>

</h1>


<p>

Registrar Accounts

</p>


</div>





<div class="card">


<h1>

<?php echo $total_departments; ?>

</h1>


<p>

Departments

</p>


</div>



</div>




</div>



</body>

</html>