<?php
require_once "../class/accounts.php";
require_once "../class/stmp_config.php";
// Start the session to store the CSRF token and potentially the user's state
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$accObj = new Accounts();
$email = "";
$errors = [];
// --- REMOVED: Unnecessary success message variable ---

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
        // Log the potential attack and terminate the request
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit(); 
    }

    $mailer = getMailerInstance();
    // Validate and sanitize the email
    $email = trim(htmlspecialchars($_POST["email"] ?? ""));

    // Basic Email Validation
    if (empty($email)) {
        $errors["email"] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format.";
    }

    // Check if the email exists, but prevent enumeration
    $user_exists = false;
    if (empty($errors)) {
        $user_exists = $accObj->isEmailExist($email);
    }
    
    // Process only if the email is valid (regardless of existence for enumeration prevention)
    if (empty($errors)) {
        // --- NOTE: The generic message is now implicitly handled by the redirect ---
        
        if ($user_exists) {
            // 2. Generate and Store OTP
            $code = random_int(100000, 999999);
            $accObj->setOtpCode($email, $code); 

            // 3. Email Content and Destination
            $subject = "Your Password Reset Code (Don't Share)";
            $body = "
                <p>Hello,</p>
                <p>You requested a password reset for your WMSU Syllabus Approval Portal account.</p>
                <p>Your one-time password (OTP) is:</p>
                <h2 style='color: #007bff;'>$code</h2>
                <p>This code will expire shortly. Please do not share it with anyone.</p>
                <p>If you did not request a password reset, you can safely ignore this email.</p>
            ";
            
            sendNotificationEmail($mailer, $email, $subject, $body);
            
            // Set the session variable BEFORE redirecting
            $_SESSION['reset_email'] = $email;
            
            // Redirect to the OTP entry page
            header("Location: enterOtp.php");
            exit(); 
        }
        // If the user doesn't exist, we do nothing, preventing enumeration.
        // The page will simply reload, clearing the form.
    }
    
    // Regenerate the CSRF token for the next request/page load
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
    <title>Forgot Password</title>
</head>
<body>
    <div class="l-login">
        <img style="height: 50px; margin-bottom: 20px;" src="../styles/col_logo/19.png" alt="">
        <h1>WMSU Course Syllabus Approval Portal</h1>
        <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
    </div>
    <div>
        <button class="mb2 back" onclick="location.href='login.php'"><- Go back</button>
        <h1 class="mb2">Forgot or Change Password</h1>
        
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <!-- IMPROVEMENT: Simplified user instruction -->
            <p class="mb2">Enter your account's email address and we will send you a password reset code.</p>
            
            <!-- REMOVED: The success message block which was never displayed -->
            
            <label class="mb2" for="email">Email:</label><br>
            <input type="text" id="email" name="email" value="<?= htmlspecialchars($email) ?>"><br>
            
            <p style="color:red;" class="mb2"><?= $errors['email'] ?? ''?></p>

            <button type="submit" class="login-btn mb1">Send Code</button>
            
            <p style="color:red;"><?= $errors['general'] ?? ''; ?></p>
        </form>
    </div>
</body>
</html>