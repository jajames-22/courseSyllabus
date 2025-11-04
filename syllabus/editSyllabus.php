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
    if (empty($syllabus["comment"])) $errors["comment"] = "Comment is required before updating or deleting";

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
            // Refresh syllabus to reload latest comment info
            $syllabus = $syllabusObj->fetchSyllabus($syllabus["course_id"]);
        } else {
            $message["error"] = "Failed to update syllabus.";
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_syllabus'])) {
    $courseId = trim(htmlspecialchars($_GET["id"]));
    if ($syllabusObj->deleteSyllabus($courseId)) {
        $message["success"] = "Syllabus deleted successfully!";
        header("Location: dashboard.php?page=returnedSyllabus");
        exit;
    } else {
        $message["error"] = "Failed to delete syllabus.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <title>Dashboard</title>
</head>
<body>
    <section class="side-bar">
        <header>
            <img class="dashboard-logo" src="../styles/col_logo/19.png" alt="">
        </header>
        <div>
            <ul>
                <li class="option" onclick="location.href='dashboard.php?page=dashboard'">
                <img class="tab-icon" src="../styles/images/dashboard_icon.svg" alt="">
                Dashboard</li>

                <li class="option <?php if ($page == 'viewSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=viewSyllabus'">
                <img class="tab-icon" src="../styles/images/book_icon.svg" alt="">
                View my Syllabus</li>

                <li class="option active" onclick="location.href='dashboard.php?page=returnedSyllabus'">
                <img class="tab-icon" src="../styles/images/return_icon.svg" alt="">
                Returned Syllabus</li>

                <li class="option <?php if ($page == 'addSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=addSyllabus'">
                <img class="tab-icon" src="../styles/images/add_icon.svg" alt="">
                Add Syllabus</li>
                
                <?php if ($_SESSION['role_id'] > 1){ ?>
                    <li class="option <?php if ($page == 'pendingApprovals') echo 'active'; ?>" onclick="location.href='dashboard.php?page=pendingApprovals'">
                <img class="tab-icon" src="../styles/images/pending_icon.svg" alt="">
                Pending Approvals</li>
                <?php } ?>
                
                <li class="option <?php if ($page == 'myAccount') echo 'active'; ?>" onclick="location.href='dashboard.php?page=myAccount'">
                <img class="tab-icon" src="../styles/images/account_icon.svg" alt="">
                My Account</li>
            </ul>
        </div>
    </section>
    <section class="content">
        <div class="headbar">
            <div>
                <h2 class="welcome">Hello, <?= $_SESSION["fname"]?>!</h2>
            </div>
            <div>
                <p><strong>Role: </strong> <?= $_SESSION["role_name"]?></p>
                <p><strong>College: </strong> <?= $_SESSION["college"]?></p>
                <p><strong>Department: </strong> <?= $_SESSION["department"]?></p>
            </div>
        </div>
        <div class="selected">
            <button class="back-btn" onclick="location.href='dashboard.php?page=returnedSyllabus'"><- Back to Retuned Syllabus</button>
            <p>Viewing your Syllabus</p>
            <h2>Editing your Syllabus</h2>
            <hr>
            <?php if (!empty($message["success"])): ?>
                <p class="message"><?= htmlspecialchars($message["success"]) ?></p>
            <?php endif; ?>

            <?php if (!empty($message["error"])): ?>
                <p class="message error"><?= htmlspecialchars($message["error"]) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="input-g">
                    <label for="course_code">Course Code:</label><br>
                    <input type="text" id="course_code" name="course_code" value="<?= htmlspecialchars($syllabus["course_code"] ?? "") ?>"><br>
                    <p style="color:red;"><?= $errors['course_code'] ?? "" ?></p>
                </div>

                <div class="input-g">
                    <label for="course_name">Course Name:</label><br>
                    <input type="text" id="course_name" name="course_name" value="<?= htmlspecialchars($syllabus["course_name"] ?? "") ?>"><br>
                    <p style="color:red;"><?= $errors['course_name'] ?? "" ?></p>
                </div>

                <div class="input-g">
                    <label for="prerequisite">Prerequisite:</label><br>
                    <input type="text" id="prerequisite" name="prerequisite" value="<?= htmlspecialchars($syllabus["prerequisite"] ?? "") ?>"><br>
                </div>

                <div class="input-g">
                    <label for="credit">Credit:</label><br>
                    <input type="number" id="credit" name="credit" value="<?= htmlspecialchars($syllabus["credit"] ?? "") ?>"><br>
                    <p style="color:red;"><?= $errors['credit'] ?? "" ?></p>
                </div>

                <div class="input-g">
                    <label for="with_lab">With Lab:</label>
                    <input type="checkbox" id="with_lab" name="with_lab" value="1" <?= (($syllabus["with_lab"] ?? 0) == 1) ? "checked" : "" ?>><br>
                </div>

                <div class="input-g">
                    <label for="description">Description:</label><br>
                    <textarea id="description" name="description"><?= htmlspecialchars($syllabus["description"] ?? "") ?></textarea><br>
                    <p style="color:red;"><?= $errors['description'] ?? "" ?></p>
                </div>

                <div class="input-g">
                    <label for="effective_date">Effective Date:</label><br>
                    <input type="date" id="effective_date" name="effective_date" value="<?= htmlspecialchars($syllabus["effective_date"] ?? "") ?>"><br>
                    <p style="color:red;"><?= $errors['effective_date'] ?? "" ?></p>
                </div>

                <div class="input-g">
                    <input type="hidden" name="old_file_name" value="<?= htmlspecialchars($syllabus['file_name'] ?? '') ?>">
                    <input type="hidden" name="old_file_dir" value="<?= htmlspecialchars($syllabus['file_dir'] ?? '') ?>">
                    <?php if (!empty($syllabus["file_name"])): ?>
                    <p>Current File: 
                        <a href="<?= htmlspecialchars($syllabus["file_dir"]) ?>" target="_blank">
                            <?= htmlspecialchars($syllabus["file_name"]) ?>
                        </a>
                    </p>
                    <label for="file">If you want to replace the current file, Insert a file here: </label>
                    <input type="file" id="file" name="file"><br>
                <?php endif; ?>
                </div>

                <div class="input-g">
                    <label for="comment">Comments as Returned:</label><br>
                    <p style="border: 1px black dashed"><?= htmlspecialchars($syllabus["latest_comment"] ?? "") ?> <span style="color: gray">(from: <?= htmlspecialchars($syllabus["latest_acc_name"] ?? "") ?>)</span></p>
                </div>

                <div class="input-g">
                    <label for="comment">Your comment:</label><br>
                    <textarea id="comment" name="comment"><?= htmlspecialchars($syllabus["comment"] ?? "") ?></textarea><br>
                    <p style="color:red;"><?= $errors['comment'] ?? "" ?></p>
                </div>
                <?php if(!isset($message["success"])){?>
                    <div class="controls">
                        <button type="submit" name="delete_syllabus">Delete</button>
                        <button type="submit" name="add_syllabus">Update Changes</button>
                    </div>
                <?php }?>    
            </form>
        </div>
    </section>
</body>
</html>