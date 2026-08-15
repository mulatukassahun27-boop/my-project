<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php
include '../config/session.php';
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Update Profile
if(isset($_POST['update']))
{
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $gender    = $_POST['gender'];

    if(empty($full_name) || empty($email) || empty($gender))
    {
        $message = "Please fill all required fields.";
    }
    else
    {
        // Check if email already exists for another user
        $check = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();

        if($check->get_result()->num_rows > 0)
        {
            $message = "Email already exists.";
        }
        else
        {
            $update = $conn->prepare("UPDATE users SET full_name=?, gender=?, email=?, phone=? WHERE id=?");
            $update->bind_param("ssssi", $full_name, $gender, $email, $phone, $user_id);

            if($update->execute())
            {
                $_SESSION['full_name'] = $full_name;
                $message = "Profile updated successfully.";
            }
            else
            {
                $message = "Update failed.";
            }
        }
    }
}

// Load current user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

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
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,.2);
}

input,select{
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

<h2>Edit Profile</h2>

<hr>

<?php
if($message!="")
{
    if(strpos($message,"successfully")!==false)
        echo "<p class='success'>$message</p>";
    else
        echo "<p class='error'>$message</p>";
}
?>

<form method="POST">

<label>Full Name</label>

<input
type="text"
name="full_name"
value="<?php echo htmlspecialchars($user['full_name']); ?>"
required>

<label>Gender</label>

<select name="gender" required>

<option value="Male" <?php if($user['gender']=="Male") echo "selected"; ?>>Male</option>

<option value="Female" <?php if($user['gender']=="Female") echo "selected"; ?>>Female</option>

</select>

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo htmlspecialchars($user['email']); ?>"
required>

<label>Phone</label>

<input
type="text"
name="phone"
value="<?php echo htmlspecialchars($user['phone']); ?>">

<br>

<button
type="submit"
name="update">

Update Profile

</button>

<a href="profile.php" class="btn">Cancel</a>

</form>

</div>

</div>

</body>
</html>