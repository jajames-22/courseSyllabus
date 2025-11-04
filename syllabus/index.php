<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css">
    <title>WMSU Syllabus Portal</title>
</head>
<body>
    <header>
        <div>
            <img class="sys-logo" src="../styles/col_logo/19.png" alt="">
        </div>
        
        <div class="nav-btns">
            <ul>
                <li ><a href="" class="hover-underline-animation center">Home</a></li>
                <li ><a href="" class="hover-underline-animation center">Published</a></li>
                <li ><a href="" class="hover-underline-animation center">Features</a></li>
                <li ><a href="" class="hover-underline-animation center">About</a></li>
            </ul>
        </div>
        <div>
            <button class="login-btn" onclick="location.href='login.php'">Login</button>
            <button class="login-btn" onclick="location.href='register.php'">Register</button>
        </div>
    </header>
    <section class="home">
        <div class="home-container">
            <p>WELCOME TO</p>
            <h1>WMSU Course Syllabus Approval Portal</h1>
            <p class="mt">Your streamlined platform for reviewing, submitting, and approving course syllabi with ease and transparency.</p>
            
            <div class="col-logos">
                <img src="../styles/col_logo/2.png" alt="">
                <img src="../styles/col_logo/11.png" alt="">
                <img src="../styles/col_logo/3.png" alt="">
                <img src="../styles/col_logo/4.png" alt="">
                <img src="../styles/col_logo/5.png" alt="">
                <img src="../styles/col_logo/6.png" alt="">
                <img src="../styles/col_logo/7.png" alt="">
                <img src="../styles/col_logo/8.png" alt="">
                <img src="../styles/col_logo/9.png" alt="">
                <img src="../styles/col_logo/10.png" alt="">
                <img src="../styles/col_logo/12.png" alt="">
                <img src="../styles/col_logo/13.png" alt="">
                <img src="../styles/col_logo/14.png" alt="">
                <img src="../styles/col_logo/15.png" alt="">
                <img src="../styles/col_logo/20.png" alt="">
                <img src="../styles/col_logo/21.png" alt="">
            </div>

            <div class="get-started">
                <p>Log In or Register to get started</p>
                <button class="home-btn" onclick="location.href='login.php'">Login</button>
                <button class="home-btn" onclick="location.href='register.php'">Register</button>
            </div>

        </div>
    </section>
    <section class="published">
        <div class="pub-container">
            <h2>WMSU Published Syllabi</h2>
        </div>
    </section>


    <script>
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 50) { // Adjust scroll threshold as needed (50px here)
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
</script>
</body>
</html>