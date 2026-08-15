<?php

include 'config/database.php';


$message="";


if(isset($_POST['register']))
{


$full_name=$_POST['full_name'];

$student_id=$_POST['student_id'];

$gender=$_POST['gender'];

$email=$_POST['email'];

$phone=$_POST['phone'];

$username=$_POST['username'];

$password=$_POST['password'];

$confirm=$_POST['confirm'];





if($password!=$confirm)
{

$message="Password does not match.";

}

else
{


// Check duplicate

$check=$conn->prepare(

"SELECT id FROM users 
WHERE email=? OR username=?"

);


$check->bind_param(

"ss",

$email,

$username

);



$check->execute();



if($check->get_result()->num_rows>0)

{

$message="Email or username already exists.";

}

else
{


$hash=password_hash(
$password,
PASSWORD_DEFAULT
);



$role="student";

$status="Active";



$insert=$conn->prepare(

"INSERT INTO users

(full_name,
student_id,
gender,
email,
phone,
username,
password,
role,
status)

VALUES(?,?,?,?,?,?,?,?,?)"

);



$insert->bind_param(

"sssssssss",

$full_name,

$student_id,

$gender,

$email,

$phone,

$username,

$hash,

$role,

$status

);





if($insert->execute())

{


header(
"location:login.php?registered=success"
);


exit();


}

else

{

$message="Registration failed.";

}



}



}



}



?>


<!DOCTYPE html>

<html>

<head>


<title>Student Registration</title>


<style>


body{

margin:0;

font-family:Arial;

background:#f4f6f9;

}



.container{

width:450px;

margin:50px auto;

background:white;

padding:30px;

border-radius:10px;

box-shadow:0 2px 8px #ccc;

}



h2{

text-align:center;

color:#003366;

}



input,select{

width:100%;

padding:12px;

margin:8px 0;

}



button{

width:100%;

padding:12px;

background:#003366;

color:white;

border:none;

cursor:pointer;

}



button:hover{

background:#00509e;

}



.error{

color:red;

text-align:center;

font-weight:bold;

}



a{

text-decoration:none;

}



</style>


</head>



<body>


<div class="container">


<h2>
Student Registration
</h2>



<?php

if($message!="")
{

echo "<p class='error'>$message</p>";

}

?>



<form method="POST">



<label>
Full Name
</label>


<input

type="text"

name="full_name"

required>





<label>
Student ID
</label>


<input

type="text"

name="student_id"

placeholder="Example: CSE/123/16"

required>





<label>
Gender
</label>


<select name="gender" required>


<option value="">
Select Gender
</option>


<option value="Male">
Male
</option>


<option value="Female">
Female
</option>


</select>





<label>
Email
</label>


<input

type="email"

name="email"

required>





<label>
Phone
</label>


<input

type="text"

name="phone"

required>





<label>
Username
</label>


<input

type="text"

name="username"

required>





<label>
Password
</label>


<input

type="password"

name="password"

required>





<label>
Confirm Password
</label>


<input

type="password"

name="confirm"

required>





<button name="register">

Create Account

</button>


</form>



<br>


<center>

Already have account?

<a href="login.php">
Login
</a>


</center>


</div>



</body>


</html>