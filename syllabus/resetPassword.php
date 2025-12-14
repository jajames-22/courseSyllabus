<?php
require_once "../class/accounts.php";
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$accObj = new Accounts();
$new_password = "";
$confirm_password = "";
$errors = [];
$success_message = "";

// 1. Authorization Check: Ensure the user successfully verified the OTP
// The 'can_change_password' flag must be set by enterOtp.php
if (empty($_SESSION['can_change_password']) || empty($_SESSION['user_reset_email'])) {
    // If not authorized, redirect them back to the start of the process
    header("Location: forgotPassword.php");
    exit();
}

$email = $_SESSION['user_reset_email']; // Get the authorized email

// --- CSRF Token Functions (Copied for consistency) ---

/**
 * Generates and stores a CSRF token in the session.
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the submitted CSRF token against the session token.
 */
function validateCsrfToken($token) {
    return !empty($token) && $token === $_SESSION['csrf_token'];
}

// --- Request Handling ---

if($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. CSRF Token Validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = "Invalid request token. Please try again.";
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit(); 
    }

    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // 3. Validation
    if (empty($new_password)) {
        $errors["new_password"] = "New password is required.";
    } elseif (strlen($new_password) < 8) {
        $errors["new_password"] = "Password must be at least 8 characters long.";
    }

    if ($new_password !== $confirm_password) {
        $errors["confirm_password"] = "Passwords do not match.";
    }

    if (empty(array_filter($errors))) {
        // 4. Securely Update Password (Function must be implemented in accounts.php)
        // It will hash the password and update the 'accounts' table based on the email.
        
        // ⚠️ ASSUMING $accObj->updatePassword($email, $new_password) is implemented
        $update_successful = $accObj->updatePassword($email, $new_password);

        if ($update_successful) {
            // 5. Cleanup and Redirect on Success
            $success_message = "Your password has been successfully reset! You can now log in.";
            
            // Crucial: Destroy the reset tokens/flags from the session
            unset($_SESSION['can_change_password']);
            unset($_SESSION['user_reset_email']);
            // Optionally redirect to the login page after a delay or link click
            header("refresh:5;url=login.php"); 
        } else {
            $errors['general'] = "Failed to update password. Please try again.";
        }
    }
    
    generateCsrfToken();
} else {
    generateCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/login2.css">
    <title>Set New Password</title>
</head>
<body>
    <div class="l-login">
        <img style="height: 50px; margin-bottom: 20px;" src="../styles/col_logo/19.png" alt="">
        <h1>WMSU Course Syllabus Approval Portal</h1>
        <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
    </div>
    <div>
        <button class="mb2 back" onclick="location.href='login.php'"><- Go to Login</button>
        <h1 class="mb2">Set New Password</h1>
        
        <?php if (!empty($success_message)): ?>
            <p style="color:green; font-weight: bold;" class="mb2"><?= $success_message ?></p>
            <p class="mb2">You will be automatically redirected to the login page shortly.</p>
        <?php else: ?>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <p class="mb2">Please enter and confirm your new password for **<?= htmlspecialchars($email) ?>**.</p>
                
                <label class="mb2" for="new_password">New Password:</label><br>
                <input type="password" id="new_password" name="new_password" value=""><br>
                <p style="color:red;" class="mb2"><?= $errors['new_password'] ?? ''?></p>

                <label class="mb2" for="confirm_password">Confirm Password:</label><br>
                <input type="password" id="confirm_password" name="confirm_password" value=""><br>
                <p style="color:red;" class="mb2"><?= $errors['confirm_password'] ?? ''?></p>

                <button type="submit" class="login-btn mb1">Change Password</button>
                
                <p style="color:red;"><?= $errors['general'] ?? ''; ?></p>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>