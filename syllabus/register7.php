<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root{
            --main-color: #930000;
            --secondary-color: #dc2121;
            --white-color: #ffff;
        }

        @font-face {
            font-family: 'Inter';
            src: url('../styles/InterReg.ttf');
        }

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Inter;
            color: white;
        }

        body{
            background: linear-gradient(to right, var(--main-color), var(--secondary-color));
            display: flex;
            justify-content: center;
        }

        .container{
            width: 600px;
            margin-top: 40px;
            text-align: left;
        }

        .mb{
            margin-bottom: 10px;
        }

        button{
            border: none;
            color: var(--main-color);
            padding: 10px 20px;
            border-radius: 100px;
            background-color: var(--white-color);
            cursor: pointer;
        }

        a{
            margin-left: 10px;
            color: var(--white-color);
            text-decoration: underline;
            cursor: pointer;
        }

        .details{
            width: 600px;
            margin-top: 20px;
        }
    </style>
    <title>WMSU Syllabus Portal</title>
</head>
<body>
    <div class="container">
        <h1 class="mb">Your registration has been processed</h1>
        <p class="mb">Please wait for the admin to verify your account.</p>
        <button onclick="location.href='index.php'">Go back to Homepage</button>
        <a href="?learnmore=true">Learn More</a>

        <?php if (isset($_GET['learnmore'])): ?>
        <div class="details">
            <p>
                Thank you for registering with the <strong>WMSU Syllabus Approval Portal</strong>.<br><br>

                Your registration has been successfully received and is now <strong>pending verification</strong>.<br><br>

                Verification is an essential step to ensure the <strong>security and integrity</strong> of our system. This process helps us confirm that only authorized users can access and manage academic syllabi, protecting both institutional data and user accounts from unauthorized access or misuse.<br><br>

                We also value your <strong>privacy</strong>. All personal information you provide is handled in accordance with our <strong>Privacy Policy</strong>, which ensures that your data will only be used for legitimate academic and administrative purposes within the portal. No information will be shared with third parties without your explicit consent.<br><br>

                If your account has not yet been verified after a reasonable period, it may be due to one of the following reasons:<br><br>
                - Your registration could not be confirmed by your adviser or administrator.<br>
                - Your account was flagged for incomplete or inaccurate details during verification.<br>
                - The account may have been automatically deleted due to verification failure or inactivity.<br><br>

                If you believe your account has been mistakenly unverified or deleted, or if you encounter any other issues, please contact the <strong>system administrator</strong> immediately for assistance.<br><br>

                Thank you for your understanding and cooperation as we maintain a secure and reliable environment for all users.<br><br>
            </p>
        </div>
        <?php endif; 
        session_unset();?>
    </div>
</body>
</html>
