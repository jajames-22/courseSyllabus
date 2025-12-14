<?php
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION["acc_id"])) {
    header("Location: login.php");
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
        echo "<a href='vpDashboard.php?page=dashboard'>Back to Dashboard</a>";
        exit("No syllabus found");
    }
} else {
    echo "<a href='vpDashboard.php?page=dashboard'>Back to Dashboard</a>";
    exit("No syllabus found");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mailer = getMailerInstance(); 
    $comment = trim(htmlspecialchars($_POST["comment"] ?? ""));
    $insCourseID = $syllabus['course_id'] ?? null;
    $insAccID = $syllabus['acc_id'] ?? null;

    // Delete action (for published syllabi)
    if (isset($_POST['delete']) && ($syllabus['level_id'] ?? 0) == 5) {
        if ($syllabusObj->deleteSyllabus($insCourseID)) {
            $message["success"] = "Syllabus and its approval logs deleted successfully.";
        } else {
            $message["error"] = "Failed to delete syllabus.";
        }
    } else {
        // Approve / Reject actions
        if (empty($comment)) {
            $errors["comment"] = "Comment is required before approving or rejecting a syllabus.";
        } else {
            $syllabusObj->date_created = date("Y-m-d H:i:s");
            $syllabusObj->comment = $comment;

            // Approve action
            if (isset($_POST['approve'])) {
                $syllabusObj->level_id = $_SESSION["role_id"] + 1; // VP final level
                $syllabusObj->action = "Published";
                if ($syllabusObj->approveSyllabus($_SESSION['acc_id'], $insCourseID)) {
                    $subject = "Notification: Your Syllabus Has Been Published";
                    $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <!-- Changed color from crimson to green for success context -->
                            <h2 style='color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 10px;'>
                                Syllabus Successfully Published
                            </h2>
                            <p>Hello " . htmlspecialchars($syllabus['fname']) . ",</p>
                            <p>We are pleased to inform you that your submitted syllabus for <strong>" . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "</strong> has been reviewed and <strong>APPROVED</strong> by '" . htmlspecialchars($_SESSION['fname']) . " " . htmlspecialchars($_SESSION['lname']) . "', the Vice President for Academic Affairs.</p>
                            
                            <h3 style='color: #333; margin-top: 20px; margin-bottom: 5px;'>Approval Notes:</h3>
                            <!-- Changed border color from red to green -->
                            <div style='background-color: #f8f8f8; border-left: 5px solid #28a745; padding: 15px; margin-top: 5px;'>
                                <p style='margin: 0;'><em>" . nl2br(htmlspecialchars($syllabus["comment"])) . "</em></p>
                            </div>

                            <p style='margin-top: 20px;'>The syllabus is now officially published and available for viewing within the portal.</p>
                            
                            <p style='margin-top: 25px;'>
                                Thank you for your hard work,<br>
                                <em>The System Administration Team</em>
                            </p>
                        </div>
                    </div> 
                    ";
                    sendNotificationEmail($mailer, $syllabus['email'], $subject, $body);
                    $message["success"] = "Syllabus approved successfully.";
                } else {
                    $message["error"] = "Approval failed.";
                }
            }

            // Reject action
            if (isset($_POST['reject'])) {
                $acc_id = $syllabusObj->getRoleIdByAccId($insAccID);
                $syllabusObj->level_id = $acc_id;
                $syllabusObj->action = "Rejected";
                if ($syllabusObj->approveSyllabus($_SESSION['acc_id'], $insCourseID)) {
                    $subject = "Action Required: Your Syllabus Needs Revision";
                    $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                                Syllabus Requires Revision
                            </h2>
                            <p>Hello " . htmlspecialchars($syllabus['fname']) . ",</p>
                            <p>Your submitted syllabus for <strong>" . htmlspecialchars($syllabus['course_code']) . " - " . htmlspecialchars($syllabus['course_name']) . "</strong> has been reviewed by '" . htmlspecialchars($_SESSION['fname']) . " " . htmlspecialchars($_SESSION['lname']) . "', the Vice President for Academic Affairs. It requires a few changes before it can be approved.</p>
                            
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
                    $message["success"] = "Syllabus rejected successfully.";
                } else {
                    $message["error"] = "Rejection failed.";
                }
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
    <title>VP | View Syllabus</title>
</head>
<body>
    <section class="side-bar">
        <header>
            <img class="dashboard-logo" src="../styles/col_logo/19.png" alt="">
        </header>
        <div>
            <ul>
                <li class="option <?php if ($_SESSION["page"] == 'dashboard') echo 'active'; ?>" onclick="location.href='vpDashboard.php?page=dashboard'">
                <img class="tab-icon" src="../styles/images/dashboard_icon.svg" alt="">Dashboard</li>

                <li class="option <?php if ($_SESSION["page"]== 'manageSyllabus') echo 'active'; ?>" onclick="location.href='vpDashboard.php?page=manageSyllabus'">
                <img class="tab-icon" src="../styles/images/book_icon.svg" alt="">Manage Syllabus</li>

                <li class="option <?php if ($_SESSION["page"]== 'manageAcc') echo 'active'; ?>" onclick="location.href='vpDashboard.php?page=manageAcc'">
                <img class="tab-icon" src="../styles/images/group_icon.svg" alt="">Manage Accounts</li>

                <li class="option <?php if ($page == 'notifications') echo 'active'; ?>" onclick="location.href='?page=notifications'">
                <img class="tab-icon" src="../styles/images/notifications_icon.svg" alt="">Notifications</li>

                <li class="option <?php if ($_SESSION["page"]== 'myAccount') echo 'active'; ?>" onclick="location.href='vpDashboard.php?page=myAccount'">
                <img class="tab-icon" src="../styles/images/account_icon.svg" alt="">My Accounts</li>
            </ul>
        </div>
    </section>
    <section class="content">
        <div class="headbar">
            <div>
                <h2 class="welcome">Hello, <?= $_SESSION["fname"]?>!</h2>
            </div>
            <div>
                <p><strong>Role: </strong> Admin</p>
                <p><strong>Office: </strong> <?= $_SESSION["college"]?></p>
                <p><strong>Department: </strong> <?= $_SESSION["department"]?></p>
            </div>
        </div>
        <div class="selected">
            <button class="back-btn" onclick="window.location.href='vpDashboard.php?page=<?php echo $_SESSION['page']; ?>'"><- Go Back</button>
            <p style="margin-bottom: 10px;">Viewing Syllabus</p>
            <h2><?= htmlspecialchars($syllabus['course_code'] ?? '') ?> - <?= htmlspecialchars($syllabus['course_name'] ?? '') ?></h2>
            <hr>

            <?php if (!empty($message["success"])) { ?>
                <p class="message"><?= $message["success"] ?></p>
            <?php } ?>
            <?php if (!empty($message["error"])) { ?>
                <p class="message error"><?= $message["error"] ?></p>
            <?php } ?>

            <table class="syllabus-details">
                    <tr>
                        <th>Prerequisite:</th>
                        <td><?= htmlspecialchars($syllabus['prerequisite'] ?? 'None') ?></td>
                    </tr>
                    <tr>
                        <th>Credit:</th>
                        <td><?= htmlspecialchars($syllabus['credit'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>College:</th>
                        <td><?= htmlspecialchars($syllabus['college'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Department:</th>
                        <td><?= htmlspecialchars($syllabus['department'] ?? '') ?></td>
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
                        <th>Approval Level: </th>
                        <td><?= htmlspecialchars($syllabus['level_id'] ?? '') ?></td>
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
                    <tr>
                        <th>Description:</th>
                        <td><?= nl2br(htmlspecialchars($syllabus['description'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Latest Comment:</th>
                        <td><?= nl2br(htmlspecialchars($syllabus['latest_comment'] ?? '')) ?></td>
                    </tr>
                </table>
  

            <form method="POST" id="syllabusActionForm">
                <?php if ($_SESSION["role_id"] == 4 && ($syllabus['level_id'] ?? 0) == 4){ ?>
                    <div class="input-g">
                        <label for="comment">Comment: <span style="color:red;">*</span></label><br>
                        <textarea id="comment" name="comment" rows="4" cols="50" placeholder="Enter your comment here..."><?= htmlspecialchars($syllabus["comment"] ?? "") ?></textarea><br>
                        <p style="color:red;"><?= $errors['comment'] ?? "" ?></p><br>
                    </div>
                <?php if(!isset($message["success"])){?>
                    <div class="controls">
                        <!-- These buttons now open modals instead of submitting -->
                        <button type="button" id="openRejectModal">Reject</button>
                        <button type="button" id="openApproveModal">Approve</button>
                    </div>
                <?php }} else if (($syllabus['level_id'] ?? 0) == 5 && !isset($message["success"])){ ?>
                    <div class="del-btn">
                        <!-- This button now opens a modal instead of submitting -->
                        <button type="button" id="openDeleteModal">Delete</button>
                    </div>
                <?php } ?>
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

<!-- Approve Modal -->
<div class="modal-bg" id="modal-IdApprove">
    <div class="modal">
        <h2>Approve Syllabus?</h2>
        <p>Are you sure you want to approve this syllabus? This action cannot be undone.</p>
        <div class="con-button">
            <button type="button" id="cancelApprove">Cancel</button>
            <!-- This button submits the form with the name 'approve' -->
            <button type="submit" name="approve" form="syllabusActionForm">Approve</button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal-bg" id="modal-IdReject">
    <div class="modal">
        <h2>Reject Syllabus?</h2>
        <p>Are you sure you want to reject this syllabus? The creator will be notified to revise it. Please ensure you have left a comment.</p>
        <div class="con-button">
            <button type="button" id="cancelReject">Cancel</button>
             <!-- This button submits the form with the name 'reject' -->
            <button type="submit" name="reject" class="reject-btn" form="syllabusActionForm">Reject</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-bg" id="modal-IdDelete">
    <div class="modal">
        <h2>Delete Syllabus?</h2>
        <p>Are you sure you want to permanently delete this syllabus? This action cannot be undone.</p>
        <div class="con-button">
            <button type="button" id="cancelDelete">Cancel</button>
             <!-- This button submits the form with the name 'delete' -->
            <button type="submit" name="delete" class="delete-btn" form="syllabusActionForm">Delete</button>
        </div>
    </div>
</div>


<script>
    // Helper function to manage modal events to avoid repeating code
    function setupModal(modalId, openBtnId, cancelBtnId) {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        const cancelBtn = document.getElementById(cancelBtnId);

        // If the open button doesn't exist on the page, do nothing.
        // This prevents errors since PHP conditionally renders the buttons.
        if (!openBtn) {
            return;
        }

        // --- Event Listeners ---
        openBtn.addEventListener("click", () => {
            modal.style.display = "flex";
        });

        cancelBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });

        modal.addEventListener("click", (e) => {
            // Close the modal if the user clicks on the background overlay
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });
    }

    // Initialize all modals
    setupModal('modal-IdApprove', 'openApproveModal', 'cancelApprove');
    setupModal('modal-IdReject', 'openRejectModal', 'cancelReject');
    setupModal('modal-IdDelete', 'openDeleteModal', 'cancelDelete');
</script>
</body>
</html>
