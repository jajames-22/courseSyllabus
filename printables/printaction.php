<?php
session_start();
require_once "../class/syllabus.php";
$syllabusObj = new Syllabus(); 
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
        <h1>Syllabus Status Distribution Report</h1>
        <br>
        <p class="date">Date Gathered: <?= date('F j, Y') ?></p>
        <br>
        <p>This report shows the number of submitted, approved, rejected, and published actions that happened per college.</p>
        <br>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>College</th>
                <th>Submitted</th>
                <th>Approved</th>
                <th>Rejected</th>
                <th>Published</th>
            </tr>
            <?php
            $results = $syllabusObj->getSyllabusStatusPerCollege();
            $colleges = [];
            $statuses = ['Submitted', 'Approved', 'Rejected', 'Published'];
            $data = [];

            // Group by college
            foreach ($results as $row) {
                $college = $row['college'];
                if (!isset($data[$college])) {
                    $data[$college] = array_fill_keys($statuses, 0);
                }
                $data[$college][$row['action']] = (int)$row['total'];
            }

            // Output table rows
            foreach ($data as $college => $statusData) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($college) . "</td>";
                foreach ($statuses as $status) {
                    echo "<td>" . $statusData[$status] . "</td>";
                }
                echo "</tr>";
            }
            ?>
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