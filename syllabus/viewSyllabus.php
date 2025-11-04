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
                <?php } ?>
                
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
            <button class="back-btn" onclick="history.back()"><- Go Back</button>
            <p>Viewing Syllabus</p>
            <h2><?= htmlspecialchars($syllabus['course_code'] ?? '') ?> - <?= htmlspecialchars($syllabus['course_name'] ?? '') ?></h2>
            <hr>
            
            <?php if (!empty($message["success"])){ ?>
                <p class="message"><?= $message["success"] ?></p>
            <?php } ?>
            <?php if (!empty($message["error"])){ ?>
                        <p class="message error"><?= $message["error"] ?></p>
            <?php } ?>

            <table class="syllabus-details">
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
            <form method="POST">
            <?php if($_SESSION["role_id"]!=1 && $syllabus['level_id']!=5 && $_SESSION["acc_id"]!=$syllabus["acc_id"]){?>
                <div class="input-g">
                    <label for="comment">Comment: <span color="red">*</span></label><br>
                    <textarea id="comment" name="comment" rows="4" cols="50" placeholder="Enter your comment here..."><?= htmlspecialchars($syllabus["comment"]??"")?></textarea><br>
                    <p style="color:red;"><?= $errors['comment'] ?? "" ?></p>
                </div>
            <?php if(!isset($message["success"])){?>
                <div class="controls">
                    <button type="submit" name="reject">Reject</button>
                    <button type="submit" name="approve">Approve</button>
                </div>
            <?php }} ?>
            </form>
        </div>
    </section>
</body>
</html>