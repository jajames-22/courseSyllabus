<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once "../class/accounts.php";
require_once "../class/stmp_config.php";

$accountsObj = new Accounts();

$error = [];

if (!isset($_GET['id'])) {
    echo "No account selected.";
    exit;
}

$id = $_GET['id'];
$account = $accountsObj->fetchAccountById($id);

if (!$account) {
    echo "Account not found.";
    exit;
}
$mailer = getMailerInstance(); 
// Handle verification
if (isset($_POST['verify'])) {
    if(!$accountsObj->isEmployeeIdExist($account['employee_id'])){
        if ($accountsObj->verifyAccount($id)) { 
            $subject = "Your Account has been Verified!";
            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: crimson; border-bottom: 2px solid crimson; padding-bottom: 10px;'>
                            Account Verified!
                        </h2>
                        <p>Hello " . htmlspecialchars($account['fname']) . ",</p>
                        <p>Great news! Your account in the <strong>Course Syllabus Approval System</strong> has been verified by an administrator.</p>
                        <p>You can now log in and access all the features available to you. We are excited to have you on board.</p>
                        <p style='margin-top: 25px;'>
                            Thank you for your patience,<br>
                            <em>The System Administration Team</em>
                        </p>
                    </div>
                </div>
            ";
            sendNotificationEmail($mailer, $account['email'], $subject, $body);
            $_SESSION['message'] = "Account is verified successfully.";
            header("Location: vpDashboard.php?page=manageAcc");
            exit;
        }
    } else {
        $error["message"] = "The Employee ID of this account already exists.";
    }
}

// Handle move to pending
if (isset($_POST['moveToPending'])) {
    if ($accountsObj->moveAccountToPending($id)) {
        $_SESSION['message'] = "Account moved to pending successfully.";
        header("Location: vpDashboard.php?page=manageAcc");
        exit;
    }
}

// Handle deletion
if (isset($_POST['delete'])) {
    if ($accountsObj->deleteAccount($id)) {
        $_SESSION['message'] = "Account deleted successfully.";
        header("Location: vpDashboard.php?page=manageAcc");
        exit;
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
    <title>View Account</title>
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
            <h2>Account Details</h2>
            <p>Showing the details of the account</p>
            <hr>

            <?php if (!empty($message["success"])): ?>
                <p class="message"><?= htmlspecialchars($message["success"]) ?></p>
            <?php endif; ?>

            <?php if (!empty($message["error"])): ?>
                <p class="message error"><?= htmlspecialchars($message["error"]) ?></p>
            <?php endif; ?>

            <table class="syllabus-details">
                <tr>
                    <th>Employee ID</th>
                    <td><?= htmlspecialchars($account['employee_id']) ?></td>
                </tr>
                <tr>
                    <th>Full Name</th>
                    <td><?= htmlspecialchars($account['fname'] . ' ' . $account['lname']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($account['email']) ?></td>
                </tr>
                <tr>
                    <th>College</th>
                    <td><?= htmlspecialchars($account['college']) ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?= htmlspecialchars($account['department']) ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?= htmlspecialchars($account['role_name']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td style="color: <?= $account['isVerified'] ? 'green' : 'red' ?>;">
                    <?= $account['isVerified'] ? 'Verified' : 'Pending' ?></td>
                </tr>
            </table>
            <label for="">ID Picture: </label>
            <div style="margin: 20px 0;">
                <?php if ($account['pic_dir']): ?>
                    <img onclick="window.location.href='<?= htmlspecialchars($account['pic_dir']) ?>'" src="<?= htmlspecialchars($account['pic_dir']) ?>" alt="Profile Picture" width="150px" height="200px" style="border-radius: 10px; border: 1px solid #ccc; object-fit: contain;">
                <?php else: ?>
                    <p style="color: red; font-weight: bold;">Failed to submit picture</p>
                <?php endif; ?>
            </div>

            <form method="post" style="margin-top: 20px;" id="viewAccountForm">
                <div class="controls">
                    <button type="button" id="toDelete">Delete Account</button>
                    <?php if ($account['isVerified']) { ?>
                        <button type="button" id="toPending" >Move to Pending</button>
                    <?php } else { ?>
                        <button type="button" id="toVerify" >Verify Account</button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </section>
    <div class="modal-bg" id="modal-IdDelete">
        <div class="modal">
            <h2>Delete Account?</h2>
            <p>Are you sure you want to delete the account of <?= htmlspecialchars($account['fname'] . ' ' . $account['lname']) ?>? The user will be notified after confirming.</p>
            <div class="con-button">
                <button type="button" id="cancelDelete">Cancel</button>
                <button type="submit" name="delete" id="deleteNow" form="viewAccountForm">Delete</button>
            </div>
        </div>
    </div>

    <div class="modal-bg" id="modal-IdVerify">
        <div class="modal">
            <h2>Verify Account?</h2>
            <p>Are you sure you want to verify the account of <?= htmlspecialchars($account['fname'] . ' ' . $account['lname']) ?>? They will be notified and granted access to the system.</p>
            <div class="con-button">
                <button type="button" id="cancelVerify">Cancel</button>
                <button type="submit" name="verify" id="verifyNow" form="viewAccountForm">Verify</button>
            </div>
        </div>
    </div>

    <!-- Move to Pending Modal (New) -->
    <div class="modal-bg" id="modal-IdPending">
        <div class="modal">
            <h2>Move Account to Pending?</h2>
            <p>Are you sure you want to move the account of <?= htmlspecialchars($account['fname'] . ' ' . $account['lname']) ?> to pending? Their access will be temporarily revoked until re-verified.</p>
            <div class="con-button">
                <button type="button" id="cancelPending">Cancel</button>
                <button type="submit" name="moveToPending" id="pendingNow" form="viewAccountForm">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        const deleteModal = document.getElementById('modal-IdDelete');
        const deleteBtn = document.getElementById('toDelete');
        const cancelDelete = document.getElementById("cancelDelete");

        deleteBtn.addEventListener("click", () => {
            deleteModal.style.display = "flex";
        });

        cancelDelete.addEventListener("click", () => {
            deleteModal.style.display = "none";
        });

        deleteModal.addEventListener("click", (e) => {
            if (e.target === deleteModal) {
                deleteModal.style.display = "none";
            }
        });

        const verifyModal = document.getElementById('modal-IdVerify');
        const verifyBtn = document.getElementById('toVerify');
        const cancelVerify = document.getElementById("cancelVerify");

        if (verifyBtn) {
            verifyBtn.addEventListener("click", () => {
                verifyModal.style.display = "flex";
            });

            cancelVerify.addEventListener("click", () => {
                verifyModal.style.display = "none";
            });

            verifyModal.addEventListener("click", (e) => {
                if (e.target === verifyModal) {
                    verifyModal.style.display = "none";
                }
            });
        }


        // --- Move to Pending Modal Logic (New) ---
        const pendingModal = document.getElementById('modal-IdPending');
        const pendingBtn = document.getElementById('toPending');
        const cancelPending = document.getElementById("cancelPending");

        // Check if the pending button exists before adding a listener
        if (pendingBtn) {
            pendingBtn.addEventListener("click", () => {
                pendingModal.style.display = "flex";
            });

            cancelPending.addEventListener("click", () => {
                pendingModal.style.display = "none";
            });

            pendingModal.addEventListener("click", (e) => {
                if (e.target === pendingModal) {
                    pendingModal.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
