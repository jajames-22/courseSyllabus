<?php
session_start();
require_once "../class/accounts.php";
$accObj = new Accounts();

$account = [];
$errors = [];

$account["lname"] = $_SESSION["lname"] ?? "";
$account["fname"] = $_SESSION["fname"] ?? "";
$account["mname"] = $_SESSION["mname"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account["lname"] = trim(htmlspecialchars($_POST["lname"] ?? $_SESSION["lname"] ?? ""));
    $account["fname"] = trim(htmlspecialchars($_POST["fname"] ?? ""));
    $account["mname"] = trim(htmlspecialchars($_POST["mname"] ?? ""));

    if (empty($account["lname"])) $errors["lname"] = "Last name is required";
    if (empty($account["fname"])) $errors["fname"] = "First name is required";

    if(empty(array_filter($errors))) {
        $_SESSION["fname"] = $account["fname"];
        $_SESSION["mname"] = $account["mname"];
        $_SESSION["lname"] = $account["lname"];
        header("Location: register2.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/register.css">
    <title>Register</title>
</head>
<body>
    <div class="l-login">
        <img style="height: 50px; margin-bottom: 20px;" src="../styles/col_logo/19.png" alt="">
        <h1>WMSU Course Syllabus Approval Portal</h1>
        <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
    </div>
    <div> 
        <h1 class="mb1">Register</h1>

        <div class="progress-bar mb1">
            <div class="bar active-bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <h3 class="mb1">Let's proccess your name</h3>
        <form action="register.php" method="POST">

        <div class="input-g">
            <label for="lname">Last Name:</label><br>
            <input type="text" id="lname" name="lname" value="<?=$account["lname"] ?? "" ?>"><br>
            <p style="color:red;"><?= $errors['lname'] ?? ''; ?></p>
        </div>

        <div class="input-g">
            <label for="fname">First Name:</label><br>
            <input type="text" id="fname" name="fname" value="<?=$account["fname"] ?? ""?>"><br>
            <p style="color:red;"><?= $errors['fname'] ?? ''; ?></p>
        </div>

        <div class="input-g">
            <label for="mname">Middle Name:</label><br>
            <input type="text" id="mname" name="mname" value="<?=$account["mname"] ?? ""?>"><br>
            <p style="color:red;"><?= $errors['mname'] ?? ''; ?></p>
        </div>
        
        <div class="sum-btn">
            <button class="bck-btn" type="button" onclick="location.href='index.php'">Back to Home</button>
            <input class="nxt-btn" type="submit" value="Next">
        </div>
        </form>
    </div>
    
</body>
</html>