<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php

include '../config/session.php';
include '../config/database.php';

$user_id = $_SESSION['user_id'];

$message = "";


// Change Password

if(isset($_POST['change_password']))
{

    $current_password = $_POST['current_password'];

    $new_password = $_POST['new_password'];

    $confirm_password = $_POST['confirm_password'];



    // Get old password

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");

    $stmt->bind_param("i",$user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();



    if(!password_verify($current_password,$user['password']))
    {

        $message = "Current password is incorrect.";

    }

    elseif($new_password != $confirm_password)
    {

        $message = "New passwords do not match.";

    }

    elseif(strlen($new_password)<6)
    {

        $message = "Password must contain at least 6 characters.";

    }

    else
    {

        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );


        $update = $conn->prepare(
            "UPDATE users SET password=? WHERE id=?"
        );


        $update->bind_param(
            "si",
            $hashed_password,
            $user_id
        );


        if($update->execute())
        {

            $message="Password changed successfully.";

        }

        else
        {

            $message="Password change failed.";

        }


    }


}

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Change Password</title>


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

padding:15px;

text-decoration:none;

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

width:500px;

box-shadow:0 2px 5px rgba(0,0,0,.2);

}



input{

width:100%;

padding:12px;

margin:10px 0;

}



button{

background:#003366;

color:white;

padding:12px 25px;

border:none;

cursor:pointer;

}



button:hover{

background:#00509e;

}



.success{

color:green;

font-weight:bold;

}



.error{

color:red;

font-weight:bold;

}



.btn{

display:inline-block;

padding:10px 20px;

background:#003366;

color:white;

text-decoration:none;

border-radius:5px;

}



</style>


</head>


<body>


<div class="sidebar">


<h2>
Student Panel
</h2>


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
Change Password
</h2>


<hr>


<?php

if($message!="")
{

if(str_contains($message,"successfully"))
{

echo "<p class='success'>$message</p>";

}

else
{

echo "<p class='error'>$message</p>";

}

}

?>



<form method="POST">


<label>
Current Password
</label>


<input 
type="password"
name="current_password"
required>



<label>
New Password
</label>


<input 
type="password"
name="new_password"
required>



<label>
Confirm New Password
</label>


<input 
type="password"
name="confirm_password"
required>



<button 
type="submit"
name="change_password">

Change Password

</button>


</form>


<br>


<a href="dashboard.php" class="btn">

Back Dashboard

</a>


</div>


</div>



</body>

</html>