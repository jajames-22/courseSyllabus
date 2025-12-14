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
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="stylesheet" href="../styles/modalsKpi.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
</head>
<body>
    <section class="side-bar">
        <header>
            <img class="dashboard-logo" src="../styles/col_logo/inverted-logo.png" alt="">
        </header>
        <div>
            <ul>
                <li class="option <?php if ($page == 'dashboard') echo 'active'; ?>" onclick="location.href='?page=dashboard'">
                    <img class="tab-icon"
                        src="../styles/images/dashboard_icon<?php echo ($page != 'dashboard') ? '_red' : ''; ?>.svg"
                        alt="">Dashboard
                </li>

                <li class="option <?php if ($page == 'manageSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus'">
                    <img class="tab-icon"
                        src="../styles/images/book_icon<?php echo ($page != 'manageSyllabus') ? '_red' : ''; ?>.svg"
                        alt="">Manage Syllabus
                </li>

                <li class="option <?php if ($page == 'manageAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc'">
                    <img class="tab-icon"
                        src="../styles/images/group_icon<?php echo ($page != 'manageAcc') ? '_red' : ''; ?>.svg"
                        alt="">Manage Accounts
                </li>

                <li class="option <?php if ($page == 'notifications') echo 'active'; ?>" onclick="location.href='?page=notifications'">
                    <img class="tab-icon"
                        src="../styles/images/notifications_icon<?php echo ($page != 'notifications') ? '_red' : ''; ?>.svg"
                        alt="">Notifications
                </li>

                <li class="option <?php if ($page == 'myAccount') echo 'active'; ?>" onclick="location.href='?page=myAccount'">
                    <img class="tab-icon"
                        src="../styles/images/account_icon<?php echo ($page != 'myAccount') ? '_red' : ''; ?>.svg"
                        alt="">My Accounts
                </li>

            </ul>
        </div>

    </section>
    <section class="content">
        <div class="headbar">
            <div>
                <h2 class="welcome">Hello, Admin!</h2>
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
                        <div class="img-count">
                            <img src="../styles/images/book_icon.svg" alt="">
                            <p><?php $count = $syllabusObj->countSyllabusLevel5();               
                        echo $count; ?></p>
                         </div>
                    </div>
                    <div class="dash-box">
                        <p>Pending Syllabus</p>
                        <div class="img-count">
                            <img src="../styles/images/pending_icon.svg" alt="">
                            <p><?php $count = $syllabusObj->countSyllabusLevel4();                     
                        echo $count; ?></p>
                        </div>
                    </div>
                    
                    <div class="dash-box">
                        <p>Verified Accounts</p>
                        <div class="img-count">
                            <img src="../styles/images/account_icon.svg" alt="">
                            <p><?php $count = $accountsObj->countVerifiedAccounts();
                        echo $count;?></p>
                        </div>
                    </div>

                    <div class="dash-box">
                        <p>Pending Accounts</p>
                        <div class="img-count">
                            <img src="../styles/images/pending_icon.svg" alt="">
                            <p><?php $count = $accountsObj->countUnverifiedAccounts();
                        echo $count; ?></p>
                        </div>
                    </div>
                </div>
            <br>
            <div class="dash-g">
                <div class="title-btn">
                    <h2>Published Syllabus by College</h2>
                    <button class="dash-btn" onclick="window.open('../printables/printpsc.php', '_blank')">Print this report</button>
                </div>
                <p>This graph shows the number of syllabus submitted by college.</p>
                <div class="bargraph-con">
                <?php    
                        function getCollegeAbbr($college) {
                            preg_match_all('/\b[A-Z]/', $college, $matches);
                            return implode('', $matches[0]);
                        }

                        $results = $syllabusObj->getPublishedSyllabusPerCollege();
                        $maxCount = 0;
                        foreach ($results as $r) {
                            if ($r['published_count'] > $maxCount) {
                                $maxCount = $r['published_count'];
                            }
                        }
                    ?>
                    <div class="bargraph">
                        <?php 
                            foreach ($results as $r){
                            $count = (int)$r['published_count'];
                            $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0; 
                        ?>
                        <div class="bar" style="height: <?= $height ?>%;"><p><?= $count ?></p></div>
                        <?php }?>
                    </div>
                    <div class="bargraph-label">
                        <?php
                            foreach ($results as $r){
                            $abbr = getCollegeAbbr($r['college']);
                        ?>
                            <p><?= htmlspecialchars($abbr) ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <br>
            <div class="dash-level">
                <div class="dash-g">
                    <div class="title-btn">
                        <h2>Approval Times</h2> 
                        <button class="dash-btn" onclick="window.open('../printables/printat.php', '_blank')">Print this report</button>
                    </div>
                        <p>This graph shows the average number of hours it takes for syllabi to move from submission to publication in each college.</p>
                    <br>
                    <?php 
                        $results = $syllabusObj->getApprovalTimes(); // make sure it includes 'college'
                        $colleges = [];
                        $avgHours = [];
                        foreach($results as $row){
                            $colleges[] = getCollegeAbbr(htmlspecialchars($row['college'])); // only works if SQL returns 'college'
                            $avgHours[] = round($row['avg_approval_hours'], 2);   // column name from SQL
                        }
                    ?>
                    <canvas id="approvalChart" width="500" height="300"></canvas>
                </div>
                <div class="dash-g">
                    <div class="title-btn">
                        <h2>Action Taken</h2>
                        <button class="dash-btn" onclick="window.open('../printables/printaction.php', '_blank')">Print this report</button>
                    </div>
                    <p>This stacked bar chart shows the number of submitted, approved, rejected, and published actions that happened per college.</p>
                    <?php
                        $results = $syllabusObj->getSyllabusStatusPerCollege();
                        $colleges = [];
                        $statuses = ['Submitted', 'Approved', 'Rejected', 'Published'];
                        $data = [];

                        // Initialize arrays
                        foreach ($statuses as $status) {
                            $data[$status] = [];
                        }

                        // Group data by college
                        foreach ($results as $row) {
                            $college = $row['college'];
                            if (!in_array($college, $colleges)) {
                                $colleges[] = $college;
                            }
                        }

                        // Convert full college names to abbreviations
                        $abbrColleges = array_map('getCollegeAbbr', $colleges);

                        // Fill data
                        foreach ($statuses as $status) {
                            foreach ($colleges as $college) {
                                $found = false;
                                foreach ($results as $row) {
                                    if ($row['college'] === $college && $row['action'] === $status) {
                                        $data[$status][] = (int)$row['total'];
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) $data[$status][] = 0;
                            }
                        }
                    ?>
                    <canvas id="statusChart" height="150" width="200"></canvas>
                </div>
            </div>
            <?php } else if($page == 'manageSyllabus'){?>
                <h2>Manage Syllabus</h2>
                <ul class="sub-tab">
                    <li class="<?php if ($page2 == 'publishedSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus&page2=publishedSyllabus'">Published Syllabus</li>
                    <li class="<?php if ($page2 == 'pendingSyllabus') echo 'active'; ?>" onclick="location.href='?page=manageSyllabus&page2=pendingSyllabus'">Pending Syllabus</li>
                </ul>

                <?php if($page2 == 'publishedSyllabus'){?>
                    <?php
                    
                    $searchSyllabus = $_GET['search_syllabus'] ?? '';
                    $filterSyllabusCollege = $_GET['filter_syllabus_college'] ?? '';
                    $sortSyllabusOrder = $_GET['sort_syllabus_order'] ?? 'asc';
                    $currentPage = $_GET['page'] ?? ''; // Get the current page/tab

                    // --- Fetch the data using the new search function ---
                    $publishedSyllabi = $syllabusObj->searchPublishedSyllabus($searchSyllabus, $filterSyllabusCollege, $sortSyllabusOrder);

                    // --- Fetch the list of colleges for the dropdown ---
                    $syllabusColleges = $syllabusObj->getDistinctCollegesForSyllabus();
                    ?>

                    <div class="page-header">
                        <div class="header-info">
                            <p>Here are the syllabus that are published</p>
                        </div>

                        <!-- Search and Filter Form for Syllabi -->
                        <form action="" method="GET" class="search-form">
                            <!-- Hidden input to stay on the correct tab -->
                            <input type="hidden" name="page" value="<?= htmlspecialchars($currentPage) ?>">

                            <div class="form-group">
                                <label for="search_syllabus">Search by Code/Name:</label>
                                <input type="text" id="search_syllabus" name="search_syllabus" value="<?= htmlspecialchars($searchSyllabus) ?>" placeholder="Enter code or name...">
                            </div>
                            
                            <div class="form-group">
                                <label for="filter_syllabus_college">Filter by College:</label>
                                <select id="filter_syllabus_college" name="filter_syllabus_college">
                                    <option value="">All Colleges</option>
                                    <?php foreach ($syllabusColleges as $college): ?>
                                        <option value="<?= htmlspecialchars($college) ?>" <?= ($filterSyllabusCollege === $college) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($college) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sort_syllabus_order">Sort by Code:</label>
                                <select id="sort_syllabus_order" name="sort_syllabus_order">
                                    <option value="asc" <?= ($sortSyllabusOrder === 'asc') ? 'selected' : '' ?>>Ascending (A-Z)</option>
                                    <option value="desc" <?= ($sortSyllabusOrder === 'desc') ? 'selected' : '' ?>>Descending (Z-A)</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="view-btn">Search</button>
                                <a href="?page=<?= htmlspecialchars($currentPage) ?>" class="reset-btn">Reset</a>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>College</th>
                                <th>Department</th>
                                <th>Creator</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($publishedSyllabi)): ?>
                                <?php foreach ($publishedSyllabi as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                                        <td><?= htmlspecialchars($row['college']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars($row['creator_name']) ?></td>
                                        <td>
                                            <a class="view-btn" href="<?= "adminViewSyllabus.php?id={$row['course_id']}" ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 50px 0;">No published syllabi found matching your criteria.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php }else if($page2 == 'pendingSyllabus'){?>
                    <?php
                    $searchPending = $_GET['search_pending'] ?? '';
                    $filterPendingCollege = $_GET['filter_pending_college'] ?? '';
                    $sortPendingOrder = $_GET['sort_pending_order'] ?? 'asc';
                    $currentPage = $_GET['page'] ?? ''; // Get the current page/tab

                    // --- Fetch the data using the new search function ---
                    $pendingSyllabi = $syllabusObj->searchPendingSyllabus($searchPending, $filterPendingCollege, $sortPendingOrder);

                    // --- Fetch the list of colleges for the dropdown ---
                    $pendingColleges = $syllabusObj->getDistinctCollegesForPendingSyllabus();

                    ?>

                  

                    <!-- Parent wrapper for layout -->
                    <div class="page-header">
                        <div class="header-info">
                            <p>Here are the syllabus waiting to be approved</p>
                        </div>

                        <!-- Search and Filter Form for Pending Syllabi -->
                        <form action="" method="GET" class="search-form">
                            <!-- Hidden input to stay on the correct tab -->
                            <input type="hidden" name="page" value="<?= htmlspecialchars($currentPage) ?>">

                            <div class="form-group">
                                <label for="search_pending">Search by Code/Name:</label>
                                <input type="text" id="search_pending" name="search_pending" value="<?= htmlspecialchars($searchPending) ?>" placeholder="Enter code or name...">
                            </div>
                            
                            <div class="form-group">
                                <label for="filter_pending_college">Filter by College:</label>
                                <select id="filter_pending_college" name="filter_pending_college">
                                    <option value="">All Colleges</option>
                                    <?php foreach ($pendingColleges as $college): ?>
                                        <option value="<?= htmlspecialchars($college) ?>" <?= ($filterPendingCollege === $college) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($college) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sort_pending_order">Sort by Code:</label>
                                <select id="sort_pending_order" name="sort_pending_order">
                                    <option value="asc" <?= ($sortPendingOrder === 'asc') ? 'selected' : '' ?>>Ascending (A-Z)</option>
                                    <option value="desc" <?= ($sortPendingOrder === 'desc') ? 'selected' : '' ?>>Descending (Z-A)</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="view-btn">Search</button>
                                <a href="?page=<?= htmlspecialchars($currentPage) ?>" class="reset-btn">Reset</a>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>College</th>
                                <th>Department</th>
                                <th>Creator</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingSyllabi)): ?>
                                <?php foreach ($pendingSyllabi as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                                        <td><?= htmlspecialchars($row['college']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars($row['creator_name']) ?></td>
                                        <td>
                                            <a class="view-btn" href="<?= "adminViewSyllabus.php?id={$row['course_id']}" ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 50px 0;">No pending syllabi found matching your criteria.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
        
            <?php }} else if($page == 'manageAcc'){?>
                <h2>Manage Accounts</h2>

                <ul class="sub-tab">
                    <li class="<?php if ($page3 == 'verifiedAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc&page3=verifiedAcc'">Verified Accounts</li>
                    <li class="<?php if ($page3 == 'pendingAcc') echo 'active'; ?>" onclick="location.href='?page=manageAcc&page3=pendingAcc'">Pending Accounts</li>
                </ul>

                <?php if($page3 == 'verifiedAcc'){?>
                    <?php
                        
                        $searchName = $_GET['search_name'] ?? '';
                        $filterCollege = $_GET['filter_college'] ?? '';
                        $sortOrder = $_GET['sort_order'] ?? 'asc';
                        $currentPage = $_GET['page'] ?? ''; 

                        $verifiedAccounts = $accountsObj->searchVerifiedAccounts($searchName, $filterCollege, $sortOrder);

                        $colleges = $accountsObj->getDistinctColleges();

                        ?>
                        <p>Here are the list of verified accounts</p>

                        <!-- Search and Filter Form -->
                        <form action="admin.php" method="GET" class="search-form">
                            <input type="hidden" name="page" value="<?= htmlspecialchars($currentPage) ?>">

                            <!-- Add the new 'form-group-grow' class here -->
                            <div class="form-group form-group-grow">
                                <label for="search_name">Search by Name:</label>
                                <input type="text" id="search_name" name="search_name" value="<?= htmlspecialchars($searchName) ?>" placeholder="Enter name...">
                            </div>
                            
                            <!-- And also add the 'form-group-grow' class here -->
                            <div class="form-group form-group-grow">
                                <label for="filter_college">Filter by College:</label>
                                <select id="filter_college" name="filter_college">
                                    <option value="">All Colleges</option>
                                    <?php foreach ($colleges as $college): ?>
                                        <option value="<?= htmlspecialchars($college) ?>" <?= ($filterCollege === $college) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($college) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- No change needed for these smaller elements -->
                            <div class="form-group">
                                <label for="sort_order">Sort by Name:</label>
                                <select id="sort_order" name="sort_order">
                                    <option value="asc" <?= ($sortOrder === 'asc') ? 'selected' : '' ?>>Ascending (A-Z)</option>
                                    <option value="desc" <?= ($sortOrder === 'desc') ? 'selected' : '' ?>>Descending (Z-A)</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="view-btn">Search</button>
                                <a href="admin.php?page=<?= htmlspecialchars($currentPage) ?>" class="reset-btn">Reset</a>
                            </div>
                        </form>

                        <hr>

                        <table>
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>College</th>
                                    <th>Department</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($verifiedAccounts)): ?>
                                    <?php foreach ($verifiedAccounts as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                            <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['college']) ?></td>
                                            <td><?= htmlspecialchars($row['department']) ?></td>
                                            <td><?= htmlspecialchars($row['role_name']) ?></td>
                                            <td>
                                                <a class="view-btn" href="<?= "adminViewAccounts.php?id={$row['acc_id']}" ?>">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 50px 0;">No verified accounts found matching your criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                <?php }else if($page3 == 'pendingAcc'){?>
                    <?php
                    // Assume $accountsObj is already instantiated, and a session is started.

                    // --- Get search, filter, and page values for the PENDING ACCOUNTS section ---
                    $searchPendingAcc = $_GET['search_pending_acc'] ?? '';
                    $filterPendingCollegeAcc = $_GET['filter_pending_college_acc'] ?? '';
                    $sortPendingOrderAcc = $_GET['sort_pending_order_acc'] ?? 'asc';
                    $currentPage = $_GET['page'] ?? ''; // Get the current page/tab

                    // --- Fetch the data using the new search function ---
                    $pendingAccounts = $accountsObj->searchPendingAccounts($searchPendingAcc, $filterPendingCollegeAcc, $sortPendingOrderAcc);

                    // --- Fetch the list of colleges for the dropdown ---
                    $pendingCollegesAcc = $accountsObj->getDistinctCollegesForPendingAccounts();

                    ?>

                    <!-- Parent wrapper for layout -->
                    <div class="page-header">
                        <div class="header-info">
                            <p>Here are the list of pending accounts</p>
                        </div>

                        <!-- Search and Filter Form for Pending Accounts -->
                        <form action="" method="GET" class="search-form">
                            <!-- Hidden input to stay on the correct tab -->
                            <input type="hidden" name="page" value="<?= htmlspecialchars($currentPage) ?>">

                            <div class="form-group">
                                <label for="search_pending_acc">Search by Name:</label>
                                <input type="text" id="search_pending_acc" name="search_pending_acc" value="<?= htmlspecialchars($searchPendingAcc) ?>" placeholder="Enter name...">
                            </div>
                            
                            <div class="form-group">
                                <label for="filter_pending_college_acc">Filter by College:</label>
                                <select id="filter_pending_college_acc" name="filter_pending_college_acc">
                                    <option value="">All Colleges</option>
                                    <?php foreach ($pendingCollegesAcc as $college): ?>
                                        <option value="<?= htmlspecialchars($college) ?>" <?= ($filterPendingCollegeAcc === $college) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($college) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sort_pending_order_acc">Sort by Name:</label>
                                <select id="sort_pending_order_acc" name="sort_pending_order_acc">
                                    <option value="asc" <?= ($sortPendingOrderAcc === 'asc') ? 'selected' : '' ?>>Ascending (A-Z)</option>
                                    <option value="desc" <?= ($sortPendingOrderAcc === 'desc') ? 'selected' : '' ?>>Descending (Z-A)</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="view-btn">Search</button>
                                <a href="?page=<?= htmlspecialchars($currentPage) ?>" class="reset-btn">Reset</a>
                            </div>
                        </form>
                    </div>

                    <hr>

                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>College</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingAccounts)): ?>
                                <?php foreach ($pendingAccounts as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                        <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['college']) ?></td>
                                        <td><?= htmlspecialchars($row['department']) ?></td>
                                        <td><?= htmlspecialchars($row['role_name']) ?></td>
                                        <td>
                                            <a class="view-btn" href="<?= "adminViewAccounts.php?id={$row['acc_id']}" ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 50px 0;">No pending accounts found matching your criteria.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
            <?php }} else if($page == 'notifications'){?>
                <h2>Notifications</h2>
                <p>We remind you here!</p>
                <hr>

                <?php
                // Step 1: Fetch the notifications (this remains the same)
                $notifications = $syllabusObj->getAllNotifications($_SESSION["acc_id"]);

                // Step 2: Check if the notifications array is empty BEFORE trying to loop through it
                if (empty($notifications)) {
                    // If it's empty, display a user-friendly message instead of the loop
                    ?>
                    <div class="no-notif-message" style="text-align: center; padding: 3rem 1rem; color: #6c757d;">
                        <p>Your notification inbox is empty.</p>
                    </div>
                    <?php
                } else {
                    // Step 3: If the array is NOT empty, proceed with the original foreach loop
                    foreach ($notifications as $notif) {
                        ?>
                        <div id="notif_<?= $notif['notif_id'] ?>" class="notif-bar <?= $notif["is_read"] ? "notif-read" : "notif-unread" ?>">
                            <div class="notif-details">
                                <!-- Security: Added htmlspecialchars() to prevent XSS -->
                                <h3><?= htmlspecialchars($notif["type"]) ?></h3>
                                <p><?= htmlspecialchars($notif["message"]) ?></p>
                            </div>
                            <div class="notif-buttons">
                                <button onclick="toggleRead(<?= $notif['notif_id'] ?>)">
                                    <?= $notif["is_read"] ? "Mark as Unread" : "Mark as Read" ?>
                                </button>
                                <button onclick="deleteNotif(<?= $notif['notif_id'] ?>)">Delete</button>
                            </div>
                        </div>
                        <?php
                    } // End of foreach
                } // End of else
                ?>
            <?php } else if($page == 'myAccount'){?>
                <h2>My Account</h2>
                <hr>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
            <?php }?>
        </div>
    </section>
    <?php if (isset($_SESSION['message'])): ?>
    <div class="corner-message">
        <p><?= htmlspecialchars($_SESSION['message']) ?></p>
        <a href="" >Close</a>
    </div>
    <?php
        unset($_SESSION['message']);
    endif;
    ?>
    <script>
        function toggleRead(id) {
            fetch("notif_action.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: `action=toggle_read&notif_id=${id}`
            })
            .then(res => res.text())
            .then(() => {
                // update UI without refresh
                location.reload(); // or remove element dynamically
            });
        }

        function deleteNotif(id) {
            fetch("notif_action.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: `action=delete&notif_id=${id}`
            })
            .then(res => res.text())
            .then(() => {
                // update UI without refresh
                document.querySelector(`#notif_${id}`).remove();
            });
        }

        const ctx = document.getElementById('approvalChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie', // changed from 'bar' to 'pie'
            data: {
                labels: <?= json_encode($colleges) ?>,
                datasets: [{
                    label: 'Approval Time (Hours)',
                    data: <?= json_encode($avgHours) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ], // add more colors if needed
                    borderColor: 'rgba(255, 255, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'right' // position of the labels
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' hrs';
                            }
                        }
                    }
                }
            }
        });


        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode($abbrColleges) ?>,
                datasets: [
                    {
                        label: 'Submitted',
                        data: <?= json_encode($data['Submitted']) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)'
                    },
                    {
                        label: 'Approved',
                        data: <?= json_encode($data['Approved']) ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    },
                    {
                        label: 'Rejected',
                        data: <?= json_encode($data['Rejected']) ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)'
                    },
                    {
                        label: 'Published',
                        data: <?= json_encode($data['Published']) ?>,
                        backgroundColor: 'rgba(255, 206, 86, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: false,
                        text: 'Syllabus Status Distribution per College'
                    },
                },
                scales: {
                    x: { stacked: true, title: { display: true, text: 'College' } },
                    y: { stacked: true, title: { display: true, text: 'Number of Actions' }, beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>