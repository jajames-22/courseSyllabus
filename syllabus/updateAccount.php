<?php
session_start();
require_once "../class/accounts.php"; // Adjust path
$accObj = new Accounts();

// --- AUTHENTICATION ---
if (!isset($_SESSION['acc_id'])) {
    header("Location: login.php");
    exit();
}
$acc_id_to_edit = $_SESSION['acc_id'];

// --- Get flash data from previous request ---
$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';
$form_data = $_SESSION['form_data'] ?? []; // For repopulating the form on error
unset($_SESSION['errors'], $_SESSION['success_message'], $_SESSION['form_data']);


// --- HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accounts = [];
    $errors = []; // Start with a fresh error array

    // 1. GATHER AND SANITIZE DATA
    $accounts["acc_id"] = $acc_id_to_edit;
    $accounts["fname"] = trim(htmlspecialchars($_POST["fname"] ?? ""));
    $accounts["mname"] = trim(htmlspecialchars($_POST["mname"] ?? ""));
    $accounts["lname"] = trim(htmlspecialchars($_POST["lname"] ?? ""));
    $accounts["email"] = trim(htmlspecialchars($_POST["email"] ?? ""));
    $accounts["col_dep_id"] = $_POST["col_dep_id"] ?? "";
    $accounts["role_id"] = $_POST["role_id"] ?? "";

    $accounts["college"] = $_POST["college"] ?? "";

    // 2. FIELD-BY-FIELD VALIDATION
    if (empty($accounts["fname"])) {
        $errors['fname'] = "First Name cannot be empty";
    }
    if (empty($accounts["lname"])) {
        $errors['lname'] = "Last Name cannot be empty";
    }
    if (empty($accounts["email"])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($accounts['email'], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format.";
    } elseif ($accObj->isEmailTakenByOther($accounts['email'], $acc_id_to_edit)) {
        $errors["email"] = "This email is already in use by another account.";
    }
    if (empty($accounts["col_dep_id"])) {
        $errors["col_dep_id"] = "Department is required";
    }
    if (empty($accounts["role_id"])) {
        $errors["role_id"] = "Role is required";
    }

    // 3. BUSINESS RULE VALIDATION (Dean & Dept Head)
    // Only perform these complex checks if the basic validation has passed
    if (empty($errors)) {
        // Role ID 2 = Department Head
        if ($accounts['role_id'] == 2) {
            $existing_holder_id = $accObj->isDeptHeadExist($accounts['col_dep_id']);
            // If the position is filled AND it's not by the user making the change
            if ($existing_holder_id && $existing_holder_id != $acc_id_to_edit) {
                $errors['role_id'] = "This Department Head position is already filled.";
            }
        }
        // Role ID 3 = Dean
        elseif ($accounts['role_id'] == 3) {
            if (!empty($accounts['college'])) {
                $existing_holder_id = $accObj->isDeanExist($accounts['college']);
                // If the position is filled AND it's not by the user making the change
                if ($existing_holder_id && $existing_holder_id != $acc_id_to_edit) {
                    $errors['role_id'] = "The Dean position for this college is already filled.";
                }
            } else {
                // This would be an unusual error, but is a good safeguard
                $errors['col_dep_id'] = "A valid college must be selected to assign a Dean.";
            }
        }
    }

    // 4. PROCESS OR REDIRECT WITH ERRORS
    if (empty(array_filter($errors))) {
        // If there are no errors, attempt to update the profile
        try {
            if ($accObj->updateUserProfile($accounts)) {
                $_SESSION['success_message'] = "Profile updated successfully!";
            }
        } catch (Exception $e) {
            // Catch errors from the backend (like database issues)
            $_SESSION['errors']['general'] = $e->getMessage();
        }
    } else {
        // If there are validation errors, store them in the session to show on the next page load
        $_SESSION['errors'] = $errors;
        // Also store the submitted data so the form can be repopulated
        $_SESSION['form_data'] = $accounts;
    }
    
    // Always redirect back to the form page to prevent resubmission
    header("Location: register7.php");
    exit();
}

$accountDetails = $accObj->fetchAccountDetails($acc_id_to_edit);
if (!$accountDetails) die("Account not found.");


