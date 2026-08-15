<?php

require_once '../config/session.php';

requireRole('admin');

?>


<?php

include '../config/session.php';
include '../config/database.php';


$message="";


// Update Role

if(isset($_POST['update_role']))
{

    $id=$_POST['user_id'];

    $role=$_POST['role'];


    $update=$conn->prepare(
        "UPDATE users SET role=? WHERE id=?"
    );


    $update->bind_param(
        "si",
        $role,
        $id
    );


    if($update->execute())
    {
        $message="Role updated successfully.";
    }

}



// Get Users

$users=mysqli_query(
$conn,
"SELECT id,full_name,email,username,role,status 
FROM users
ORDER BY id DESC"
);


?>



<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<title>Manage Roles</title>



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



.card{

background:white;

padding:25px;

border-radius:10px;

box-shadow:0 2px 5px rgba(0,0,0,.2);

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



select{

padding:8px;

}



button{

background:#333366;

color:white;

border:none;

padding:8px 15px;

cursor:pointer;

}



.success{

color:green;

font-weight:bold;

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
Manage User Roles
</h2>


<hr>



<?php

if($message!="")
{

echo "<p class='success'>$message</p>";

}

?>




<table>


<tr>


<th>
No
</th>


<th>
Name
</th>


<th>
Email
</th>


<th>
Username
</th>


<th>
Current Role
</th>


<th>
Change Role
</th>


</tr>



<?php


$count=1;


while($row=mysqli_fetch_assoc($users))

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


<form method="POST">


<input 
type="hidden"
name="user_id"
value="<?php echo $row['id']; ?>">



<select name="role">


<option value="student"
<?php if($row['role']=="student") echo "selected"; ?>>

Student

</option>



<option value="registrar"
<?php if($row['role']=="registrar") echo "selected"; ?>>

Registrar

</option>



<option value="admin"
<?php if($row['role']=="admin") echo "selected"; ?>>

Admin

</option>



</select>



<button name="update_role">

Update

</button>



</form>



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