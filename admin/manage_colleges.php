<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();


$message="";


// Add College

if(isset($_POST['add']))
{

    $name=$_POST['college_name'];

    $code=$_POST['college_code'];



    $check=$conn->prepare(
        "SELECT id FROM colleges WHERE college_code=?"
    );


    $check->bind_param(
        "s",
        $code
    );


    $check->execute();



    if($check->get_result()->num_rows>0)
    {

        $message="College code already exists.";

    }

    else
    {


        $insert=$conn->prepare(
            "INSERT INTO colleges
            (college_name,college_code)
            VALUES(?,?)"
        );


        $insert->bind_param(
            "ss",
            $name,
            $code
        );


        if($insert->execute())
        {
            $message="College added successfully.";
        }


    }


}



// Delete College

if(isset($_GET['delete']))
{

    $id=$_GET['delete'];


    $delete=$conn->prepare(
        "DELETE FROM colleges WHERE id=?"
    );


    $delete->bind_param(
        "i",
        $id
    );


    $delete->execute();


    header("location:manage_colleges.php");

    exit();

}



// Get Colleges

$colleges=mysqli_query(
$conn,
"SELECT * FROM colleges ORDER BY id DESC"
);


?>



<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<title>Manage Colleges</title>



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

margin:5px;

}



button{

background:#333366;

color:white;

padding:10px 20px;

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



.delete{

background:red;

color:white;

padding:7px;

text-decoration:none;

border-radius:4px;

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
Manage Colleges
</h2>


<hr>


<?php

if($message!="")
{

echo "<p style='color:green;font-weight:bold;'>$message</p>";

}

?>



<h3>
Add College
</h3>


<form method="POST">


<input

type="text"

name="college_name"

placeholder="College Name"

required>



<input

type="text"

name="college_code"

placeholder="College Code"

required>



<button name="add">

Add College

</button>



</form>




<h3>
College List
</h3>



<table>


<tr>

<th>No</th>

<th>College Name</th>

<th>Code</th>

<th>Date</th>

<th>Action</th>

</tr>




<?php


$count=1;


while($row=mysqli_fetch_assoc($colleges))

{


?>


<tr>


<td>

<?php echo $count++; ?>

</td>


<td>

<?php echo htmlspecialchars($row['college_name']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['college_code']); ?>

</td>


<td>

<?php echo htmlspecialchars($row['created_at']); ?>

</td>


<td>


<a class="delete"

href="manage_colleges.php?delete=<?php echo $row['id']; ?>"

onclick="return confirm('Delete college?')">

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