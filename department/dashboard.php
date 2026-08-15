<?php

require_once '../config/session.php';

requireRole('department_head');

?>
<?php
include '../config/session.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Department Dashboard</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="card">

<h1>Department Dashboard</h1>

<hr><br>

<h2>Welcome,
<?php echo $_SESSION['full_name']; ?>
</h2>

<br>

<p><strong>User ID:</strong>
<?php echo $_SESSION['user_id']; ?>
</p>

<p><strong>Username:</strong>
<?php echo $_SESSION['username']; ?>
</p>

<p><strong>Role:</strong>
<?php echo ucfirst($_SESSION['role']); ?>
</p>

<p><strong>Status:</strong> Logged In</p>

<br>

<h3>Department Menu</h3>

<ul>

<li><a href="students.php">Department Students</a></li>

<li><a href="capacity.php">Department Capacity</a></li>

<li><a href="reports.php">Department Reports</a></li>

</ul>

<br>

<a class="btn" href="../logout.php">Logout</a>

</div>

</div>

</body>

</html>