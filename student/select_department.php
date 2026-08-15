<?php

require_once '../config/session.php';

requireRole('student');

?>
<?php
include '../config/session.php';
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Check if already submitted
$check = $conn->prepare("SELECT * FROM student_choices WHERE student_id=?");
$check->bind_param("i",$user_id);
$check->execute();
$already = $check->get_result()->num_rows;

if(isset($_POST['submit']) && $already==0)
{

    $choice1=$_POST['choice1'];
    $choice2=$_POST['choice2'];
    $choice3=$_POST['choice3'];

    if($choice1==$choice2 || $choice1==$choice3 || $choice2==$choice3)
    {
        $message="Each department choice must be different.";
    }
    else
    {

        $insert=$conn->prepare("
        INSERT INTO student_choices
        (student_id,first_choice,second_choice,third_choice)
        VALUES(?,?,?,?)
        ");

        $insert->bind_param(
            "iiii",
            $user_id,
            $choice1,
            $choice2,
            $choice3
        );

        if($insert->execute())
        {
            $message="Department choices submitted successfully.";
            $already=1;
        }
        else
        {
            $message="Submission failed.";
        }

    }

}

$departments=mysqli_query($conn,"SELECT * FROM departments WHERE status='Active'");
?>

<!DOCTYPE html>
<html>
<head>

<title>Department Selection</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="container">

<div class="card">

<h2>Department Selection</h2>

<hr><br>

<?php
if($message!="")
{
    echo "<p style='color:green;'>$message</p>";
}
?>

<?php if($already==0){ ?>

<form method="POST">

<label>First Choice</label>

<select name="choice1" required>

<option value="">Select Department</option>

<?php
mysqli_data_seek($departments,0);
while($row=mysqli_fetch_assoc($departments)){
?>

<option value="<?php echo $row['id']; ?>">
<?php echo $row['department_name']; ?>
</option>

<?php } ?>

</select>

<label>Second Choice</label>

<select name="choice2" required>

<option value="">Select Department</option>

<?php
mysqli_data_seek($departments,0);
while($row=mysqli_fetch_assoc($departments)){
?>

<option value="<?php echo $row['id']; ?>">
<?php echo $row['department_name']; ?>
</option>

<?php } ?>

</select>

<label>Third Choice</label>

<select name="choice3" required>

<option value="">Select Department</option>

<?php
mysqli_data_seek($departments,0);
while($row=mysqli_fetch_assoc($departments)){
?>

<option value="<?php echo $row['id']; ?>">
<?php echo $row['department_name']; ?>
</option>

<?php } ?>

</select>

<br><br>

<button type="submit" name="submit">
Submit Choices
</button>

</form>

<?php } else { ?>

<h3 style="color:green;">
You have already submitted your department choices.
</h3>

<a href="my_choices.php" class="btn">
View My Choices
</a>

<?php } ?>

<br><br>

<a href="dashboard.php" class="btn">
Back Dashboard
</a>

</div>

</div>

</body>

</html>