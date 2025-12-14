<?php
session_start();
require_once "../class/syllabus.php";
$syllabusObj = new Syllabus(); 
$results = $syllabusObj->getApprovalTimes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/printables.css">
    <title>Report</title>
</head>
<body>
    <header>
        <div class="school-logo">
            <img src="../styles/col_logo/school-logo.jpg" alt="">
        </div>
        <div class="text-header">
            <p>Republic of the Philippines</p>
            <p>WESTERN MINDANAO STATE UNIVERSITY</p>
            <p>Office of the Academic Affairs</p>
            <p>Normal Road Baliwasan Zamboanga City</p>
            <img src="../styles/col_logo/internalization.png" class="internalization" alt="">
        </div>
        <div class="school-logo"></div>
    </header>
    <main>
        <br>
        <hr>
        <br>
        <h1>Average Approval Times Report</h1>
        <br>
        <p class="date">Date Gathered: <?= date('F j, Y') ?></p>
        <br>
        <p>This report shows the average number of hours it takes for syllabi to move from submission to publication in each college.</p>
        <br>
        <table border="1">
            <tr>
                <th>Colleges</th>
                <th>No. of Syllabus</th>
            </tr>
            <?php 
            foreach ($results as $row) {?>
            <tr>
                <td><?= $row['college'] ?></td>
                <td><?= round($row['avg_approval_hours'], 2)?> hours</td>                
            </tr>
            <?php }?>
        </table>
    </main>
    <footer>
        <div>
            <p class="name"><?= $_SESSION["fname"] ?> <?= $_SESSION["lname"] ?></p>
            <p class="role">Vice President for Academic Affairs</p>
        </div>
    </footer>
    <?php echo "<script>alert('Click Ctrl+P to print this page document');</script>"; ?>
</body>
</html>