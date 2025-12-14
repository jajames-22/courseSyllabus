<?php
session_start();
require_once "../class/accounts.php";
$accObj = new Accounts();

if (!isset($_SESSION["lname"], $_SESSION["fname"], $_SESSION["email"], $_SESSION["employeeId"], $_SESSION["college"], $_SESSION["department"])) {
    header("Location: register.php");
    exit();
}

$account = [];
$errors = $_SESSION["errors"] ?? [];
unset($_SESSION["errors"]);

$roleId = $_SESSION["role_id"] ?? "";
$role = "";

if ($roleId == 1) {
    $role = "Instructor";
} elseif ($roleId == 2) {
    $role = "Department Head";
} elseif ($roleId == 3) {
    $role = "Dean";
} else {
    $role = "Unknown";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_FILES["idPic"]["name"])) {
        $errors["idPic"] = "ID Picture is required.";
    } else {
        $targetDir = "../empId/";
        $fileName = basename($_FILES["idPic"]["name"]);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["idPic"]["tmp_name"]);
        if ($check === false) $errors["idPic"] = "File is not an image.";

        if ($_FILES["idPic"]["size"] > 2000000)
            $errors["idPic"] = "Sorry, your file is too large.";

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif']))
            $errors["idPic"] = "Only JPG, JPEG, PNG & GIF files are allowed.";

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $counter = 1;
        while (file_exists($targetFile)) {
            $fileName = $baseName . "($counter)." . $imageFileType;
            $targetFile = $targetDir . $fileName;
            $counter++;
        }

        if (empty($errors["idPic"])) {
            if (move_uploaded_file($_FILES["idPic"]["tmp_name"], $targetFile)) {
                $account["id_name"] = $fileName;
                $account["id_dir"] = $targetFile;
            } else {
                $errors["idPic"] = "There was an error uploading your file.";
            }
        }
    }

    // Continue registration if no errors
    if (empty(array_filter($errors))) {
        $accObj->lname = $_SESSION["lname"];
        $accObj->fname = $_SESSION["fname"];
        $accObj->mname = $_SESSION["mname"];
        $accObj->email = $_SESSION["email"];
        $accObj->employeeId = $_SESSION["employeeId"];
        $accObj->password = $_SESSION["password"];
        $accObj->col_dep_id = $_SESSION["col_dep_id"];

        if ($_SESSION["college"] == "Office of the Academic Affairs") {
            $accObj->role_id = 4;
        } else {
            $accObj->role_id = $_SESSION["role_id"];
        }

        $accObj->pic_name = $account["id_name"];
        $accObj->pic_dir = $account["id_dir"];
        
        if ($accObj->register()) {
            $account = [];
            session_destroy();
            header("Location: register7.php");
            exit();
        } else {
            $_SESSION["errors"]["idPic"] = "Registration failed. Please try again.";
            header("Location: register6.php");
            exit();
        }
    } else {
        $_SESSION["errors"] = $errors;
        header("Location: register6.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/review.css?v=2"> <!-- Version bump recommended -->
    <title>Register</title>
</head>
<body>
    <!-- This is the OUTER clipping container -->
    <div>
        <!-- =================== FIX: ADD THIS INNER SCROLLING WRAPPER =================== -->
        <div class="content-scroller">
            <h1 class="mb">Review Registration</h1>

            <div class="progress-bar mb">
                <div class="bar active-bar"></div>
                <div class="bar active-bar"></div>
                <div class="bar active-bar"></div>
                <div class="bar active-bar"></div>
                <div class="bar active-bar"></div>
                <div class="bar active-bar"></div>
            </div>

            <h3>Please review your information before submission:</h3>
            <p class="mb">Choose a specific category to update information.</p>

            <div class="details" onclick="window.location.href='register.php'">
                <p><strong>Last Name:</strong> <?= htmlspecialchars($_SESSION["lname"] ?? "") ?></p>
                <p><strong>First Name:</strong> <?= htmlspecialchars($_SESSION["fname"] ?? "") ?></p>
                <p><strong>Middle Name:</strong> <?= htmlspecialchars($_SESSION["mname"] ?? "") ?></p>
            </div>

            <div class="details" style="cursor: default;"> <!-- Removed full div onclick -->
                <p onclick="window.location.href='register2.php'" style="cursor: pointer;"><strong>Email:</strong> <?= htmlspecialchars($_SESSION["email"] ?? "") ?></p>
                <p onclick="window.location.href='register2.php'" style="cursor: pointer;"><strong>Employee ID:</strong> <?= htmlspecialchars($_SESSION["employeeId"] ?? "") ?></p>
                <p>
                    <strong>Password:</strong>
                    <span id="passwordText"></span>
                    <button type="button" id="togglePassword" class="show-btn">Show</button>
                </p>
            </div>

            <div class="details" onclick="window.location.href='register3.php'">
                <p><strong>College:</strong> <?= htmlspecialchars($_SESSION["college"] ?? "") ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($_SESSION["department"] ?? "") ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="pic-input mb">
                    <label for="idPic">Photo of your ID:</label><br>
                    <input type="file" name="idPic" id="idPic" accept="image/*">
                    <p style="color:red;"><?= $errors["idPic"] ?? "" ?></p>
                </div>
                <div class="sum-btn">
                    <button class="bck-btn" type="button" onclick="location.href='register5.php'">Go Back</button>
                    <input class="nxt-btn" type="submit" value="Submit Registration">
                </div>
            </form>
        </div>
        <!-- ============================ END OF INNER WRAPPER ============================= -->
    </div>

    <script>
        const passwordText = document.getElementById("passwordText");
        const toggleBtn = document.getElementById("togglePassword");
        const originalPassword = "<?= htmlspecialchars($_SESSION["password"] ?? "") ?>";
        let visible = false;

        function updatePasswordDisplay() {
            if (visible) {
                passwordText.textContent = originalPassword;
                toggleBtn.textContent = "Hide";
            } else {
                passwordText.textContent = "•".repeat(originalPassword.length);
                toggleBtn.textContent = "Show";
            }
        }

        toggleBtn.addEventListener("click", () => {
            visible = !visible;
            updatePasswordDisplay();
        });

        // Initial display
        updatePasswordDisplay();
    </script>
</body>
</html>