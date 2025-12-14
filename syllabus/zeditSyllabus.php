<?php
session_start();
date_default_timezone_set('Asia/Manila');

if(!isset($_SESSION["acc_id"])) {
    header("Location: login.php");
    exit();
}elseif(!$_SESSION["isVerified"]){
    header("Location: register7.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'returnedSyllabus';

require_once "../class/syllabus.php";
$syllabusObj = new Syllabus();
$syllabus = [];
$errors = [];
$message = [];

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["id"])){
        $sid = trim(htmlspecialchars($_GET["id"]));
        $syllabus = $syllabusObj->fetchSyllabus($sid);
        
        if(!$syllabus){
            echo "<a href='dashboard.php'>View Syllabus</a>";
            exit("No syllabus found");
        }
    }else{
        echo "<a href='dashboard.php'>View Syllabus</a>";
        exit("No syllabus found");
    }
} 
else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_syllabus'])) {
    $syllabus["course_code"] = trim(htmlspecialchars($_POST["course_code"] ?? ""));
    $syllabus["course_name"] = trim(htmlspecialchars($_POST["course_name"] ?? ""));
    $syllabus["prerequisite"] = trim(htmlspecialchars($_POST["prerequisite"] ?? ""));
    $syllabus["credit"] = trim(htmlspecialchars($_POST["credit"] ?? ""));
    $syllabus["with_lab"] = isset($_POST["with_lab"]) ? 1 : 0;
    $syllabus["description"] = trim(htmlspecialchars($_POST["description"] ?? ""));
    $syllabus["effective_date"] = trim(htmlspecialchars($_POST["effective_date"] ?? ""));
    $syllabus["comment"] = trim(htmlspecialchars($_POST["comment"] ?? ""));
    $syllabus["course_id"] = trim(htmlspecialchars($_GET["id"]));

    // Handle file
    $hasNewFile = !empty($_FILES["file"]["name"]) && $_FILES["file"]["size"] > 0;

    if ($hasNewFile) {
        $syllabus["file_name"] = $_FILES["file"]["name"];
        $syllabus["file_dir"] = "../uploads/" . basename($syllabus["file_name"]);
    } else {
        // Retain old file if no new one uploaded
        $syllabus["file_name"] = $_POST["old_file_name"] ?? "";
        $syllabus["file_dir"]  = $_POST["old_file_dir"] ?? "";
    }

    // Validation
    if (empty($syllabus["course_code"])) $errors["course_code"] = "Course code is required";
    if (empty($syllabus["course_name"])) $errors["course_name"] = "Course name is required";
    if (empty($syllabus["credit"]) || !is_numeric($syllabus["credit"]) || $syllabus["credit"] <= 0) $errors["credit"] = "Valid credit is required";
    if (empty($syllabus["description"])) $errors["description"] = "Description is required";
    if (empty($syllabus["effective_date"])) $errors["effective_date"] = "Effective date is required";

    if (empty(array_filter($errors))) {
        if ($hasNewFile) {
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $syllabus["file_dir"])) {
                $message["error"] = "File upload failed.";
            }
        }

        // Assign values to object
        $syllabusObj->course_code = $syllabus["course_code"];
        $syllabusObj->course_name = $syllabus["course_name"];
        $syllabusObj->prerequisite = $syllabus["prerequisite"];
        $syllabusObj->credit = $syllabus["credit"];
        $syllabusObj->with_lab = $syllabus["with_lab"];
        $syllabusObj->description = $syllabus["description"];
        $syllabusObj->date_created = date("Y-m-d H:i:s");
        $syllabusObj->role_id = $_SESSION["role_id"] + 1;
        $syllabusObj->effective_date = $syllabus["effective_date"];
        $syllabusObj->file_name = $syllabus["file_name"];
        $syllabusObj->file_dir = $syllabus["file_dir"];
        $syllabusObj->acc_id = $_SESSION["acc_id"];
        $syllabusObj->action = "Submitted";
        $syllabusObj->comment = $syllabus["comment"];

        if ($syllabusObj->updateSyllabus($syllabus["course_id"])) {
            $message["success"] = "Syllabus updated successfully!";
        } else {
            $message["error"] = "Failed to update syllabus.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Syllabus</title>
</head>
<body>
    <h1>WMSU Course Syllabus Portal</h1>
    <h2>Hello! <?= htmlspecialchars($_SESSION["fname"]) ?></h2>

    <div>
        <ul>
            <li><a href="dashboard.php?page=dashboard">Dashboard</a></li>
            <li><a href="dashboard.php?page=viewSyllabus">View my Syllabus</a></li>
            <li><a href="dashboard.php?page=returnedSyllabus">Returned Syllabus</a></li>
            <li><a href="dashboard.php?page=addSyllabus">Add Syllabus</a></li>
            <?php if ($_SESSION['role_id'] > 1) echo '<li><a href="?page=pendingApprovals">Pending Approvals</a></li>'; ?>
            <li><a href="dashboard.php?page=myAccount">My Account</a></li>
        </ul>
    </div>

    <p>Editing Syllabus</p>

    <?php if (!empty($message["success"])): ?>
        <p style="color:green;"><?= htmlspecialchars($message["success"]) ?></p>
    <?php endif; ?>

    <?php if (!empty($message["error"])): ?>
        <p style="color:red;"><?= htmlspecialchars($message["error"]) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="course_code">Course Code:</label>
        <input type="text" id="course_code" name="course_code" value="<?= htmlspecialchars($syllabus["course_code"] ?? "") ?>"><br>
        <p style="color:red;"><?= $errors['course_code'] ?? "" ?></p>

        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" value="<?= htmlspecialchars($syllabus["course_name"] ?? "") ?>"><br>
        <p style="color:red;"><?= $errors['course_name'] ?? "" ?></p>

        <label for="prerequisite">Prerequisite:</label>
        <input type="text" id="prerequisite" name="prerequisite" value="<?= htmlspecialchars($syllabus["prerequisite"] ?? "") ?>"><br>

        <label for="credit">Credit:</label>
        <input type="number" id="credit" name="credit" value="<?= htmlspecialchars($syllabus["credit"] ?? "") ?>"><br>
        <p style="color:red;"><?= $errors['credit'] ?? "" ?></p>

        <label for="with_lab">With Lab:</label>
        <input type="checkbox" id="with_lab" name="with_lab" value="1" <?= (($syllabus["with_lab"] ?? 0) == 1) ? "checked" : "" ?>><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description"><?= htmlspecialchars($syllabus["description"] ?? "") ?></textarea><br>
        <p style="color:red;"><?= $errors['description'] ?? "" ?></p>

        <label for="effective_date">Effective Date:</label>
        <input type="date" id="effective_date" name="effective_date" value="<?= htmlspecialchars($syllabus["effective_date"] ?? "") ?>"><br>
        <p style="color:red;"><?= $errors['effective_date'] ?? "" ?></p>

        <label for="file">Syllabus File:</label>
        <input type="file" id="file" name="file"><br>

        <!-- Keep old file info -->
        <input type="hidden" name="old_file_name" value="<?= htmlspecialchars($syllabus['file_name'] ?? '') ?>">
        <input type="hidden" name="old_file_dir" value="<?= htmlspecialchars($syllabus['file_dir'] ?? '') ?>">

        <!-- Show current file -->
        <?php if (!empty($syllabus["file_name"])): ?>
            <p>Current File: 
                <a href="<?= htmlspecialchars($syllabus["file_dir"]) ?>" target="_blank">
                    <?= htmlspecialchars($syllabus["file_name"]) ?>
                </a>
            </p>
        <?php endif; ?>

        <label for="comment">Comment:</label>
        <textarea id="comment" name="comment"><?= htmlspecialchars($syllabus["comment"] ?? "") ?></textarea><br>

        <label for="comment">Comments as Returned:</label>
        <p><?= htmlspecialchars($syllabus["latest_comment"] ?? "") ?></p>

        <div class="controls">
            <button type="submit" name="add_syllabus">Update Changes</button>
        </div>
    </form>
</body>
</html>