if (!empty($form_data)) {
    $accountDetails['fname'] = $form_data['fname'] ?? $accountDetails['fname'];
    $accountDetails['mname'] = $form_data['mname'] ?? $accountDetails['mname'];
    $accountDetails['lname'] = $form_data['lname'] ?? $accountDetails['lname'];
    $accountDetails['email'] = $form_data['email'] ?? $accountDetails['email'];
    $accountDetails['college'] = $form_data['college'] ?? $accountDetails['college'];
    $accountDetails['col_dep_id'] = $form_data['col_dep_id'] ?? $accountDetails['col_dep_id'];
    $accountDetails['role_id'] = $form_data['role_id'] ?? $accountDetails['role_id'];
}

$allRoles = $accObj->getAllRoles();
$allCollegesAndDepts = $accObj->getAllCollegesAndDepartments();
$structuredData = [];
foreach ($allCollegesAndDepts as $item) {
    $structuredData[$item['college']][] = ['id' => $item['col_dep_id'], 'name' => $item['department']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/review.css?v=7">
    <link rel="stylesheet" href="../styles/modalsKpi.css?v=2">
    <title>Update Profile</title>
</head>
<body>
    <main class="content-wrapper">
        <div>
            <div class="content-scroller">
                <h1>Update Your Profile</h1>
                <p>You can change your account details here. Note that every change you make will move your account to pending status for change confirmation.</p>
                <br><hr><br>
                
                <?php if ($success_message): ?><p class="message success mb"><?= $success_message ?></p><?php endif; ?>
                <?php if (!empty($errors['general'])): ?><p class="message error mb"><?= $errors['general'] ?></p><?php endif; ?>

                <form method="POST" id="profileForm">
                    <div class="details">
                        <p><strong>Personal Information</strong></p>
                        <div class="input-group">
                            <label for="fname">First Name:</label>
                            <input type="text" name="fname" value="<?= htmlspecialchars($accountDetails['fname']) ?>" required>
                            <p style="color: red;"><?= $errors['fname'] ?? "" ?></p>
                        </div>
                        <div class="input-group">
                            <label for="mname">Middle Name:</label>
                            <input type="text" name="mname" value="<?= htmlspecialchars($accountDetails['mname']) ?>">
                        </div>
                        <div class="input-group">
                            <label for="lname">Last Name:</label>
                            <input type="text" name="lname" value="<?= htmlspecialchars($accountDetails['lname']) ?>" required>
                            <p style="color: red;"><?= $errors['lname'] ?? "" ?></p>
                        </div>
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($accountDetails['email']) ?>" required>
                            <p style="color: red;"><?= $errors['email'] ?? "" ?></p>
                        </div>
                    </div>
                    <div class="details">
                        <p><strong>Your Role & Department</strong></p>
                        <div class="input-group">
                            <label for="college">College:</label>
                            <select name="college" id="college" required>
                                <option value="">-- Select College --</option>
                                <?php foreach (array_keys($structuredData) as $college): if($college!='Office of the Academic Affairs') { ?>
                                    <option value="<?= htmlspecialchars($college) ?>" <?= ($accountDetails['college'] === $college) ? 'selected' : '' ?>><?= htmlspecialchars($college) ?></option>
                                <?php } endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="col_dep_id">Department:</label>
                            <select name="col_dep_id" id="col_dep_id" required><option value="">-- Select College First --</option></select>
                            <p style="color: red;"><?= $errors['col_dep_id'] ?? "" ?></p>
                        </div>
                        <div class="input-group">
                            <label for="role_id">Role:</label>
                            <select name="role_id" id="role_id" required>
                                <option value="">-- Select Role --</option>
                                <?php foreach ($allRoles as $role): if($role['role_name']!='Published'){ ?>
                                    <option value="<?= $role['role_id'] ?>" <?= ($accountDetails['role_id'] == $role['role_id']) ? 'selected' : '' ?>><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php } endforeach; ?>
                            </select>
                            <p style="color: red;"><?= $errors['role_id'] ?? "" ?></p>
                        </div>
                    </div>
                    <div class="sum-btn">
                        <button type="button" class="bck-btn" onclick="history.back()">Go Back</button>
                        <button class="bck-btn" type="button" onclick="window.location.href='forgotPassword.php'">Update Password</button>
                        <button class="nxt-btn" type="button" id="saveChangesBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="modal-bg" id="confirmModal">
        <div class="modal">
            <h2>Confirm Changes</h2>
            <p>Are you sure you want to save these changes to your profile? Remember that your account will be <span style="color: red;">MOVED to PENDING</span></p>
            <div class="con-button">
                <button type="button" id="cancelBtn">Cancel</button>
                <button type="button" id="submitNow">Save</button>
            </div>
        </div>
    </div>

    <script>
        // Your JavaScript is correct and remains the same
        const collegeSelect = document.getElementById('college');
        const departmentSelect = document.getElementById('col_dep_id');
        const collegeData = <?= json_encode($structuredData ?? []) ?>;
        const currentDepartmentId = <?= (int)$accountDetails['col_dep_id'] ?>;
        function updateDepartments() {
            const selectedCollege = collegeSelect.value;
            departmentSelect.innerHTML = '<option value="">-- Select a Department --</option>';
            if (selectedCollege && collegeData[selectedCollege]) {
                collegeData[selectedCollege].forEach(department => {
                    const option = document.createElement('option');
                    option.value = department.id;
                    option.textContent = department.name;
                    departmentSelect.appendChild(option);
                });
                if (selectedCollege === "<?= htmlspecialchars($accountDetails['college']) ?>" || collegeData[selectedCollege].some(d => d.id == currentDepartmentId)) {
                    departmentSelect.value = currentDepartmentId;
                }
            }
        }
        collegeSelect.addEventListener('change', updateDepartments);
        document.addEventListener('DOMContentLoaded', updateDepartments);

        const saveChangesBtn = document.getElementById('saveChangesBtn');
        const confirmModal = document.getElementById('confirmModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const submitBtn = document.getElementById('submitNow');
        saveChangesBtn.addEventListener('click', () => { confirmModal.classList.add('active'); });
        cancelBtn.addEventListener('click', () => { confirmModal.classList.remove('active'); });
        submitBtn.addEventListener('click', () => { document.getElementById('profileForm').submit(); });
        confirmModal.addEventListener('click', (event) => { if (event.target === confirmModal) { confirmModal.classList.remove('active'); } });
    </script>
    <style>
    /* Helper Classes */
    .message { padding: 1rem; border-radius: 5px; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
    .details label { font-weight: bold; margin-top: 10px; }
    .details input, .details select { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
    
    /* Body now only handles the background */
    body {
        background: linear-gradient(135deg, rgba(217, 0, 0, 0.8), rgba(65, 0, 0, 0.8)), url("../styles/images/bg-img.jpg");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* NEW: This wrapper now handles centering */
    .content-wrapper {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100vh;
    }

    /* MODIFIED: This now targets the panel inside the wrapper */
    .content-wrapper > div {
        margin: 20px;
        background-color: white;
        border-radius: 10px;
        max-height: 95vh;
        width: 500px;
        max-width: 95vw;
        overflow: hidden;
        padding: 0;
        display: flex;
        flex-direction: column;
    }

    /* Modal Styles (Unchanged but will now work correctly) */
    .modal-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none; 
    }
    .modal-bg.active {
        display: flex;
    }
    .modal {
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        width: 90%;
        max-width: 450px;
    }
    .modal h2 { margin-bottom: 1rem; }
    .modal p { margin-bottom: 1.5rem; line-height: 1.5; }
    .con-button { display: flex; justify-content: center; gap: 1rem; }
    .con-button button { padding: 10px 25px; border: none; border-radius: 50px; cursor: pointer; font-weight: bold; transition: all 0.2s ease; }
    #cancelBtn { background-color: #f0f0f0; border: 1px solid #ccc; }
    #cancelBtn:hover { background-color: #e0e0e0; }
    #submitNow { background-color: var(--main-color); color: white; }
    #submitNow:hover { background-color: #ce0000; }
</style>
</body>
</html>
    
