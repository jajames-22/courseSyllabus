<?php
session_start();

if(!isset($_SESSION["college"])) {
    header("Location: register3.php");
    exit();
}

require_once "../class/accounts.php";
$accObj = new Accounts();
$account = [];
$errors = [];

$account["department"] = $_SESSION["department"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account["department"] = trim(htmlspecialchars($_POST["department"] ?? ""));

    if (empty($account["department"])) {
        $errors["department"] = "Department is required";
    } elseif ($accObj->isDeanExist($_SESSION["college"]) && $account["department"] == "Dean") {
        $errors["department"] = "Dean for this college already exists";
    }

    if (empty(array_filter($errors))) {
        $idcoldep = $accObj->getColDepId($_SESSION["college"], $account["department"]);
        $_SESSION["col_dep_id"] = $idcoldep["col_dep_id"];
        if($account["department"] == "Dean"){
            $_SESSION["department"] = "Dean";
            $_SESSION["role_id"] = 3;
            header("Location: register6.php");
            exit();
        }else{
            $_SESSION["department"] = $account["department"];
            header("Location: register5.php");
            exit();
        }
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
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <h3>Select Department</h3>
        <form method="POST">
            <div class="input-g">
                <label for="department">Department:</label><br>
                <select name="department" id="department">
                    <option value="">--Select Department--</option>
                    <?php 
                    $college = $_SESSION["college"];
                    $departments = $accObj->getDepartmentsByCollege($college);
                    foreach ($departments as $dept){?>
                        <option value="<?= $dept['department']?>" name="department" <?= (($account["department"] ?? "") == $dept["department"]) ? "selected" : ""?>><?= $dept['department']?></option>
                    <?php }?>
                </select>
                <p style="color:red;"><?= $errors['department'] ?? ''; ?></p>
            </div>
            
            <p class="mb1">If you are the College Dean, please select Dean</p>
            
            <div class="sum-btn">
                <button class="bck-btn" type="button" onclick="location.href='register3.php'">Go Back</button>
                <input class="nxt-btn" type="submit" value="Next">
            </div>
        </form>

    </div>
</body>
</html>