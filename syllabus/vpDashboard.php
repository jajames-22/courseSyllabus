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

require_once "../class/syllabus.php";
require_once "../class/accounts.php";
$syllabusObj = new Syllabus(); 
$accountsObj = new Accounts();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$_SESSION["page"] = $page;
$page2 = isset($_GET['page2']) ? $_GET['page2'] : 'publishedSyllabus';
$page3 = isset($_GET['page3']) ? $_GET['page3'] : 'verifiedAcc';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/dashboard.css">
    <title>VP Dashboard</title>
</head>
<body>
    <section class="side-bar">
        <header>
            <img class="dashboard-logo" src="../styles/col_logo/19.png" alt="">
        </header>
        <div>
            <ul>
                <li class="option <?php if ($page == 'dashboard') echo 'active'; ?>" onclick="location.href='?page=dashboard'">
                <img class="tab-icon" src="../styles/images/dashboard_icon.svg" alt="">Dashboard</li>

                <li class="option <?php if ($page == 'manageSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus'">
                <img class="tab-icon" src="../styles/images/book_icon.svg" alt="">Manage Syllabus</li>

                <li class="option <?php if ($page == 'manageAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc'">
                <img class="tab-icon" src="../styles/images/group_icon.svg" alt="">Manage Accounts</li>

                <li class="option <?php if ($page == 'myAccount') echo 'active'; ?>" onclick="location.href='?page=myAccount'">
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
            <?php if($page == 'dashboard'){?>
                <h2>Dashboard</h2>
                <p>Keep in touch because of this dashboard!</p>
                <hr>
                <div class="sum-boxes">
                    <div class="dash-box">
                        <p>Published Syllabus</p>
                        <p><?php $count = $syllabusObj->countSyllabusLevel5();
                        echo $count; ?></p>
                    </div>
                    <div class="dash-box">
                        <p>Pending Syllabus</p>
                        <p><?php $count = $syllabusObj->countSyllabusLevel4();
                        echo $count; ?></p>
                    </div>
                    
                    <div class="dash-box">
                        <p>Verified Accounts</p>
                        <p><?php $count = $accountsObj->countVerifiedAccounts();
                        echo $count;?></p>
                    </div>

                    <div class="dash-box">
                        <p>Pending Accounts</p>
                        <p><?php $count = $accountsObj->countUnverifiedAccounts();
                        echo $count; ?></p>
                    </div>
                </div>
            <?php } else if($page == 'manageSyllabus'){?>
                <h2>Manage Syllabus</h2>
                <ul class="sub-tab">
                    <li class="<?php if ($page2 == 'publishedSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus&page2=publishedSyllabus'">Published Syllabus</li>
                    <li class="<?php if ($page2 == 'pendingSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus&page2=pendingSyllabus'">Pending Syllabus</li>
                </ul>

                <?php if($page2 == 'publishedSyllabus'){?>
                    <h2>Published Syllabus</h2>
                    <p>Here are the syllabus that are published</p>
                    <hr>
                    <table>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Creator</th>  
                            <th>Actions</th>   
                        </tr>
                        <?php $publishedSyllabi = $syllabusObj->fetchAllPublishedSyllabus();
                        if (!empty($publishedSyllabi)) { ?>
                            <?php foreach ($publishedSyllabi as $row) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                                    <td><?= htmlspecialchars($row['college']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['creator_name']) ?></td>
                                    <td>
                                        <a class="view-btn" href='<?="vpViewSyllabus.php?id={$row['course_id']}"?>'>View</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No published syllabi found.</td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php }else if($page2 == 'pendingSyllabus'){?>
                    <h2>Pending Syllabus</h2>
                    <p>Here are the syllabus waiting to be approved</p>
                    <hr>
                    <table>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Creator</th>  
                            <th>Actions</th>   
                        </tr>
                        <?php $pendingSyllabi = $syllabusObj->fetchAllPendingSyllabus(); 
                        if (!empty($pendingSyllabi)) { ?>
                        <?php foreach ($pendingSyllabi as $row) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td><?= htmlspecialchars($row['college']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars($row['creator_name']) ?></td>
                                <td>
                                    <a class="view-btn" href='<?="vpViewSyllabus.php?id={$row['course_id']}"?>'>View</a>
                                </td>
                            </tr>
                        <?php }} else { ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No pending syllabi found.</td>
                            </tr>
                        <?php } ?>
                    </table>
        
            <?php }} else if($page == 'manageAcc'){?>
                <h2>Manage Accounts</h2>

                <ul class="sub-tab">
                    <li class="<?php if ($page3 == 'verifiedAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc&page3=verifiedAcc'">Verified Accounts</li>
                    <li class="<?php if ($page3 == 'pendingAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc&page3=pendingAcc'">Pending Accounts</li>
                </ul>

                <?php if($page3 == 'verifiedAcc'){?>
                    <h2>Verified Accounts</h2>
                    <p>Here are the list of verified accounts</p>
                    <hr>
                    <table>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        <?php 
                        $verifiedAccounts = $accountsObj->fetchAllVerifiedAccounts();
                        if (!empty($verifiedAccounts)) { ?>
                            <?php foreach ($verifiedAccounts as $row) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                    <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['college']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['role_name']) ?></td>
                                    <td>
                                        <a class="view-btn" href='<?="vpViewAccounts.php?id={$row['acc_id']}"?>'>View</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">No verified accounts found.</td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php }else if($page3 == 'pendingAcc'){?>
                    <h2>Pending Accounts</h2>
                    <p>Here are the list of pending accounts</p>
                    <hr>
                    <table>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                        <?php 
                        $pendingAccounts = $accountsObj->fetchAllPendingAccounts();
                        if (!empty($pendingAccounts)) { ?>
                            <?php foreach ($pendingAccounts as $row) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                    <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['college']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['role_name']) ?></td>
                                    <td>
                                        <a class="view-btn" href='<?="vpViewAccounts.php?id={$row['acc_id']}"?>'>View</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">No pending accounts found.</td>
                            </tr>
                        <?php } ?>
                    </table>
            <?php }} else if($page == 'myAccount'){?>
                <h2>My Account</h2>
                <hr>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
            <?php }?>
        </div>
    </section>
</body>
</html>