<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();


// Total Users

$total_users = mysqli_fetch_assoc(
mysqli_query($conn,
"SELECT COUNT(*) total FROM users")
)['total'];



// Students

$total_students = mysqli_fetch_assoc(
mysqli_query($conn,
"SELECT COUNT(*) total FROM users WHERE role='student'")
)['total'];



// Registrar

$total_registrar = mysqli_fetch_assoc(
mysqli_query($conn,
"SELECT COUNT(*) total FROM users WHERE role='registrar'")
)['total'];



// Departments

$total_departments = mysqli_fetch_assoc(
mysqli_query($conn,
"SELECT COUNT(*) total FROM departments")
)['total'];



// Placements

$total_placements = mysqli_fetch_assoc(
mysqli_query($conn,
"SELECT COUNT(*) total FROM placements")
)['total'];



// Department report

$report=mysqli_query($conn,

"
SELECT

d.department_name,

COUNT(p.id) AS students


FROM departments d


LEFT JOIN placements p

ON d.id=p.department_id


GROUP BY d.id

ORDER BY students DESC

"

);


?>



<!DOCTYPE html>

<html>

<head>


<title>Admin Reports</title>


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



.card{

background:white;

padding:25px;

border-radius:10px;

margin-bottom:20px;

box-shadow:0 2px 5px rgba(0,0,0,.2);

}



.cards{

display:grid;

grid-template-columns:repeat(5,1fr);

gap:15px;

}



.box{

background:#eeeeff;

padding:20px;

text-align:center;

border-radius:8px;

}



.box h1{

color:#333366;

}



table{

width:100%;

border-collapse:collapse;

}



th{

background:#333366;

color:white;

padding:12px;

}



td{

padding:10px;

border:1px solid #ddd;

}



button{

background:#333366;

color:white;

padding:10px 20px;

border:none;

cursor:pointer;

}



@media print{


.sidebar,

button{

display:none;

}


.main{

margin:0;

}


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
Settings
</a>


<a href="reports.php">
Reports
</a>


<a href="../logout.php">
Logout
</a>


</div>






<div class="main">


<div class="card">


<h2>
System Reports
</h2>


<button onclick="window.print()">

Print Report

</button>


</div>





<div class="cards">


<div class="box">

<h1>
<?php echo $total_users;?>
</h1>

<p>
Users
</p>

</div>



<div class="box">

<h1>
<?php echo $total_students;?>
</h1>

<p>
Students
</p>

</div>



<div class="box">

<h1>
<?php echo $total_registrar;?>
</h1>

<p>
Registrar
</p>

</div>



<div class="box">

<h1>
<?php echo $total_departments;?>
</h1>

<p>
Departments
</p>

</div>



<div class="box">

<h1>
<?php echo $total_placements;?>
</h1>

<p>
Placed
</p>

</div>



</div>





<div class="card">


<h2>
Department Placement Report
</h2>



<table>


<tr>

<th>
No
</th>

<th>
Department
</th>

<th>
Students
</th>

</tr>



<?php

$i=1;


while($row=mysqli_fetch_assoc($report))

{


?>


<tr>


<td>
<?php echo $i++;?>
</td>


<td>
<?php echo htmlspecialchars($row['department_name']);?>
</td>


<td>
<?php echo $row['students'];?>
</td>


</tr>


<?php

}

?>


</table>


</div>



</div>


</body>


</html>