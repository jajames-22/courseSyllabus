<?php
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailerInstance() {
    $mail = new PHPMailer(true); // Enable exceptions

    // --- Server Settings ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Example for Gmail. Change for your provider.
    $mail->SMTPAuth   = true;
    $mail->Username   = 'wmsucoursesyllabus@gmail.com'; // Your SMTP username (your full email)
    $mail->Password   = 'morljmahdsllzraz'; // IMPORTANT: Use a Gmail "App Password", not your regular password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- Sender Information ---
    // This is the "From" address that recipients will see
    $mail->setFrom('no-reply@yourdomain.com', 'Course Syllabus System');

    return $mail;
}

/**
 * A reusable function to send notifications.
 *
 * @param PHPMailer $mailer An instance of PHPMailer from getMailerInstance().
 * @param string $recipientEmail The email address of the recipient.
 * @param string $subject The subject of the email.
 * @param string $body The HTML or text body of the email.
 * @return bool True on success, false on failure.
 */
function sendNotificationEmail(PHPMailer $mailer, $recipientEmail, $subject, $body) {
    try {
        // Recipient
        $mailer->addAddress($recipientEmail);

        // Content
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body    = $body;
        $mailer->AltBody = strip_tags($body); // A plain text version for non-HTML email clients

        $mailer->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging. Never show this to the user.
        error_log("Message could not be sent. Mailer Error: {$mailer->ErrorInfo}");
        return false;
    }
}