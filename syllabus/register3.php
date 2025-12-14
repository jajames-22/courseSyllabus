<?php
session_start();

if(!isset($_SESSION["email"]) || !isset($_SESSION["password"]) || !isset($_SESSION["employeeId"]) ) {
    header("Location: register2.php");
    exit();
}

require_once "../class/accounts.php";
$accObj = new Accounts();

$account = [];
$errors = [];

$account["college"] = $_SESSION["college"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account["college"] = trim(htmlspecialchars($_POST["college"] ?? ""));

    if (empty($account["college"])) {
        $errors["college"] = "College is required";
    }

    if (empty(array_filter($errors))) {
        if($account["college"]=="Office of the Academic Affairs"){
            $_SESSION["college"] = $account["college"];
            $_SESSION["department"] = "Vice-President for Academic Affairs";
            header("Location: register6.php");
            exit();
        }else{
            $_SESSION["college"] = $account["college"];
            header("Location: register4.php");
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
    <title>Document</title>
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
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        
        <h3 class="mb1">What college are you?</h3>
        <form method="POST">
            <div class="input-g">
                <label for="college">College:</label><br>
                <select name="college" id="college">
                    <option value="">--Select College--</option>
                    <?php foreach ($accObj->getColleges() as $college){?>
                        <option value="<?= $college['college']?>" <?= (($account["college"] ?? "") == $college["college"]) ? "selected" : ""?>><?= $college['college']?></option>
                    <?php }?>
                </select>
            </div>

            <div class="sum-btn">
                <button class="bck-btn" type="button" onclick="location.href='register2.php'">Go Back</button>
                <input class="nxt-btn" type="submit" value="Next">
            </div>
        </form>
    </div>
    
</body>
</html>