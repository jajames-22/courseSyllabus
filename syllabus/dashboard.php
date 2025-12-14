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
require_once "../class/accounts.php";
require_once "../class/stmp_config.php";
$syllabusObj = new Syllabus();
$accountsObj = new Accounts();
$syllabus = [];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_syllabus'])) {
    $mailer = getMailerInstance();
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
    } else if ($syllabusObj->isCourseCodeExist($syllabus["course_code"])) {
        $errors["course_code"] = "Course code already exist";
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
                if($_SESSION["role_id"]==1){
                    $deptHeadInfo = $accountsObj->getAccountInfoByRole($_SESSION["col_dep_id"], 2);

                    $deptHeadEmail = $deptHeadInfo['email'];
                    $deptHeadName  = $deptHeadInfo['full_name'];
                    $subject = "Action Required: New Syllabus Submitted for Your Review";

                    $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                                New Syllabus for Review
                            </h2>
                            
                            <p>Hello <strong>" . htmlspecialchars($deptHeadName) . "</strong>,</p> 
                            
                            <p>A new syllabus has been submitted by the instructor 
                                <strong>" . htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) . "</strong> 
                                and is now awaiting your review.
                            </p>
                            
                            <h3 style='color: #333; margin-top: 20px; margin-bottom: 10px;'>Syllabus Details:</h3>
                            <p style='margin-left: 15px;'>
                                <strong>Course:</strong> " . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "<br>
                                <strong>Instructor:</strong> " . htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) . "
                            </p>

                            <p style='margin-top: 20px;'>
                                Please log in to the Course Syllabus Approval System at your earliest convenience to review the submission.
                            </p>
                            
                            <p style='margin-top: 25px;'>
                                Thank you,<br>
                                <em>The System Administration Team</em>
                            </p>
                        </div>
                    </div>
                    ";
                    sendNotificationEmail($mailer, $deptHeadEmail, $subject, $body);
                    $syllabusObj->sendNotifications(
                    $deptHeadInfo["acc_id"],          // recipient
                    $_SESSION["acc_id"],         // related course
                    "New syllabus to review ",
                    "New syllabus titled" . $syllabus["course_code"] . " - " . $syllabus["course_name"] .
                    " is now waiting for your approval from " . $_SESSION["fname"] . " " . $_SESSION["lname"] . ". ",
                    "viewSyllabus.php?id=" . $syllabus["course_id"]
                    );
                } else if ($_SESSION["role_id"]==2) {
                     // Get Dean of the same college
                    $deanInfo = $accountsObj->getDeanOfCollege($_SESSION["college"]);

                    if ($deanInfo) {
                        $deanEmail = $deanInfo['email'];
                        $deanName  = $deanInfo['full_name'];

                        $subject = "Action Required: New Syllabus Submitted by Department Head";

                        $body = "
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                                <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                                    New Syllabus Submitted for Your Review
                                </h2>
                                
                                <p>Hello <strong>" . htmlspecialchars($deanName) . "</strong>,</p>

                                <p>A new syllabus has been submitted by the Department Head 
                                    <strong>" . htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) . "</strong> 
                                    and is now awaiting your review.
                                </p>

                                <h3 style='color: #333; margin-top: 20px; margin-bottom: 10px;'>Syllabus Details:</h3>
                                <p style='margin-left: 15px;'>
                                    <strong>Course:</strong> " . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "<br>
                                    <strong>Department Head:</strong> " . htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) . "
                                </p>

                                <p style='margin-top: 20px;'>
                                    Please log in to the Course Syllabus Approval System to review this submission.
                                </p>

                                <p style='margin-top: 25px;'>
                                    Thank you,<br>
                                    <em>The System Administration Team</em>
                                </p>
                            </div>
                        </div>
                        ";
                        sendNotificationEmail($mailer, $deanEmail, $subject, $body);}
                        $syllabusObj->sendNotifications(
                        $deptHeadInfo["acc_id"],          // recipient
                        $_SESSION["acc_id"],         // related course
                        "New syllabus to review ",
                        "New syllabus titled" . $syllabus["course_code"] . " - " . $syllabus["course_name"] .
                        " is now waiting for your approval from " . $_SESSION["fname"] . " " . $_SESSION["lname"] . ". ",
                        "viewSyllabus.php?id=" . $syllabus["course_id"]
                        );
                }
                $message["success"] = "New syllabus submitted successfully!";
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
    <link rel="stylesheet" href="../styles/modalsKpi.css">
    <title>Dashboard</title>
</head>
<body>
    <section class="side-bar">
        <header>
            <img class="dashboard-logo" src="../styles/col_logo/19.png" alt="">
        </header>
        <div>
            <?php 
                // 1. Fetch notifications
                $myNotifs = $syllabusObj->getAllNotifications($_SESSION["acc_id"]);
                
                // 2. Check if any are unread
                $hasUnread = false;
                if ($myNotifs) {
                    foreach ($myNotifs as $n) {
                        if ($n['is_read'] == 0) {
                            $hasUnread = true;
                            break; // We found one, so we can stop checking
                        }
                    }
                }

                // 3. Determine which icon to use
                $notifIcon = $hasUnread ? "unread_icon.svg" : "notifications_icon.svg";
            ?>
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

                    <li class="option <?php if ($page == 'associatedSyllabus') echo 'active'; ?>" onclick="location.href='?page=associatedSyllabus'">
                    <img class="tab-icon" src="../styles/images/associated_icon.svg" alt="">
                    Associated Syllabus</li>
                <?php } ?>

                <li class="option <?php if ($page == 'notifications') echo 'active'; ?>" onclick="location.href='?page=notifications'">
                    <?php
                        // Quick check for unread items
                        $allNotifs = $syllabusObj->getAllNotifications($_SESSION["acc_id"]);
                        $isUnread = false;
                        if($allNotifs){
                            foreach($allNotifs as $n) { if($n['is_read'] == 0) { $isUnread = true; break; } }
                        }
                    ?>
                    <img class="tab-icon" src="../styles/images/<?= $isUnread ? 'unread_icon.svg' : 'notifications_icon.svg' ?>" alt="">
                    Notifications
                </li>
                
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
        <?php } else if ($page == "viewSyllabus") { ?>
            <h2>Your Syllabus</h2>
            <p>All syllabus that is created by you will appear here</p>
            <hr>
            <div class="mysyl-list">
                <?php
                $syllabi = $syllabusObj->viewAllMySyllabusById($_SESSION["acc_id"]);
                if ($syllabi) {
                    foreach ($syllabi as $row) { ?>
                        <div class="syllabus-bar">
                            <div class="syl-details">
                                <p style="font-weight: bold; color: #b30909">
                                    <?= htmlspecialchars($row['course_code']) ?> - <?= htmlspecialchars($row['course_name']) ?>
                                </p>
                                <p>Date Created: <?= htmlspecialchars($row['date_created']) ?></p>
                            </div>

                            <div class="progress-bar" style="max-width: 800px; margin: 15px auto 10px auto;">
                                <div class="progress-line">
                                    <div class="level-line" style="width: <?= 
                                        ($row['approval_level'] == 1 ? '0%' : 
                                        ($row['approval_level'] == 2 ? '25%' : 
                                        ($row['approval_level'] == 3 ? '50%' : 
                                        ($row['approval_level'] == 4 ? '75%' : 
                                        ($row['approval_level'] == 5 ? '100%' : '0%'))))) 
                                    ?>;"></div>
                                </div>

                                <div class="step-circle step-hov1 <?= ($row['approval_level'] >= 1 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                                <div class="step-circle step-hov2 <?= ($row['approval_level'] > 2 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                                <div class="step-circle step-hov3 <?= ($row['approval_level'] > 3 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                                <div class="step-circle step-hov4 <?= ($row['approval_level'] > 4 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                                <div class="step-circle step-hov5 <?= ($row['approval_level'] == 5 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                            </div>

                            <div>
                                <p>
                                    <?php
                                    switch ($row['approval_level']) {
                                        case 1:
                                            echo "This syllabus is returned to you ";
                                            break;
                                        case 2:
                                            if($row['approval_level']==$_SESSION['role_id']){
                                                echo "The syllabus is returned to you";
                                            }
                                            else{
                                                echo "Waiting for Department Head Approval";
                                            }
                                            break;
                                        case 3:
                                            if($row['approval_level']==$_SESSION['role_id']){
                                                echo "The syllabus is returned to you";
                                            }
                                            else{
                                                echo "Waiting for College Dean Approval";
                                            }
                                            break;
                                        case 4:
                                            echo "Waiting for VPAA Approval";
                                            break;
                                        case 5:
                                            echo "Your Syllabus has already been published!";
                                            break;
                                        default:
                                            echo "Pending Approval";
                                    }
                                    ?>
                                </p>
                                <button class="view-btn" onclick="window.location.href='viewSyllabus.php?id=<?= ($row['course_id']) ?>'">
                                    View Syllabus
                                </button>
                            </div>
                        </div>
                    <?php } // end foreach ?>
                    
                <?php } else { // no syllabi ?>
                    <p style="text-align: center;">
                        Currently, you still don't have any syllabus, try creating one!
                    </p>
                <?php } ?>
            </div>
        <?php } else if ($page == "returnedSyllabus") { ?>
                <h2>Returned Syllabus</h2>
                <p>Showing all the returned syllabus that needs to be revised or delete</p>
                <hr>
                <?php if (!empty($_SESSION["notif_message"])){ ?>
                            <p class="message"><?= $_SESSION["notif_message"] ?></p>
                <?php } ?>
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
                            <td colspan="10" style="text-align: center; padding: 70px 0px;">Good Job! No syllabus has been returned for revisions.</td>
                        </tr>
                    <?php } ?>
                </table>
        <?php } else if ($page == "associatedSyllabus"){?>
                <h2>Associated Syllabus</h2>
                <p>Here are the syallabus associated with you</p>
                <hr>
                <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Creator Name</th>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Step 1: Call the function to fetch the history of syllabi reviewed by the current user.
                    // The function's internal query already ensures we only get syllabi from other users.
                    $syllabiHistory = $syllabusObj->getSyllabiFromApprovalLog($_SESSION["acc_id"]);

                    // Step 2: Check if the function returned any results.
                    if (empty($syllabiHistory)) {
                        // If the array is empty, display a single row with a user-friendly message.
                        echo '<tr><td colspan="6" style="text-align:center; padding: 70px 0;">You have no history of reviewed syllabi.</td></tr>';
                    } else {
                        // Step 3: If there are results, loop through them and create a table row for each.
                        foreach ($syllabiHistory as $row) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row["course_code"]) ?></td>
                                
                                <!-- Note: Using 'description' for Course Name as 'course_name' doesn't exist in the schema -->
                                <td><?= htmlspecialchars($row["course_name"]) ?></td>
                                
                                <td><?= htmlspecialchars($row["fname"] . " " . $row["lname"]) ?></td>
                                <td><?= htmlspecialchars($row["department"]) ?></td>
                                <td><?= htmlspecialchars($row["description"]) ?></td>
                                <td><a class="view-btn" href="viewSyllabus.php?id=<?= urlencode($row['course_id']) ?>">View</a></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
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

                    <form method="POST" enctype="multipart/form-data" id="syllabusForm">
                        <div class="input-level">
                            <div class="input-g">
                                <label for="course_code">Course Code: <span style="color:red">*</span></label><br>
                                <input type="text" id="course_code" name="course_code" value="<?= $syllabus["course_code"] ?? ""?>"><br>
                                <p style="color: red;"><?= $errors['course_code'] ?? "" ?></p>
                            </div>

                            <div class="input-g">
                                <label for="course_name">Course Name: <span style="color:red">*</span></label><br>
                                <input type="text" id="course_name" name="course_name" value="<?= $syllabus["course_name"] ?? ""?>"><br>
                                <p style="color: red;"><?= $errors['course_name'] ?? "" ?></p>
                            </div>
                        </div>

                        <div class="input-level input-level2">
                            <div class="input-g">
                                <label for="prerequisite">Prerequisite:</label><br>
                                <input type="text" id="prerequisite" name="prerequisite" value="<?= $syllabus["prerequisite"] ?? ""?>"><br>
                            </div>

                            <div class="input-g">
                                <label for="credit">Credit: <span style="color:red">*</span></label><br>
                                <input type="number" id="credit" name="credit" value="<?= $syllabus["credit"] ?? ""?>"><br>
                                <p style="color: red;"><?= $errors['credit']?? ""?></p>
                            </div>
                        </div>

                        <div class="input-g">
                            <label for="with_lab">With Lab:</label>
                            <input type="checkbox" id="with_lab" name="with_lab" value="1" <?= (($syllabus["with_lab"]??0)==1)? "checked" : "" ?>><br>
                        </div>

                        <div class="input-g">
                            <label for="description">Description: <span style="color:red">*</span></label><br>
                            <textarea id="description" name="description"><?= $syllabus["description"] ?? ""?></textarea><br>
                            <p style="color: red;"><?= $errors['description'] ?? "" ?></p>
                        </div>

                        <div class="input-level">
                            <div class="input-g">
                                <label for="effective_date">Effective Date: <span style="color:red">*</span></label><br>
                                <input type="date" id="effective_date" name="effective_date" value="<?= $syllabus["effective_date"] ?? ""?>"><br>
                                <p style="color: red;"><?= $errors['effective_date'] ?? "" ?></p>
                            </div>

                            <div class="input-g">
                                <label for="file">Syllabus File: <span style="color:red">*</span></label><br>
                                <input type="file" id="file" name="file"><br>
                                <p style="color: red;"><?= $errors['file'] ?? "" ?></p>
                            </div>
                        </div>

                        <div class="input-g">
                            <label for="comment">Your comment:</label><br>
                            <textarea id="comment" name="comment"><?= $syllabus["comment"] ?? ""?></textarea><br>
                        </div>
                        <!--<button class="sub-syl" type="submit" name="add_syllabus">Submit Syllabus</button>-->
                        <button class="sub-syl" id="conBtn" type="button">Submit Syllabus</button>
                    </form>
                </div>
        <?php  } else if($page == "pendingApprovals" && ($_SESSION["role_id"]==2 || $_SESSION["role_id"]==3)){ ?>
                <div>
                    <h2>Pending Syllabus</h2>
                    <p>Showing all your pending approvals</p>
                    <hr>
                    <table>
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

                        // --- Step 2: Check if the initial database query returned any results at all ---
                        if (empty($syllabi)) {
                            // This handles the case where the database query itself is empty.
                            echo '<tr><td colspan="6" style="text-align:center; padding: 70px 0;">No syllabi are currently pending your review.</td></tr>';
                        } else {
                            // --- Step 3: Loop through the results and apply the display filter ---
                            
                            // Initialize a flag to track if we actually display any rows.
                            $hasVisibleData = false; 

                            foreach ($syllabi as $row) {
                                // This condition filters the results to prevent users from approving their own submissions.
                                if ($_SESSION["acc_id"] != $row["acc_id"]) {
                                    
                                    // A row is going to be displayed, so set the flag to true.
                                    $hasVisibleData = true; 
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

                            // --- Step 4: After the loop, check if the flag was ever set to true ---
                            if (!$hasVisibleData) {
                                // This handles the case where the query returned data, but all of it was filtered out.
                                echo '<tr><td colspan="6" style="text-align:center; padding: 70px 0;">There are no syllabi submitted by other users for you to review.</td></tr>';
                            }
                        }
                        ?>
                    </table>
                </div>
        <?php }else if($page=="notifications"){?>
                <h2>Notifications</h2>
                <p>We remind you here!</p>
                <hr>
                <div class="notif-list">
                
                <?php 
                    $notifications = $syllabusObj->getAllNotifications($_SESSION["acc_id"]);

                    if(!$notifications){
                        echo '<p class="empty-message">You have no notifications</p>';
                    } else {
                        foreach ($notifications AS $notif){
                            // Helper variable for is_read (0 or 1)
                            $isRead = $notif["is_read"]; 
                    ?>
                        <!-- CHANGED: onclick calls a function instead of direct location.href -->
                        <div id="notif_<?= $notif['notif_id'] ?>" 
                            onclick="handleNotifClick(<?= $notif['notif_id'] ?>, '<?= htmlspecialchars($notif['dir']) ?>', <?= $isRead ?>)" 
                            class="notif-bar <?= $isRead ? "notif-read" : "notif-unread" ?>"
                            style="cursor: pointer;">
                            
                            <div class="notif-details">
                                <h3><?= htmlspecialchars($notif["type"]) ?></h3>
                                <p><?= htmlspecialchars($notif["message"]) ?></p>
                            </div>

                            <div class="notif-buttons">
                                <!-- event.stopPropagation() is crucial here -->
                                <button onclick="event.stopPropagation(); toggleRead(<?= $notif['notif_id'] ?>);">
                                    <?= $isRead ? "Mark as Unread" : "Mark as Read" ?>
                                </button>

                                <button onclick="event.stopPropagation(); deleteNotif(<?= $notif['notif_id'] ?>);">
                                    Delete
                                </button>
                            </div>
                        </div>
                <?php 
                        }
                    }
                ?>
                </div>
            <?php }else if($page=="myAccount"){ ?>
                <h2>My Account</h2>
                <p>Account actions can be found here!</p>
                <hr>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
                <button class="sec-btn" onclick="window.location.href='updateAccount.php'">Update Account</button>
            <?php } ?>
    </section>
    <div class="modal-bg" id="modalId">
        <div class=modal>
            <h2>Confirmation</h2>
            <p>Are you sure to submit this syllabus? Make sure all required fields are not empty</p>
            <div class="con-button">
                <button type="button" id="cancelBtn">Cancel</button>
                <button type="submit" name="add_syllabus" id="submitNow" form="syllabusForm">Submit</button>
            </div>
        </div>
    </div>
    <script>
        async function handleNotifClick(id, url, isRead) {
    // If it's currently UNREAD (0), we need to mark it as read before leaving
    if (isRead == 0) {
        try {
            // We await the fetch so the DB updates before the page changes
            await fetch("notif_action.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                // Assuming your backend uses 'toggle_read' logic. 
                // Since we know it's unread, toggling it will make it read.
                body: `action=toggle_read&notif_id=${id}`
            });
        } catch (error) {
            console.error("Error marking notification as read:", error);
        }
    }

    // Now redirect the user to the correct page
    window.location.href = url;
}
    
        function toggleRead(id) {
        fetch("notif_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=toggle_read&notif_id=${id}`
    })
    .then(res => res.text())
    .then(() => {
        // Toggle class for read/unread
        const notif = document.getElementById(`notif_${id}`);
        notif.classList.toggle("notif-read");
        notif.classList.toggle("notif-unread");

        // Update button text
        const btn = notif.querySelector("button");
        if (btn) {
            btn.textContent = notif.classList.contains("notif-read")
                ? "Mark as Unread"
                : "Mark as Read";
        }
    });
}

function deleteNotif(id) {
    fetch("notif_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=delete&notif_id=${id}`
    })
    .then(res => res.text())
    .then(() => {
        const notif = document.getElementById(`notif_${id}`);
        if (notif) notif.remove(); // remove notification from UI
    });
}

        const modal = document.getElementById("modalId");
        const conBtn = document.getElementById("conBtn");
        const cancelBtn = document.getElementById("cancelBtn");
        const submitNow = document.getElementById("submitNow");
        const syllabusForm = document.getElementById("syllabusForm");

        conBtn.addEventListener("click", () => {
        modal.style.display = "flex";
        });

        cancelBtn.addEventListener("click", () => {
        modal.style.display = "none";
        });

        // ✅ Close only if clicked *outside* modal content
        modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
        });
    </script>
</body>
</html>