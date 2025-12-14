<?php
require_once "../class/accounts.php";
require_once "../class/stmp_config.php";
// Start the session to store the CSRF token and potentially the user's state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$accObj = new Accounts();

// 1. Check for the session variable. This is the gatekeeper.
if (empty($_SESSION['reset_email'])) {
    // If the session variable is empty, redirect them to restart the flow.
    header("Location: forgotPassword.php");
    exit();
}

// 2. Set the email variable for page use from the SECURE session data.
$email = $_SESSION['reset_email']; 
$otp_code = "";
$errors = [];
$success_message = "";

// --- CSRF Token Functions ---

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
    // 1. CSRF Token Validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = "Invalid request token. Please try again.";
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit(); 
    }
    
    // --- FIX: DO NOT get email from POST. It is already securely set from the session. ---
    $otp_code = trim(htmlspecialchars($_POST["otp_code"] ?? ""));

    // Basic Validation
    if (empty($otp_code)) {
        $errors["otp_code"] = "Please enter the 6-digit code.";
    } elseif (!ctype_digit($otp_code) || strlen($otp_code) !== 6) {
        $errors["otp_code"] = "The code must be exactly 6 digits.";
    }

    if (empty($errors)) {
        // 2. OTP Verification using the secure email from the session.
        $verification_result = $accObj->verifyOtpCode($email, (int)$otp_code);

        if ($verification_result) {
            // Success! Store a flag and email in the session to allow password change.
            $_SESSION['can_change_password'] = true;
            $_SESSION['user_reset_email'] = $email;
            
            // --- FIX: Clean up the session state by unsetting the temporary token ---
            unset($_SESSION['reset_email']);
            
            // Redirect to the new password setting page
            header("Location: resetPassword.php");
            exit();
        } else {
            $errors['otp_code'] = "Invalid or expired code. Please try again.";
        }
    }
    
    generateCsrfToken();
} else {
    // On GET request (initial page load), ensure a token is generated
    generateCsrfToken();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/login2.css">
    <title>Verify OTP</title>
</head>
<body>
    <div class="l-login">
        <img style="height: 50px; margin-bottom: 20px;" src="../styles/col_logo/19.png" alt="">
        <h1>WMSU Course Syllabus Approval Portal</h1>
        <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
    </div>
    <div>
        <button class="mb2 back" onclick="location.href='forgotPassword.php'"><- Resend Code</button>
        <h1 class="mb2">Enter Verification Code</h1>
        
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <!-- NOTE: This hidden email field is no longer used by the server-side logic, but does no harm. -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <p class="mb2">A 6-digit code has been sent to <strong><?= htmlspecialchars($email) ?></strong>.</p>
            <p class="mb2">Please check your inbox and spam folder.</p>
            
            <label class="mb2" for="otp_code">Verification Code:</label><br>
            <input type="text" id="otp_code" name="otp_code" value="<?= htmlspecialchars($otp_code) ?>" maxlength="6" pattern="\d{6}" required autofocus><br>
            
            <p style="color:red;" class="mb2"><?= $errors['otp_code'] ?? ''?></p>

            <button type="submit" class="login-btn mb1">Verify Code</button>
            
            <p style="color:red;"><?= $errors['general'] ?? ''; ?></p>
        </form>
    </div>
</body>
</html>