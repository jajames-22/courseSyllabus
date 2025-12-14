<?php
require_once "../class/syllabus.php";
require_once "../class/accounts.php";
$syllabusObj = new Syllabus(); 
$accountsObj = new Accounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/index.css?v=1">
    <title>WMSU Syllabus Portal</title>
    
    <style>
        html {
            scroll-behavior: smooth;
        }

        /* REMOVED scroll-margin-top (Allowances) per instruction */

        header {
            transition: background-color 0.4s ease;
            /* Ensure header is fixed so the logic works */
            position: fixed; 
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Class for the specific Gray color when on About section */
        header.active-gray {
            background-color: rgb(38, 38, 38) !important;
        }
    </style>
</head>
<body>
    <header>
        <div> 
            <img class="sys-logo" src="../styles/col_logo/19.png" alt="">
        </div>
        
        <div class="nav-btns">
            <ul>
                <li><a href="#home" class="hover-underline-animation center">Home</a></li>
                <li><a href="#published" class="hover-underline-animation center">Published</a></li>
                <li><a href="#features" class="hover-underline-animation center">Features</a></li>
                <li><a href="#about" class="hover-underline-animation center">About</a></li>
            </ul>
        </div>
        <div>
            <button class="login-btn" onclick="location.href='login.php'">Login</button>
            <button class="login-btn" onclick="location.href='register.php'">Register</button>
        </div>
    </header>

    <section id="home" class="home">
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

    <section id="published" class="published">
        <div class="pub-container">
            <h2>WMSU Published Syllabi</h2>
            <?php
                    $searchSyllabus = $_GET['search_syllabus'] ?? '';
                    $filterSyllabusCollege = $_GET['filter_syllabus_college'] ?? '';
                    $sortSyllabusOrder = $_GET['sort_syllabus_order'] ?? 'asc';
                    $currentPage = $_GET['page'] ?? ''; 

                    $publishedSyllabi = $syllabusObj->searchPublishedSyllabus($searchSyllabus, $filterSyllabusCollege, $sortSyllabusOrder);
                    $syllabusColleges = $syllabusObj->getDistinctCollegesForSyllabus();
            ?>

            <div class="page-header">
                <form action="#published" method="GET" class="search-form">
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
                        <button type="button" onclick="window.location.href='?page=<?= htmlspecialchars($currentPage) ?>#published'" class="view-btn" style="background-color: gray;">Reset</button>
                    </div>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>College</th>
                            <th>Department</th>
                            <th>Creator</th>
                            <th></th>
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
                                        <a class="view-btn" href="<?= "{$row['file_dir']}" ?>">View</a>
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
            </div>         
        </div>
    </section>

    <section id="features" class="features">
        <div class="container-features">
            <h2>Features</h2>
                <div class="feature-list">
                    <div class="feature-box">
                        <div class="f-img-con">
                            <img src="../styles/images/feature1.png" alt="">
                        </div>
                       <h3>Online Syllabus Submission & Tracking</h3>
                       <p>Faculty can submit syllabi and monitor approval status in real time.</p>
                    </div>
                    <div class="feature-box">
                        <div class="f-img-con">
                            <img src="../styles/images/feature2.png" alt="">
                        </div>
                        <h3>Multi-Level Approval Workflow</h3>
                        <p>Supports review by Program Chair, Dean, and University offices.</p>
                    </div>
                    <div class="feature-box">
                        <div class="f-img-con">
                            <img src="../styles/images/feature3.png" alt="">
                        </div>
                        <h3>Centralized & Secure Records</h3>
                        <p>Approved syllabi are safely stored and easily accessible anytime.</p>
                    </div>
                </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="about-container">
            <h2>About</h2>
            <div class="site-logo">
                <img src="../styles/col_logo/19.png" alt="">
                <img src="../styles/col_logo/school-logo-name.png" alt="">
            </div>
            <div class="passage">
                <p>
                    The WMSU Syllabus Approval Portal is a web-based system designed to streamline the submission, review, and approval of academic syllabi. It provides a centralized platform where faculty members can easily upload their syllabi, track approval progress, and receive feedback, ensuring a faster and more organized workflow across departments.
                </p>
                <p>
                    This portal was created to promote efficiency, transparency, and accuracy in academic documentation while reducing manual processes. Developed by <strong>James Benedict Rojas</strong>, the system reflects a commitment to improving digital solutions for academic administration at Western Mindanao State University.
                </p>
            </div>

        </div>
    </section>

    <script>
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        const aboutSection = document.getElementById('about');
        
        // Exact height of the header
        const headerHeight = header.offsetHeight;

        if (aboutSection) {
            const aboutRect = aboutSection.getBoundingClientRect();

            // LOGIC: If the top of the About section hits the header (or goes above it)
            // AND the bottom of the About section is still below the header.
            // This logic uses 0 allowances.
            if (aboutRect.top <= headerHeight && aboutRect.bottom >= headerHeight) {
                header.classList.add('active-gray');
            } else {
                header.classList.remove('active-gray');
            }
        }

        // Standard Scroll Logic for other sections (adds 'scrolled' class if not at top)
        if (window.scrollY > 0) { 
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    </script>
</body>
</html>