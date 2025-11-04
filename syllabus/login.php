<?php
require_once "../class/accounts.php";
$accObj = new Accounts();

$email = "";
$password = "";
$errors = [];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(htmlspecialchars($_POST["email"] ?? ""));
    $password = trim(htmlspecialchars($_POST["password"] ?? ""));

    if (empty($email)) {
        $errors["email"] = "Email is required";
    }

    if (empty($password)) {
        $errors["password"] = "Password is required";
    }

    if (empty(array_filter($errors))) {
        $user = $accObj->login($email, $password);

        if ($user) {
            session_start();
            $_SESSION["acc_id"] = $user["acc_id"];
            $_SESSION["fname"] = $user["fname"];
            $_SESSION["lname"] = $user["lname"];
            $_SESSION["mname"] = $user["lname"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["col_dep_id"] = $user["col_dep_id"];
            $_SESSION["role_name"] = $user["role_name"];
            $_SESSION["role_id"] = $user["role_id"];
            $_SESSION["college"] = $user["college"];
            $_SESSION["department"] = $user["department"];
            $_SESSION["isVerified"] = $user["isVerified"];
            if($_SESSION["role_id"] != 4){
                header("Location: dashboard.php");
            } else {
                header("Location: vpDashboard.php");
            }
            exit;
        } else {
            $errors["login"] = "Invalid email or password";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/login2.css">
    <title>Login</title>
</head>
<body>
    <div class="l-login">
        <img style="height: 50px; margin-bottom: 20px;" src="../styles/col_logo/19.png" alt="">
        <h1>WMSU Course Syllabus Approval Portal</h1>
        <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
    </div>
    <div>
        <button class="mb2 back" onclick="location.href='index.php'"><- Go back to Homepage</button>
        <h1 class="mb2">Login</h1>
        <form action="login.php" method="POST">
            <label for="email">Email:</label><br>
            <input type="text" id="email" name="email" value="<?= $email ?>"><br>
            <p class="mb2"style="color:red;"><?= $errors['email'] ?? ''?></p>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" value="<?= $password ?>"><br>
            <p class="mb2" style="color:red;"><?= $errors['password'] ?? '' ?></p>

            <button type="submit" value="Login" class="login-btn mb1">Log In</button>
            <p style="color:red;"><?= $errors['login'] ?? ''; ?></p>
        </form>
    </div>
</body>
</html>