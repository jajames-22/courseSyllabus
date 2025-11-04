<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once "../class/accounts.php";
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

// Handle verification
if (isset($_POST['verify'])) {
    if(!$accountsObj->isEmployeeIdExist($account['employee_id'])){
        if ($accountsObj->verifyAccount($id)) {
            echo "<script>alert('Account verified successfully!'); window.location.href='vpDashboard.php?page=manageAcc&page3=pendingAcc';</script>";
            exit;
        }
    } else {
        $error["message"] = "The Employee ID of this account already exists.";
    }
}

// Handle move to pending
if (isset($_POST['moveToPending'])) {
    if ($accountsObj->moveAccountToPending($id)) {
        echo "<script>alert('Account moved to pending successfully!'); window.location.href='vpDashboard.php?page=manageAcc&page3=verifiedAcc';</script>";
        exit;
    }
}

// Handle deletion
if (isset($_POST['delete'])) {
    if ($accountsObj->deleteAccount($id)) {
        echo "<script>alert('Account deleted successfully!'); window.location.href='vpDashboard.php?page=manageAcc&page3=pendingAcc';</script>";
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
            <button class="back-btn" onclick="history.back()"><- Go Back</button>
            <h2>Account Details</h2>
            <p>Showing the details of the account</p>
            <hr>

            <p style="color: red;"><?= $error["message"] ?? ""?></p>

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

            <form method="post" style="margin-top: 20px;">
                <div class="controls">
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this account?')">Delete Account</button>
                    <?php if ($account['isVerified']) { ?>
                        <button type="submit" name="moveToPending">Move to Pending</button>
                    <?php } else { ?>
                        <button type="submit" name="verify">Verify Account</button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </section>
</body>
</html>
