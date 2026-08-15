<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php

include '../config/session.php';
include '../config/database.php';


$user_id = $_SESSION['user_id'];


// Get notifications

$query = "

SELECT * FROM notifications

WHERE user_id=? OR user_id IS NULL

ORDER BY created_at DESC

";


$stmt = $conn->prepare($query);

$stmt->bind_param("i",$user_id);

$stmt->execute();

$notifications = $stmt->get_result();


?>


<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">

<title>Notifications</title>


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

box-shadow:0 2px 5px rgba(0,0,0,.2);

}



.notification{

border:1px solid #ddd;

padding:15px;

margin-bottom:15px;

border-radius:8px;

}



.notification h3{

margin:0;

color:#003366;

}



.date{

color:gray;

font-size:14px;

}



.message{

margin-top:10px;

}



.btn{

display:inline-block;

padding:10px 20px;

background:#003366;

color:white;

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


<a href="notifications.php">
Notifications
</a>


<a href="../logout.php">
Logout
</a>


</div>





<div class="main">


<div class="card">


<h2>
My Notifications
</h2>


<hr><br>




<?php


if($notifications->num_rows > 0)

{


while($row=$notifications->fetch_assoc())

{


?>


<div class="notification">


<h3>

<?php echo htmlspecialchars($row['title']); ?>

</h3>



<p class="date">

<?php echo htmlspecialchars($row['created_at']); ?>

</p>



<p class="message">

<?php echo htmlspecialchars($row['message']); ?>

</p>



</div>



<?php


}


}

else

{


?>


<p class="empty">

No notifications available.

</p>


<?php


}


?>



<br>


<a href="dashboard.php" class="btn">

Back Dashboard

</a>



</div>


</div>



</body>


</html>