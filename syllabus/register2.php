<?php 
session_start();

if(!isset($_SESSION["lname"]) || !isset($_SESSION["fname"])) {
    header("Location: register.php");
    exit();
}

require_once "../class/accounts.php";
$accObj = new Accounts();

$account = [];
$errors = [];

$account["email"] = $_SESSION["email"] ?? "";
$account["employeeId"] = $_SESSION["employeeId"] ?? "";
$account["password"] = $_SESSION["password"] ?? "";
$account["confirm_password"] = $_SESSION["confirm_password"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account["email"] = trim(htmlspecialchars($_POST["email"] ?? ""));
    $account["employeeId"] = trim(htmlspecialchars($_POST["employeeId"] ?? ""));
    $account["password"] = trim(htmlspecialchars($_POST["password"] ?? ""));
    $account["confirm_password"] = trim(htmlspecialchars($_POST["confirm_password"] ?? ""));

    if (empty($account["email"])) {
        $errors["email"] = "Email is required";
    } elseif ($accObj->isEmailExist($account["email"])) {
        $errors["email"] = "Email already exists";
    }

    if (empty($account["employeeId"])) {
        $errors["employeeId"] = "Employee ID is required";
    } elseif ($accObj->isEmployeeIdExist($account["employeeId"])) {
        $errors["employeeId"] = "Employee ID already exists";
    }

    if (empty($account["password"])) {
        $errors["password"] = "Password is required";
    } elseif (strlen($account["password"]) < 8) {
        $errors["password"] = "Password must be at least 8 characters long";
    }

    if (empty($account["confirm_password"])) {
        $errors["confirm_password"] = "Please confirm your password";
    } elseif ($account["password"] !== $account["confirm_password"]) {
        $errors["confirm_password"] = "Passwords do not match";
    }

    if (empty(array_filter($errors))) {
        $_SESSION["email"] = $account["email"];
        $_SESSION["employeeId"] = $account["employeeId"];
        $_SESSION["password"] = $account["password"];
        $_SESSION["confirm_password"] = $account["confirm_password"];
        header("Location: register3.php");
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
            <div class="bar active-bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <h3>Now, lets process your email and password</h3>
        <form method="POST">
            <div class="input-g">
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" value="<?=$account["email"] ?? "" ?>"><br>
                <p style="color:red;"><?= $errors['email'] ?? ''; ?></p>
            </div>

            <div class="input-g">
                <label for="employeeId">Employee ID</label><br>
                <input type="text" id="employeeId" name="employeeId" value="<?=$account["employeeId"] ?? "" ?>"><br>
                <p style="color:red;"><?= $errors['employeeId'] ?? ''; ?></p>
            </div>

            <div class="input-g">
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" value="<?=$account["password"] ?? "" ?>"><br>
                <p style="color:red;"><?= $errors['password'] ?? ''; ?></p>
            </div>

            <div class="input-g">
                <label for="confirm_password">Confirm Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" value="<?=$account["confirm_password"] ?? "" ?>"><br>
                <p style="color:red;"><?= $errors['confirm_password'] ?? ''; ?></p>
            </div>

            <div class="sum-btn">
                <button class="bck-btn" type="button" onclick="location.href='register.php'">Go Back</button>
                <input class="nxt-btn" type="submit" value="Next">
            </div>
        </form>
    </div>
</body>
</html>