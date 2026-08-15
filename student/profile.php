<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php
include '../config/session.php';
include '../config/database.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Profile</title>

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
    position:fixed;
    left:0;
    top:0;
    background:#003366;
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
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,.2);
}

table{
    width:100%;
    border-collapse:collapse;
}

table td{
    border:1px solid #ddd;
    padding:12px;
}

table td:first-child{
    font-weight:bold;
    width:220px;
    background:#f0f0f0;
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

</style>

</head>

<body>

<div class="sidebar">

<h2>Student Panel</h2>

<a href="dashboard.php">Dashboard</a>

<a href="profile.php">My Profile</a>

<a href="edit_profile.php">Edit Profile</a>

<a href="select_department.php">Department Selection</a>

<a href="my_choices.php">My Choices</a>

<a href="placement_result.php">Placement Result</a>

<a href="change_password.php">Change Password</a>

<a href="../logout.php">Logout</a>

</div>

<div class="main">

<div class="card">

<h2>Student Profile</h2>

<hr><br>

<table>

<tr>
<td>Full Name</td>
<td><?php echo htmlspecialchars($user['full_name']); ?></td>
</tr>

<tr>
<td>Student ID</td>
<td><?php echo htmlspecialchars($user['student_id']); ?></td>
</tr>

<tr>
<td>Gender</td>
<td><?php echo htmlspecialchars($user['gender']); ?></td>
</tr>

<tr>
<td>Email</td>
<td><?php echo htmlspecialchars($user['email']); ?></td>
</tr>

<tr>
<td>Phone</td>
<td><?php echo htmlspecialchars($user['phone']); ?></td>
</tr>

<tr>
<td>Username</td>
<td><?php echo htmlspecialchars($user['username']); ?></td>
</tr>

<tr>
<td>Role</td>
<td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
</tr>

<tr>
<td>Status</td>
<td><?php echo htmlspecialchars($user['status']); ?></td>
</tr>

<tr>
<td>Registered On</td>
<td><?php echo htmlspecialchars($user['created_at']); ?></td>
</tr>

</table>

<br>

<a href="edit_profile.php" class="btn">Edit Profile</a>

<a href="dashboard.php" class="btn">Back Dashboard</a>

</div>

</div>

</body>

</html>