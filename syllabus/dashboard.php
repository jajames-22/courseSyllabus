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

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$_SESSION["page"] = $page;

require_once "../class/syllabus.php";
$syllabusObj = new Syllabus();
$syllabus = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_syllabus'])) {
    $syllabus["course_code"] = trim(htmlspecialchars($_POST["course_code"] ?? ""));
    $syllabus["course_name"] = trim(htmlspecialchars($_POST["course_name"] ?? ""));
    $syllabus["prerequisite"] = trim(htmlspecialchars($_POST["prerequisite"] ?? ""));
    $syllabus["credit"] = trim(htmlspecialchars($_POST["credit"] ?? ""));
    $syllabus["with_lab"] = isset($_POST["with_lab"]) ? 1 : 0;
    $syllabus["description"] = trim(htmlspecialchars($_POST["description"] ?? ""));
    $syllabus["effective_date"] = trim(htmlspecialchars($_POST["effective_date"] ?? ""));
    $syllabus["file_name"] =  $_FILES["file"]["name"] ?? "";;
    $syllabus["file_dir"] = "../uploads/" . basename($syllabus["file_name"]);
    $syllabus["comment"] = trim(htmlspecialchars($_POST["comment"] ?? ""));

    if (empty($syllabus["course_code"])) {
        $errors["course_code"] = "Course code is required";
    }

    if (empty($syllabus["course_name"])) {
        $errors["course_name"] = "Course name is required";
    }

    if (empty($syllabus["credit"]) || !is_numeric($syllabus["credit"]) || $syllabus["credit"] <= 0) {
        $errors["credit"] = "Valid credit is required";
    }

    if (empty($syllabus["description"])) {
        $errors["description"] = "Description is required";
    }

    if (empty($syllabus["effective_date"])) {
        $errors["effective_date"] = "Effective date is required";
    }

    if (empty($_FILES["file"]["name"]) || $_FILES["file"]["size"] == 0) {
        $errors["file"] = "File upload is required";
    }

    if (empty(array_filter($errors))) {
         if (move_uploaded_file($_FILES["file"]["tmp_name"], $syllabus["file_dir"])) {
            $syllabusObj->course_code = $syllabus["course_code"];
            $syllabusObj->acc_id = $_SESSION["acc_id"];
            $syllabusObj->col_dep_id = $_SESSION["col_dep_id"];
            $syllabusObj->course_name = $syllabus["course_name"];
            $syllabusObj->prerequisite = $syllabus["prerequisite"];
            $syllabusObj->credit = $syllabus["credit"];
            $syllabusObj->with_lab = $syllabus["with_lab"]? 1 : 0;
            $syllabusObj->description = $syllabus["description"];
            $syllabusObj->date_created = date("Y-m-d H:i:s");
            $syllabusObj->role_id = $_SESSION["role_id"]+1;
            $syllabusObj->effective_date = $syllabus["effective_date"];
            $syllabusObj->file_name = $syllabus["file_name"];
            $syllabusObj->file_dir = $syllabus["file_dir"];
            $syllabusObj->action = "Submitted";
            $syllabusObj->comment = $syllabus["comment"];

            if ($syllabusObj->addSyllabus()) {
                $message["success"] = "New syllabus added successfully!";
                $syllabus = []; 
            } else {
                $message["error"] = "⚠️ Failed to add syllabus to the database.";
            }
        } else {
            $message["error"] = "File upload failed to upload.";
        }
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
                <li class="option <?php if ($page == 'dashboard') echo 'active'; ?>" onclick="location.href='?page=dashboard'">
                <img class="tab-icon" src="../styles/images/dashboard_icon.svg" alt="">
                Dashboard</li>

                <li class="option <?php if ($page == 'viewSyllabus') echo 'active'; ?>" onclick="location.href='?page=viewSyllabus'">
                <img class="tab-icon" src="../styles/images/book_icon.svg" alt="">
                View my Syllabus</li>

                <li class="option <?php if ($page == 'returnedSyllabus') echo 'active'; ?>" onclick="location.href='?page=returnedSyllabus'">
                <img class="tab-icon" src="../styles/images/return_icon.svg" alt="">
                Returned Syllabus</li>

                <li class="option <?php if ($page == 'addSyllabus') echo 'active'; ?>" onclick="location.href='?page=addSyllabus'">
                <img class="tab-icon" src="../styles/images/add_icon.svg" alt="">
                Add Syllabus</li>
                
                <?php if ($_SESSION['role_id'] > 1){ ?>
                    <li class="option <?php if ($page == 'pendingApprovals') echo 'active'; ?>" onclick="location.href='?page=pendingApprovals'">
                <img class="tab-icon" src="../styles/images/pending_icon.svg" alt="">
                Pending Approvals</li>
                <?php } ?>
                
                <li class="option <?php if ($page == 'myAccount') echo 'active'; ?>" onclick="location.href='?page=myAccount'">
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
        <?php if($page == "dashboard"){?>
                <h2>Dashboard</h2>
                <p>Keep in touch because of this dashboard!</p>
                <hr>
                <div class="sum-boxes">
                    <div class="dash-box">
                        <p>Your total syllbus</p>
                        <p><?php $total = $syllabusObj->countSyllabusByAccId($_SESSION['acc_id']);
                        echo $total; ?></p>
                    </div>
                    <div class="dash-box">
                        <p>Your published syllabus</p>
                        <p><?php $countApproved = $syllabusObj->countMySyllabusLevel5($_SESSION['acc_id']);
                        echo $countApproved ; ?></p>
                    </div>
                    
                    <div class="dash-box">
                        <p>Retuned Syllabus</p>
                        <p><?php $countPending = $syllabusObj->countReturn($_SESSION['department'], $_SESSION['role_id'], $_SESSION['acc_id']);
                        echo $countPending; ?></p>
                    </div>

                    <?php if ($_SESSION['role_id'] > 1){ ?>
                    <div class="dash-box">
                        <p>Pending Approvals</p>
                        <p><?php if($_SESSION["role_id"] == 2){
                        $numrows = $syllabusObj->countPendingforHead($_SESSION["department"], $_SESSION["role_id"]);
                        echo $numrows; } else if($_SESSION["role_id"] == 3) {
                        $numrows = $syllabusObj->countPendingforDean($_SESSION["college"], $_SESSION["role_id"]);
                        echo $numrows; }   
                        ?></p>
                    </div>
                    <?php }?>
                </div>
        <?php }else if($page == "viewSyllabus"){ ?>
                <h2>Your Syllabus</h2>
                    <p>All syllabus that is created by you will appear here</p>
                    <hr>
                    <table>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Credit</th>
                            <th>With Lab</th>
                            <th>Effective Date</th>
                            <th>Approval Level</th>
                            <th>Comment</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                <?php
                    $syllabi = $syllabusObj->viewAllMySyllabusById($_SESSION["acc_id"]);

                    if ($syllabi) {
                        foreach ($syllabi as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['credit']) ?></td>
                                <td><?= $row['with_lab'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($row['effective_date']) ?></td>
                                <td><?= htmlspecialchars($row['approval_level']) ?>/5</td>
                                <td><?= htmlspecialchars($row['latest_comment']) ?></td>
                                <td><?= htmlspecialchars($row['date_created']) ?></td>
                                <td>
                                    <a class="view-btn" href="viewSyllabus.php?id=<?= urlencode($row['course_id']) ?>">View</a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else { ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">Currently, you still don't have any syllabus, try creating one!</td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else if($page == "returnedSyllabus"){?>
                <h2>Returned Syllabus</h2>
                <p>Showing all the returned syllabus that needs to be revised or delete</p>
                <hr>
                <table>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Credit</th>
                            <th>With Lab</th>
                            <th>Effective Date</th>
                            <th>Approval Level</th>
                            <th>Comment</th>
                            <th>Date Interacted</th>
                            <th>Actions</th>
                        </tr>
                        <?php
                    $syllabi = $syllabusObj->getPendingforHead($_SESSION["department"], $_SESSION["role_id"]);
                    $hasData = false; // flag to track if any rows were printed

                    if ($syllabi) {
                        foreach ($syllabi as $row) {
                            if ($_SESSION["acc_id"] == $row["acc_id"]) {
                                $hasData = true; ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                                    <td><?= htmlspecialchars($row['credit']) ?></td>
                                    <td><?= $row['with_lab'] ? 'Yes' : 'No' ?></td>
                                    <td><?= htmlspecialchars($row['effective_date']) ?></td>
                                    <td><?= htmlspecialchars($row['approval_level']) ?></td>
                                    <td><?= htmlspecialchars($row['latest_comment']) ?></td>
                                    <td><?= htmlspecialchars($row['latest_date']) ?></td>
                                    <td>
                                        <a class="view-btn" href="editSyllabus.php?id=<?= urlencode($row['course_id']) ?>">Edit</a>
                                    </td>
                                </tr>
                    <?php
                            }
                        }
                    }
                    if (!$hasData) { ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">Good Job! No syllabus has been returned for revisions.</td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else if($page == "addSyllabus"){ ?>
                <div>
                    <h2>Adding a Syllabus</h2>
                    <p>Submit new syllabus here</p>
                    <hr>

                    <?php if (!empty($message["success"])){ ?>
                            <p class="message"><?= $message["success"] ?></p>
                    <?php } ?>
                    <?php if (!empty($message["error"])){ ?>
                            <p class="message error"><?= $message["error"] ?></p>
                    <?php } ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="input-g">
                            <label for="course_code">Course Code: <span color="red">*</span></label><br>
                            <input type="text" id="course_code" name="course_code" value="<?= $syllabus["course_code"] ?? ""?>"><br>
                            <p style="color: red;"><?= $errors['course_code'] ?? "" ?></p>
                        </div>

                        <div class="input-g">
                            <label for="course_name">Course Name: <span color="red">*</span></label><br>
                            <input type="text" id="course_name" name="course_name" value="<?= $syllabus["course_name"] ?? ""?>"><br>
                            <p style="color: red;"><?= $errors['course_name'] ?? "" ?></p>
                        </div>

                        <div class="input-g">
                            <label for="prerequisite">Prerequisite:</label><br>
                            <input type="text" id="prerequisite" name="prerequisite" value="<?= $syllabus["prerequisite"] ?? ""?>"><br>
                        </div>

                        <div class="input-g">
                            <label for="credit">Credit: <span color="red">*</span></label><br>
                            <input type="number" id="credit" name="credit" value="<?= $syllabus["credit"] ?? ""?>"><br>
                            <p style="color: red;"><?= $errors['credit']?? ""?></p>
                        </div>

                        <div class="input-g">
                            <label for="with_lab">With Lab:</label>
                            <input type="checkbox" id="with_lab" name="with_lab" value="1" <?= (($syllabus["with_lab"]??0)==1)? "checked" : "" ?>><br>
                        </div>

                        <div class="input-g">
                            <label for="description">Description: <span color="red">*</span></label><br>
                            <textarea id="description" name="description"><?= $syllabus["description"] ?? ""?></textarea><br>
                            <p style="color: red;"><?= $errors['description'] ?? "" ?></p>
                        </div>

                        <div class="input-g">
                            <label for="effective_date">Effective Date: <span color="red">*</span></label><br>
                            <input type="date" id="effective_date" name="effective_date" value="<?= $syllabus["effective_date"] ?? ""?>"><br>
                            <p style="color: red;"><?= $errors['effective_date'] ?? "" ?></p>
                        </div>

                        <div class="input-g">
                            <label for="file">Syllabus File: <span color="red">*</span></label><br>
                            <input type="file" id="file" name="file"><br>
                            <p style="color: red;"><?= $errors['file'] ?? "" ?></p>
                        </div>

                        <div class="input-g">
                            <label for="comment">Your comment:</label><br>
                            <textarea id="comment" name="comment"><?= $syllabus["comment"] ?? ""?></textarea><br>
                        </div>
                        <button class="sub-syl" type="submit" name="add_syllabus">Submit Syllabus</button>
                    </form>
                </div>
            <?php  } else if($page == "pendingApprovals" && ($_SESSION["role_id"]==2 || $_SESSION["role_id"]==3)){ ?>
                <div>
                    <h2>Pending Syllabus</h2>
                    <p>Showing all your pending approvals</p>
                    <hr>
                    <table
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Creator Name</th>
                            <th>Department</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    <?php
                        if ($_SESSION["role_id"] == 2) {
                            $syllabi = $syllabusObj->getPendingforHead($_SESSION["department"], $_SESSION["role_id"]);
                        } else if ($_SESSION["role_id"] == 3) {
                            $syllabi = $syllabusObj->getPendingforDean($_SESSION["college"], $_SESSION["role_id"]);
                        }

                        if (empty($syllabi)) {
                            echo '<tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">No one submitted any syllabus to you yet</td></tr>';
                        } else {
                            $hasData = false;
                            foreach ($syllabi as $row) {
                                if ($_SESSION["acc_id"] != $row["acc_id"]) {
                                    $hasData = true;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row["course_code"]) ?></td>
                                        <td><?= htmlspecialchars($row["course_name"]) ?></td>
                                        <td><?= htmlspecialchars($row["fname"] . " " . $row["lname"]) ?></td>
                                        <td><?= htmlspecialchars($row["department"]) ?></td>
                                        <td><?= htmlspecialchars($row["description"]) ?></td>
                                        <td><a class="view-btn" href="viewSyllabus.php?id=<?= urlencode($row['course_id']) ?>">View</a></td>
                                    </tr>
                                    <?php
                                }
                            }

                            if (!$hasData) {
                                echo '<tr><td colspan="5" style="text-align:center; padding:20px;">No one submitted any syllabus to you yet</td></tr>';
                            }
                        }
                        ?>
                    </table>
                </div>
            <?php }else if($page=="myAccount"){ ?>
                <h2>My Account</h2>
                <hr>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
            <?php } ?>

        </div>
    </section>
</body>
</html>