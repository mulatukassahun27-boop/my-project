<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php
include '../config/session.php';
include '../config/database.php';

$user_id = $_SESSION['user_id'];

$query = "
SELECT 
    sc.*,
    d1.department_name AS first_department,
    d2.department_name AS second_department,
    d3.department_name AS third_department
FROM student_choices sc

JOIN departments d1 
ON sc.first_choice = d1.id

JOIN departments d2 
ON sc.second_choice = d2.id

JOIN departments d3 
ON sc.third_choice = d3.id

WHERE sc.student_id = ?
";


$stmt = $conn->prepare($query);
$stmt->bind_param("i",$user_id);
$stmt->execute();

$result = $stmt->get_result();

$choice = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>My Department Choices</title>

<link rel="stylesheet" href="../assets/css/style.css">


<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
}


.sidebar{

    width:250px;
    height:100vh;
    background:#003366;
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
    text-decoration:none;
    padding:15px;

}


.sidebar a:hover{

    background:#00509e;

}



.main{

    margin-left:250px;
    padding:20px;

}



.card{

    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 2px 5px rgba(0,0,0,.2);

}



table{

    width:100%;
    border-collapse:collapse;

}


table th{

    background:#003366;
    color:white;
    padding:12px;

}


table td{

    border:1px solid #ddd;
    padding:12px;

}



.btn{

    display:inline-block;
    background:#003366;
    color:white;
    padding:10px 20px;
    text-decoration:none;
    border-radius:5px;

}



.btn:hover{

    background:#00509e;

}


.empty{

    color:red;
    font-weight:bold;

}


</style>


</head>


<body>


<div class="sidebar">

<h2>Student Panel</h2>


<a href="dashboard.php">
Dashboard
</a>


<a href="profile.php">
My Profile
</a>


<a href="edit_profile.php">
Edit Profile
</a>


<a href="select_department.php">
Department Selection
</a>


<a href="my_choices.php">
My Choices
</a>


<a href="placement_result.php">
Placement Result
</a>


<a href="change_password.php">
Change Password
</a>


<a href="../logout.php">
Logout
</a>


</div>



<div class="main">


<div class="card">


<h2>
My Department Choices
</h2>


<hr><br>


<?php if($choice){ ?>


<table>


<tr>

<th>
Priority
</th>

<th>
Department
</th>

<th>
Submitted Date
</th>

</tr>



<tr>

<td>
First Choice
</td>

<td>
<?php echo htmlspecialchars($choice['first_department']); ?>
</td>

<td rowspan="3">
<?php echo htmlspecialchars($choice['submitted_at']); ?>
</td>

</tr>



<tr>

<td>
Second Choice
</td>

<td>
<?php echo htmlspecialchars($choice['second_department']); ?>
</td>

</tr>



<tr>

<td>
Third Choice
</td>

<td>
<?php echo htmlspecialchars($choice['third_department']); ?>
</td>

</tr>



</table>


<br>


<h3>
Placement Status
</h3>


<p>
Status:
<span style="color:orange;">
Pending
</span>
</p>


<p>
The registrar has not published placement results yet.
</p>



<?php } else { ?>


<p class="empty">

You have not submitted department choices yet.

</p>


<a href="select_department.php" class="btn">

Select Department

</a>


<?php } ?>



<br><br>


<a href="dashboard.php" class="btn">

Back Dashboard

</a>



</div>


</div>


</body>

</html>