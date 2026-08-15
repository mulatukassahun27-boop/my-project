<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();
$message="";
// Add Academic Year
if(isset($_POST['add']))
{
    $year=$_POST['year_name'];
    $check=$conn->prepare(
        "SELECT id FROM academic_years WHERE year_name=?"
    );
    $check->bind_param(
        "s",
        $year
    );
    $check->execute();
    if($check->get_result()->num_rows>0)
    {
        $message="Academic year already exists.";
    }
    else
    {
        $insert=$conn->prepare(
            "INSERT INTO academic_years(year_name)
            VALUES(?)"
        );
        $insert->bind_param(
            "s",
            $year
        );
        if($insert->execute())
        {
            $message="Academic year added.";
        }
    }
}
// Activate Academic Year
if(isset($_GET['activate']))
{
    $id=$_GET['activate'];
    // deactivate all
    $conn->query(
        "UPDATE academic_years SET status='Inactive'"
    );
    // activate selected
    $update=$conn->prepare(
        "UPDATE academic_years 
        SET status='Active'
        WHERE id=?"
    );
    $update->bind_param(
        "i",
        $id
    );
    $update->execute();
    header("location:manage_academic_year.php");
    exit();
}
// Delete
if(isset($_GET['delete']))
{
    $id=$_GET['delete'];
    $delete=$conn->prepare(
        "DELETE FROM academic_years WHERE id=?"
    );
    $delete->bind_param(
        "i",
        $id
    );
    $delete->execute();
    header("location:manage_academic_year.php");
    exit();
}
// Get years
$years=mysqli_query(
$conn,
"SELECT * FROM academic_years ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Academic Year</title>
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
.active{
color:green;
font-weight:bold;
}
.activate{
background:green;
color:white;
padding:7px;
text-decoration:none;
}
.delete{
background:red;
color:white;
padding:7px;
text-decoration:none;
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
Manage Academic Year
</h2>
<hr>
<?php
if($message!="")
{
echo "<p style='color:green;font-weight:bold;'>$message</p>";
}
?>
<h3>
Add New Academic Year
</h3>
<form method="POST">
<input
type="text"
name="year_name"
placeholder="Example: 2026/27"
required>
<button name="add">
Add
</button>
</form>
<h3>
Academic Year List
</h3>
<table>
<tr>
<th>No</th>
<th>Year</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php
$count=1;
while($row=mysqli_fetch_assoc($years))
{
?>
<tr>
<td>
<?php echo $count++; ?>
</td>
<td>
<?php echo htmlspecialchars($row['year_name']); ?>
</td>
<td>
<?php
if($row['status']=="Active")
{
echo "<span class='active'>Active</span>";
}
else
{
echo "Inactive";
}
?>
</td>
<td>
<a class="activate"
href="manage_academic_year.php?activate=<?php echo $row['id']; ?>">
Activate
</a>
<a class="delete"
href="manage_academic_year.php?delete=<?php echo $row['id']; ?>"
onclick="return confirm('Delete year?')">
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