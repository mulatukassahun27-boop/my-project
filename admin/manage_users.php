<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();


$message="";


// Change Status

if(isset($_GET['status']))
{

    $id=$_GET['id'];

    $status=$_GET['status'];


    $update=$conn->prepare(
        "UPDATE users SET status=? WHERE id=?"
    );


    $update->bind_param(
        "si",
        $status,
        $id
    );


    $update->execute();


    header("location:manage_users.php");

    exit();

}



// Delete User

if(isset($_GET['delete']))
{

    $id=$_GET['delete'];


    $delete=$conn->prepare(
        "DELETE FROM users WHERE id=?"
    );


    $delete->bind_param(
        "i",
        $id
    );


    $delete->execute();


    header("location:manage_users.php");

    exit();

}




$search="";


if(isset($_GET['search']))
{
    $search=$_GET['search'];
}



$query="

SELECT *

FROM users

WHERE

full_name LIKE ?

OR email LIKE ?

OR username LIKE ?

ORDER BY id DESC

";



$stmt=$conn->prepare($query);


$value="%".$search."%";


$stmt->bind_param(
"sss",
$value,
$value,
$value
);


$stmt->execute();


$users=$stmt->get_result();


?>



<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">

<title>Manage Users</title>



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

box-shadow:0 2px 5px rgba(0,0,0,.2);

}



input{

padding:10px;

width:300px;

}



button{

padding:10px 20px;

background:#333366;

color:white;

border:none;

cursor:pointer;

}



table{

width:100%;

border-collapse:collapse;

margin-top:20px;

}



th{

background:#333366;

color:white;

padding:12px;

}



td{

border:1px solid #ddd;

padding:10px;

}



a.action{

padding:6px 10px;

color:white;

text-decoration:none;

border-radius:4px;

}



.active{

background:green;

}



.block{

background:orange;

}



.delete{

background:red;

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
Manage Users
</h2>


<hr>



<form method="GET">


<input

type="text"

name="search"

placeholder="Search user..."

value="<?php echo htmlspecialchars($search); ?>">


<button>

Search

</button>


</form>





<table>


<tr>

<th>No</th>

<th>Name</th>

<th>Email</th>

<th>Username</th>

<th>Role</th>

<th>Status</th>

<th>Action</th>


</tr>




<?php


$count=1;


while($row=$users->fetch_assoc())

{


?>


<tr>


<td>

<?php echo $count++; ?>

</td>


<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['email']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['username']); ?>

</td>


<td>

<?php echo ucfirst($row['role']); ?>

</td>


<td>

<?php echo $row['status']; ?>

</td>


<td>



<?php if($row['status']=="Active"){ ?>


<a class="action block"

href="manage_users.php?id=<?php echo $row['id']; ?>&status=Blocked">

Block

</a>


<?php }else{ ?>


<a class="action active"

href="manage_users.php?id=<?php echo $row['id']; ?>&status=Active">

Activate

</a>


<?php } ?>



<a class="action delete"

onclick="return confirm('Delete user?')"

href="manage_users.php?delete=<?php echo $row['id']; ?>">

Delete

</a>



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