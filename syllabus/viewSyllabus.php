<?php
session_start();
date_default_timezone_set('Asia/Manila');



if (!isset($_SESSION["acc_id"])) {
    header("Location: login.php");
    exit();
} elseif (!$_SESSION["isVerified"]) {
    header("Location: register7.php");
    exit();
}

require_once "../class/syllabus.php";
require_once "../class/stmp_config.php";
$syllabusObj = new Syllabus();
$syllabus = [];
$message = [];
$errors = [];

if (isset($_GET["id"])) {
    $sid = trim(htmlspecialchars($_GET["id"]));
    $syllabus = $syllabusObj->fetchSyllabus($sid);

    if (!$syllabus) {
        echo "<a href='dashboard.php'>View Syllabus</a>";
        exit("No syllabus found");
    }
} else {
    echo "<a href='dashboard.php'>View Syllabus</a>";
    exit("No syllabus found");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mailer = getMailerInstance(); 
    $syllabus["comment"] = trim(htmlspecialchars($_POST["comment"] ?? ""));
    $insCourseID = $syllabus['course_id'] ?? null;
    $insAccID = $syllabus['acc_id'] ?? null;

    if (empty($syllabus["comment"])) {
        $errors["comment"] = "Comment is required before approving or rejecting a syllabus";
    } else {
        $syllabusObj->date_created = date("Y-m-d H:i:s");
        $syllabusObj->comment = $syllabus["comment"];

        if (isset($_POST['approve'])) {  
            $syllabusObj->level_id = $_SESSION["role_id"] + 1;
            $syllabusObj->action = "Approved";
            if($syllabusObj->approveSyllabus($_SESSION['acc_id'], $insCourseID)){
                $subject = "Syllabus Approval Update";
                $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                            Syllabus Approved!
                        </h2>
                        <p>Hello " . htmlspecialchars($syllabus['fname']) . ",</p>
                        <p>Great news! Your syllabus for <strong>" . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "</strong> has been officially approved by ''" . htmlspecialchars($_SESSION['fname']) . " " . htmlspecialchars($_SESSION['lname']) . "', your ''". htmlspecialchars($_SESSION['role_name']) ."''</p>
                        <p>No further action is required from you at this time. Thank you for your hard work and contribution to our curriculum.</p>
                        <p style='margin-top: 25px;'>
                            Best regards,<br>
                            <em>The System Administration Team</em>
                        </p>
                    </div>
                </div>
            ";
                sendNotificationEmail($mailer, $syllabus["email"], $subject, $body);
                $syllabusObj->sendNotifications(
                    $syllabus["acc_id"],          
                    $_SESSION["acc_id"],          
                    "Your Syllabus has been approved",
                    "Your syllabus " . $syllabus["course_code"] . " - " . $syllabus["course_name"] .
                    " has been approved by " . $_SESSION["fname"] . " " . $_SESSION["lname"] .
                    ", your " . $_SESSION["role_name"],
                    "viewSyllabus.php?id=" . $syllabus["course_id"]
                );
                $message["success"] = "Approved successfully";
            }else{
                $message["error"] = "Approval failed";
            }
        }

        if (isset($_POST['reject'])) {  
            $acc_id = $syllabusObj->getRoleIdByAccId($insAccID);
            $syllabusObj->level_id = $acc_id;
            $syllabusObj->action = "Rejected";
            if($syllabusObj->approveSyllabus($_SESSION['acc_id'], $insCourseID)){
                $subject = "Action Required: Your Syllabus Needs Revision";
                $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                            Syllabus Requires Revision
                        </h2>
                        <p>Hello " . htmlspecialchars($syllabus['fname']) . ",</p>
                        <p>Your submitted syllabus for <strong>" . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "</strong> has been reviewed by '" . htmlspecialchars($_SESSION['fname']) . " " . htmlspecialchars($_SESSION['lname']) . "', your '" . htmlspecialchars($_SESSION['role_name']) . "'. It requires a few changes before it can be approved.</p>
                        
                        <h3 style='color: #333; margin-top: 20px; margin-bottom: 5px;'>Reviewer's Feedback:</h3>
                        <div style='background-color: #f8f8f8; border-left: 5px solid #dd0000; padding: 15px; margin-top: 5px;'>
                            <p style='margin: 0;'><em>" . nl2br(htmlspecialchars($syllabus["comment"])) . "</em></p>
                        </div>

                        <p style='margin-top: 20px;'>Please log in to the system, review the feedback, make the necessary updates, and resubmit the syllabus for another review.</p>
                        
                        <p style='margin-top: 25px;'>
                            Thank you,<br>
                            <em>The System Administration Team</em>
                        </p>
                    </div>
                </div>
                ";
                sendNotificationEmail($mailer, $syllabus['email'], $subject, $body);
                $syllabusObj->sendNotifications(
                    $syllabus["acc_id"],          // recipient
                    $_SESSION["acc_id"],       // related course
                    "Your Syllabus has been returned",
                    "Your syllabus " . $syllabus["course_code"] . " - " . $syllabus["course_name"] .
                    " has been returned to you by " . $_SESSION["fname"] . " " . $_SESSION["lname"] .
                    ", your " . $_SESSION["role_name"] . ". Please consider making changes according to the instructed comment.",
                    "viewSyllabus.php?id=" . $syllabus["course_id"]
                );
                $message["success"] = "Rejected successfully";
            }else{
                $message["error"] = "Rejection failed";
            }
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
            <ul>
                <li class="option <?php if ($_SESSION["page"] == 'dashboard') echo 'active'; ?>" onclick="location.href='dashboard.php?page=dashboard'">
                <img class="tab-icon" src="../styles/images/dashboard_icon.svg" alt="">
                Dashboard</li>

                <li class="option <?php if ($_SESSION["page"] == 'viewSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=viewSyllabus'">
                <img class="tab-icon" src="../styles/images/book_icon.svg" alt="">
                View my Syllabus</li>

                <li class="option <?php if ($_SESSION["page"] == 'returnedSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=returnedSyllabus'">
                <img class="tab-icon" src="../styles/images/return_icon.svg" alt="">
                Returned Syllabus</li>

                <li class="option <?php if ($_SESSION["page"] == 'addSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=addSyllabus'">
                <img class="tab-icon" src="../styles/images/add_icon.svg" alt="">
                Add Syllabus</li>
                
                <?php if ($_SESSION['role_id'] > 1){ ?>
                    <li class="option <?php if ($_SESSION["page"] == 'pendingApprovals') echo 'active'; ?>" onclick="location.href='dashboard.php?page=pendingApprovals'">
                    <img class="tab-icon" src="../styles/images/pending_icon.svg" alt="">
                    Pending Approvals</li>

                    <li class="option <?php if ($_SESSION["page"] == 'associatedSyllabus') echo 'active'; ?>" onclick="location.href='dashboard.php?page=associatedSyllabus'">
                    <img class="tab-icon" src="../styles/images/associated_icon.svg" alt="">
                    Associated Syllabus</li>
                <?php } ?>

                <li class="option <?php if ($_SESSION["page"] == 'notifications') echo 'active'; ?>" onclick="location.href='dashboard.php?page=notifications'">
                <img class="tab-icon" src="../styles/images/notifications_icon.svg" alt="">
                Notifications</li>
                
                <li class="option <?php if ($_SESSION["page"] == 'myAccount') echo 'active'; ?>" onclick="location.href='dashboard.php?page=myAccount'">
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
        <div class="selected details">
            <button class="back-btn" onclick="window.location.href='dashboard.php?page=<?php echo $_SESSION['page']; ?>'"><- Go Back</button>
            <p>Viewing Syllabus</p>
            <h2><?= htmlspecialchars($syllabus['course_code'] ?? '') ?> - <?= htmlspecialchars($syllabus['course_name'] ?? '') ?></h2>
            <hr>
            
            <?php if (!empty($message["success"])){ ?>
                <p class="message"><?= $message["success"] ?></p>
            <?php } ?>
            <?php if (!empty($message["error"])){ ?>
                        <p class="message error"><?= $message["error"] ?></p>
            <?php } ?>

            <div class="progress-bar" style="max-width: 500px; margin: 10px auto 40px auto;"> 
                <div class="progress-line">
                    <div class="level-line" style="width: <?= 
                        ($syllabus['level_id'] == 1 ? '0%' : 
                            ($syllabus['level_id'] == 2 ? '25%' : 
                            ($syllabus['level_id'] == 3 ? '50%' : 
                            ($syllabus['level_id'] == 4 ? '75%' : 
                            ($syllabus['level_id'] == 5 ? '100%' : '0%'))))) 
                        ?>;"></div>
                </div>

                <div class="step-circle step-label1 <?= ($syllabus['level_id'] >= 1 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                <div class="step-circle step-label2 <?= ($syllabus['level_id'] > 2 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                <div class="step-circle step-label3 <?= ($syllabus['level_id'] > 3 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                <div class="step-circle step-label4 <?= ($syllabus['level_id'] > 4 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
                <div class="step-circle step-label5 <?= ($syllabus['level_id'] == 5 ? 'app-active' : '') ?>"><img src="../styles/images/check-icon.svg"></div>
            </div>

            <table class="syllabus-details">
                <tr>
                    <th>Status</th>
                    <td>
                        <?php
                            switch ($syllabus['level_id']) {
                                case 1:
                                    if($syllabus['acc_id']==$_SESSION['acc_id']){
                                        echo 'Syllabus is returned to you. <a class="view-btn view-edit" style="color: white;" href="editSyllabus.php?id=' . urlencode($syllabus['course_id']) . '">Edit</a>';
                                    } else {
                                        echo 'Syllabus is returned to the owner.';
                                    }
                                    break;
                                case 2:
                                    echo "Waiting for Department Head Approval";
                                    break;
                                case 3:
                                    echo "Waiting for Dean Approval";
                                    break;
                                case 4:
                                    echo "Waiting for VPAA Approval";
                                    break;
                                case 5:
                                    echo "Syllabus has already been published!";
                                    break;
                                default:
                                    echo "Pending Approval";
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>College:</th>
                    <td><?= htmlspecialchars($syllabus['college'] ?? 'None') ?></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td><?= htmlspecialchars($syllabus['department'] ?? 'None') ?></td>
                </tr>
                <tr>
                    <th>Prerequisite:</th>
                    <td><?= !empty($syllabus['prerequisite']) ? htmlspecialchars($syllabus['prerequisite']) : 'None' ?></td>
                </tr>
                <tr>
                    <th>Credit:</th>
                    <td><?= htmlspecialchars($syllabus['credit'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Creator Name:</th>
                    <td><?= htmlspecialchars($syllabus['fname'] ?? '') ?> <?= htmlspecialchars($syllabus['lname'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?= htmlspecialchars($syllabus['email'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>With Lab:</th>
                    <td><?= (!empty($syllabus['with_lab']) && $syllabus['with_lab'] == 1) ? 'Yes' : 'No' ?></td>
                </tr>
                <tr>
                    <th>Effective Date:</th>
                    <td><?= htmlspecialchars($syllabus['effective_date'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?= nl2br(htmlspecialchars($syllabus['description'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Latest Comment:</th>
                    <td><?= nl2br(htmlspecialchars($syllabus['latest_comment'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>File:</th>
                    <td>
                        <?php if (!empty($syllabus['file_name'])): ?>
                            <a href="<?= htmlspecialchars($syllabus['file_dir']) ?>" target="_blank">View Syllabus File</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <form method="POST" id="syllabusForm">
            <?php if($_SESSION["role_id"]!=1 && $syllabus['level_id']!=5 && $syllabus['level_id'] == $_SESSION["role_id"] && $_SESSION["acc_id"]!=$syllabus["acc_id"]){?>
                <div class="input-g">
                    <label for="comment">Comment: <span color="red">*</span></label><br>
                    <textarea id="comment" name="comment" rows="4" cols="50" placeholder="Enter your comment here..."><?= htmlspecialchars($syllabus["comment"]??"")?></textarea><br>
                    <p style="color:red;"><?= $errors['comment'] ?? "" ?></p>
                </div>
            <?php if(!isset($message["success"])){?>
                <div class="controls">
                    <button type="button" id="rej-btn">Reject</button>
                    <button type="button" id="app-btn">Approve</button>
                </div>
            <?php }} ?>
            </form>
        </div>
        <div>
            <h2>History Log</h2>
            <p>Here, you can see the approval history for this syllabus</p>
            <hr>
            <table>
                <tr>
                    <th>Interacted By</th>
                    <th>Date and Time</th>
                    <th>Approved Level</th>
                    <th>Action</th>
                    <th>Comment</th>
                </tr>
                <?php $syllabuslog = $syllabusObj->approvalHistory($sid);
                    foreach($syllabuslog as $log){?>
                <tr>
                    <td><?= $log['interacted'] ?></td>
                    <td><?= $log['datetime'] ?></td>
                    <td><?php  
                        if($log['level_id'] == 1) echo "Instructor";
                        else if($log['level_id'] == 2) echo "Department Head";
                        else if($log['level_id'] == 3) echo "College Dean";
                        else if($log['level_id'] == 4) echo "VP for Academic Affairs";
                        else if($log['level_id'] == 5) echo "Published";
                    ?></td>
                    <td><?= $log['action'] ?></td>
                    <td><?= $log['comment'] ?></td>
                </tr>
                <?php }?>
            </table>
        </div>
    </section>
    <div class="modal-bg" id="modalId-app">
        <div class="modal">
            <h2>Approving Confirmation</h2>
            <p>Are you sure to approve <span style="color: #b30909"><?= htmlspecialchars($syllabus['course_code'] ?? '') ?> - <?= htmlspecialchars($syllabus['course_name'] ?? '') ?></span> syllabus? Make sure to input your comment before approving</p>
            <div class="con-button">
                <button type="button" id="cancelBtnApp">Cancel</button>
                <button type="submit" name="approve" id="approveNow" form="syllabusForm">Approve</button>
            </div>
        </div>
    </div>

    <div class="modal-bg" id="modalId-rej">
        <div class="modal">
            <h2>Rejecting Confirmation</h2>
            <p>Are you sure to reject <span style="color: #b30909"><?= htmlspecialchars($syllabus['course_code'] ?? '') ?> - <?= htmlspecialchars($syllabus['course_name'] ?? '') ?></span> syllabus? Make sure to input your comment before rejecting</p>
            <div class="con-button">
                <button type="button" id="cancelBtnRej">Cancel</button>
                <button type="submit" name="reject" id="rejectNow" form="syllabusForm">Reject</button>
            </div>
        </div>
    </div>
    <script>
        const modalapp = document.getElementById("modalId-app");
        const modalrej = document.getElementById("modalId-rej");
        const cancelBtnApp = document.getElementById("cancelBtnApp");
        const cancelBtnRej = document.getElementById("cancelBtnRej");
        const rejectNow = document.getElementById("rejectNow");
        const approveNow = document.getElementById("approveNow");
        const appBtn = document.getElementById("app-btn");
        const rejBtn = document.getElementById("rej-btn");
        const syllabusForm = document.getElementById("syllabusForm");

        appBtn.addEventListener("click", () => {
            modalapp.style.display = "flex";
        });

        rejBtn.addEventListener("click", () => {
            modalrej.style.display = "flex";
        });

        cancelBtnRej.addEventListener("click", () => {
            modalrej.style.display = "none";
        });

        cancelBtnApp.addEventListener("click", () => {
            modalapp.style.display = "none";
        });


        modalapp.addEventListener("click", (e) => {
            if (e.target === modalapp) {
                modalapp.style.display = "none";
            }
        });

        modalrej.addEventListener("click", (e) => {
            if (e.target === modalrej) {
                modalrej.style.display = "none";
            }
        });
    </script>
</body>
</html>