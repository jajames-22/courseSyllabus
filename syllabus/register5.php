<?php
session_start();
require_once "../class/accounts.php";

if(!isset($_SESSION["department"])) {
    header("Location: register4.php");
    exit();
}

$accObj = new Accounts();
$account = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account["role_id"] = trim(htmlspecialchars($_POST["role_id"] ?? ""));

    if (empty($account["role_id"])) {
        $errors["role_id"] = "Role is required";
    } elseif ($account["role_id"] == 2 && $accObj->isDeptHeadExist($_SESSION["col_dep_id"])) {
        $errors["role_id"] = "Department Head for this department already exists";
    } 

    if (empty(array_filter($errors))) {
        $_SESSION["role_id"] = $account["role_id"];
        header("Location: register6.php");
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
            <div class="bar active-bar"></div>
            <div class="bar active-bar"></div>
            <div class="bar active-bar"></div>
            <div class="bar"></div>
        </div>
        <h3 class="mb1">Please select suitable option</h3>

        <form method="POST">
            <label>You are the Department: </label><br>
            <div class="mb1 mt1">
                <?php foreach ($accObj->getRoles() as $roles) { ?>
                    <input 
                        id="role<?= $roles['role_id'] ?>" 
                        type="radio" 
                        name="role_id" 
                        value="<?= $roles['role_id'] ?>" 
                        <?= (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == $roles['role_id']) ? 'checked' : '' ?>
                    >
                    <label for="role<?= $roles['role_id'] ?>"> <?= $roles['role_name'] ?></label><br>
                <?php } ?>
            </div>
            <p style="color:red;" class="mb1"><?= $errors['role_id'] ?? '' ?></p>
            
            <div class="sum-btn">
                <button class="bck-btn" type="button" onclick="location.href='register4.php'">Go Back</button>
                <input class="nxt-btn" type="submit" value="Next">
            </div>
        </form>
    </div>

</body>
</html>
