<?php

require_once "database.php";

class Accounts extends Database {
    public $acc_id = "";
    public $lname = "";
    public $fname = "";
    public $mname = "";
    public $email = "";
    public $employeeId = "";
    public $password = "";
    public $role_id = "";
    public $col_dep_id = "";
    public $pic_name = "";
    public $pic_dir = "";

    protected $db;

    public function __construct() {
        $this->db = $this->connect();
    }

    public function register() {
        $sql1 = "INSERT INTO accounts (lname, fname, mname, email, employee_id, password, pic_name, pic_dir)
                 VALUES (:lname, :fname, :mname, :email, :employee_id, :password, :pic_name, :pic_dir)";
        $query1 = $this->db->prepare($sql1);

        $query1->bindParam(':lname', $this->lname);
        $query1->bindParam(':fname', $this->fname);
        $query1->bindParam(':mname', $this->mname);
        $query1->bindParam(':email', $this->email);
        $query1->bindParam(':employee_id', $this->employeeId);
        $query1->bindParam(':password', $this->password);
        $query1->bindParam(':pic_name', $this->pic_name);
        $query1->bindParam(':pic_dir', $this->pic_dir);

        $query1->execute();

        $acc_id = $this->db->lastInsertId();

        $sql2 = "INSERT INTO acc_type (acc_id, role_id, col_dep_id)
                 VALUES (:acc_id, :role_id, :col_dep_id)";
        $query2 = $this->db->prepare($sql2);

        $query2->bindParam(':acc_id', $acc_id);
        $query2->bindParam(':role_id', $this->role_id);
        $query2->bindParam(':col_dep_id', $this->col_dep_id);

        return $query2->execute();
    }

    public function isEmailExist($email) {
        $sql = "SELECT * FROM accounts WHERE email = :email";
        $query = $this->db->prepare($sql);
        $query->bindParam(':email', $email);
        $query->execute();

        return $query->rowCount() > 0;
    }

    public function isEmployeeIdExist($employeeId) {
        $sql = "SELECT * FROM accounts WHERE employee_id = :employee_id AND isVerified = 1";
        $query = $this->db->prepare($sql);
        $query->bindParam(':employee_id', $employeeId);
        $query->execute();

        return $query->rowCount() > 0;
    }

    public function getColleges() {
        $sql = "SELECT DISTINCT college FROM college_dept ORDER BY college ASC";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function getDepartmentsByCollege($college) {
        $sql = "SELECT col_dep_id, department FROM college_dept WHERE college = :college";
        $query = $this->db->prepare($sql);
        $query->bindParam(":college", $college);
        $query->execute();
        return $query->fetchAll();
    }

    public function getColDepId($college, $department) {
        $sql = "SELECT col_dep_id FROM college_dept WHERE college = :college AND department = :department";
        $query = $this->db->prepare($sql);
        $query->bindParam(":college", $college);
        $query->bindParam(":department", $department);
        $query->execute();
        return $query->fetch();
    }

    public function getRoles() {
        $sql = "SELECT role_id, role_name FROM acc_role LIMIT 2";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function isDeptHeadExist($col_dep_id) {
        $sql = "SELECT * FROM acc_type WHERE role_id = 2 AND col_dep_id = :col_dep_id";
        $query = $this->db->prepare($sql);
        $query->bindParam(':col_dep_id', $col_dep_id);
        $query->execute();
        return $query->rowCount() > 0;
    }

    public function isDeanExist($college) {
        $sql = "SELECT at.* FROM acc_type at
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE at.role_id = 3 AND cd.college = :college";
        $query = $this->db->prepare($sql);
        $query->bindParam(':college', $college);
        $query->execute();

        return $query->rowCount() > 0;
    }

    public function login($email, $password) {
        $sql = "SELECT a.acc_id, a.lname, a.fname, a.mname, a.email, a.isVerified, at.col_dep_id, ar.role_name, ar.role_id, cd.college, cd.department
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN acc_role ar ON at.role_id = ar.role_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.email = :email AND a.password = :password";
        $query = $this->db->prepare($sql);
        $query->bindParam(':email', $email);
        $query->bindParam(':password', $password);
        $query->execute();

        return $query->fetch();
    }

    public function fetchAllVerifiedAccounts() {
        $sql = "SELECT 
                    a.acc_id,
                    a.employee_id,
                    a.fname,
                    a.lname,
                    a.email,
                    at.role_id,
                    r.role_name,
                    cd.college,
                    cd.department
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN acc_role r ON at.role_id = r.role_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.isVerified = 1 AND at.role_id != 4
                ORDER BY cd.college, cd.department, a.lname ASC";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function fetchAllPendingAccounts() {
        $sql = "SELECT 
                    a.acc_id,
                    a.employee_id,
                    a.fname,
                    a.lname,
                    a.email,
                    a.pic_dir,
                    at.role_id,
                    r.role_name,
                    cd.college,
                    cd.department
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN acc_role r ON at.role_id = r.role_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.isVerified = 0 AND at.role_id != 4
                ORDER BY cd.college, cd.department, a.lname ASC";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function fetchAccountById($id) {
    $sql = "SELECT a.*, r.role_name, cd.college, cd.department
            FROM accounts a
            JOIN acc_type at ON at.acc_id = a.acc_id
            JOIN acc_role r ON at.role_id = r.role_id
            JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
            WHERE a.acc_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    public function verifyAccount($id) {
        $sql = "UPDATE accounts SET isVerified = 1 WHERE acc_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function deleteAccount($id) {
        try {
            // Begin transaction to ensure both deletions succeed or fail together
            $this->conn->beginTransaction();

            // Delete from acc_type first
            $sql1 = "DELETE FROM acc_type WHERE acc_id = ?";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([$id]);

            // Then delete from accounts
            $sql2 = "DELETE FROM accounts WHERE acc_id = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([$id]);

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback if any error occurs
            $this->conn->rollBack();
            return false;
        }
    }

    public function moveAccountToPending($id) {
        $sql = "UPDATE accounts SET isVerified = 0 WHERE acc_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function countVerifiedAccounts(){
        $sql = "SELECT COUNT(*) AS numrows
                FROM accounts
                WHERE isVerified = 1;";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchColumn(); // returns the count
    }

    public function countUnverifiedAccounts(){
        $sql = "SELECT COUNT(*) AS numrows
                FROM accounts
                WHERE isVerified = 0;";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchColumn(); // returns the count
    }

    public function getAccountInfoByRole($col_dep_id, $role_id) {
        $sql = "
            SELECT 
                a.acc_id,
                a.email,
                CONCAT(a.fname, ' ', a.lname ) AS full_name
            FROM acc_type at
            INNER JOIN accounts a ON at.acc_id = a.acc_id
            WHERE at.col_dep_id = :col_dep_id
            AND at.role_id = :role_id
            LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindParam(':col_dep_id', $col_dep_id, PDO::PARAM_INT);
        $query->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC); 
    }

    public function getDeanOfCollege($college){
        $sql = "
            SELECT 
                a.acc_id,
                CONCAT(a.fname, ' ', a.lname ) AS full_name,
                a.email
            FROM accounts a
            INNER JOIN acc_type at ON a.acc_id = at.acc_id
            INNER JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
            WHERE cd.college = :college
            AND cd.department = 'Dean'
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':college', $college, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setOtpCode(string $email, int $code): bool {
        
        // Define the expiration time (10 minutes from now)
        $expiration_time_expr = "DATE_ADD(NOW(), INTERVAL 10 MINUTE)"; 

        try {
            // 1. Find the Account ID (acc_id)
            $sql_id = "SELECT acc_id FROM accounts WHERE email = :email LIMIT 1";
            $stmt_id = $this->conn->prepare($sql_id);
            $stmt_id->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_id->execute();
            $user = $stmt_id->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Account not found
                return false; 
            }
            $acc_id = $user['acc_id'];

            // 2. Clear any existing pending reset tokens for this account
            $sql_delete = "DELETE FROM password_resets WHERE acc_id = :acc_id";
            $stmt_delete = $this->conn->prepare($sql_delete);
            $stmt_delete->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);
            $stmt_delete->execute();
            
            // 3. Insert the new OTP code and expiration time
            $sql_insert = "
                INSERT INTO password_resets (acc_id, code, created_at, expires_at)
                VALUES (:acc_id, :code, NOW(), $expiration_time_expr)
            ";
            
            $stmt_insert = $this->conn->prepare($sql_insert);
            $stmt_insert->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':code', $code, PDO::PARAM_INT);

            return $stmt_insert->execute();

        } catch (PDOException $e) {
            // Log the error
            error_log("Database Error in setOtpCode: " . $e->getMessage());
            return false;
        }
    }

    public function verifyOtpCode(string $email, int $otp_code): bool {
        
        try {
            // 1. Find the Account ID (acc_id)
            $sql_id = "SELECT acc_id FROM accounts WHERE email = :email LIMIT 1";
            $stmt_id = $this->conn->prepare($sql_id);
            $stmt_id->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_id->execute();
            $user = $stmt_id->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Account not found (shouldn't happen in this flow)
                return false; 
            }
            $acc_id = $user['acc_id'];

            // 2. Check the code, expiration, and acc_id in password_resets
            $sql_check = "
                SELECT 
                    reset_id 
                FROM 
                    password_resets 
                WHERE 
                    acc_id = :acc_id 
                    AND code = :code 
                    AND expires_at > NOW() 
                LIMIT 1
            ";
            
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);
            $stmt_check->bindParam(':code', $otp_code, PDO::PARAM_INT);
            $stmt_check->execute();
            $reset_record = $stmt_check->fetch(PDO::FETCH_ASSOC);

            // If no matching, unexpired record is found, verification fails.
            if (!$reset_record) {
                return false;
            }

            // 3. Success: Delete the used OTP record to prevent replay attacks and reuse
            $sql_delete = "DELETE FROM password_resets WHERE reset_id = :reset_id";
            $stmt_delete = $this->conn->prepare($sql_delete);
            $stmt_delete->bindParam(':reset_id', $reset_record['reset_id'], PDO::PARAM_INT);
            $stmt_delete->execute();
            
            return true; // Verification successful
            
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Error in verifyOtpCode: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword(string $email, string $newPassword): bool 
    {
        try {
      
            $sql = "UPDATE accounts SET password = :password WHERE email = :email";
            
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':password', $newPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Database Error in updatePassword: " . $e->getMessage());
            return false;
        }
    }

    public function getDistinctColleges(): array
    {
        $sql = "SELECT DISTINCT college FROM college_dept ORDER BY college ASC";
        $query = $this->connect()->prepare($sql);
        $query->execute();
        // PDO::FETCH_COLUMN fetches only the first column of each row
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }


    public function searchVerifiedAccounts(string $searchName = '', string $filterCollege = '', string $sortOrder = 'asc'): array
    {
        // Base SQL query
        $sql = "SELECT 
                    a.acc_id,
                    a.employee_id,
                    a.fname,
                    a.lname,
                    a.email,
                    r.role_name,
                    cd.college,
                    cd.department
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN acc_role r ON at.role_id = r.role_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.isVerified = 1 AND at.role_id < 3";

        $params = [];

        // Dynamically add search conditions
        if (!empty($searchName)) {
            // Search in the concatenated full name
            $sql .= " AND CONCAT(a.fname, ' ', a.lname) LIKE :search_name";
            $params[':search_name'] = '%' . $searchName . '%';
        }

        if (!empty($filterCollege)) {
            $sql .= " AND cd.college = :college";
            $params[':college'] = $filterCollege;
        }
        
        $sortDirection = (strtolower($sortOrder) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY a.lname $sortDirection, a.fname $sortDirection";

        $query = $this->connect()->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    public function getDistinctCollegesForPendingAccounts(): array
    {
        $sql = "SELECT DISTINCT cd.college 
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.isVerified = 0 AND at.role_id != 4
                ORDER BY cd.college ASC";
                
        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function searchPendingAccounts(string $searchName = '', string $filterCollege = '', string $sortOrder = 'asc'): array
    {
        // Base SQL query
        $sql = "SELECT 
                    a.acc_id,
                    a.employee_id,
                    a.fname,
                    a.lname,
                    a.email,
                    r.role_name,
                    cd.college,
                    cd.department
                FROM accounts a
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN acc_role r ON at.role_id = r.role_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE a.isVerified = 0 AND at.role_id < 3"; // Base condition for pending accounts

        $params = [];

        // Dynamically add search and filter conditions
        if (!empty($searchName)) {
            $sql .= " AND CONCAT(a.fname, ' ', a.lname) LIKE :search_name";
            $params[':search_name'] = '%' . $searchName . '%';
        }

        if (!empty($filterCollege)) {
            $sql .= " AND cd.college = :college";
            $params[':college'] = $filterCollege;
        }
        
        // Add sorting. Whitelist to prevent SQL injection.
        $sortDirection = (strtolower($sortOrder) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY a.lname $sortDirection, a.fname $sortDirection";

        $query = $this->connect()->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    public function getAllRoles(): array
    {
        $sql = "SELECT role_id, role_name FROM acc_role WHERE role_id != 4 ORDER BY role_name"; // Exclude VP
        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all colleges and their corresponding departments.
     * @return array
     */
    public function getAllCollegesAndDepartments(): array
    {
        $sql = "SELECT col_dep_id, college, department FROM college_dept ORDER BY college, department";
        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing user's account, including role and department.
     * Throws an exception if a unique role (Dean/Head) is already filled.
     *
     * @param array $data Associative array of user data.
     * @return bool True on success.
     * @throws Exception If a business rule is violated (e.g., position filled).
     */
    public function updateAccount(array $data): bool
    {
        // --- Step 1: Check for unique role constraints BEFORE starting the transaction ---
        $role_id = (int)$data['role_id'];
        $col_dep_id = (int)$data['col_dep_id'];
        $acc_id = (int)$data['acc_id'];

        // Role IDs: 2 = Department Head, 3 = Dean
        if ($role_id === 2 || $role_id === 3) {
            $sql_check = "SELECT acc_id FROM acc_type WHERE role_id = :role_id AND col_dep_id = :col_dep_id LIMIT 1";
            $stmt_check = $this->connect()->prepare($sql_check);
            $stmt_check->execute([':role_id' => $role_id, ':col_dep_id' => $col_dep_id]);
            $existing_user = $stmt_check->fetch(PDO::FETCH_ASSOC);

            // If a user exists in that role and it's not the user we are currently editing
            if ($existing_user && (int)$existing_user['acc_id'] !== $acc_id) {
                $role_name = ($role_id === 2) ? "Department Head" : "Dean";
                // Throw an exception that we can catch in the front-end
                throw new Exception("This $role_name position is already filled. Please assign a different role.");
            }
        }

        // --- Step 2: Proceed with the update inside a transaction ---
        $this->connect()->beginTransaction();
        try {
            // Update the 'accounts' table
            $sql_accounts = "UPDATE accounts SET fname = :fname, mname = :mname, lname = :lname, email = :email WHERE acc_id = :acc_id";
            $stmt_accounts = $this->connect()->prepare($sql_accounts);
            $stmt_accounts->execute([
                ':fname' => $data['fname'],
                ':mname' => $data['mname'],
                ':lname' => $data['lname'],
                ':email' => $data['email'],
                ':acc_id' => $acc_id
            ]);

            // Update the 'acc_type' table
            $sql_acc_type = "UPDATE acc_type SET role_id = :role_id, col_dep_id = :col_dep_id WHERE acc_id = :acc_id";
            $stmt_acc_type = $this->connect()->prepare($sql_acc_type);
            $stmt_acc_type->execute([
                ':role_id' => $role_id,
                ':col_dep_id' => $col_dep_id,
                ':acc_id' => $acc_id
            ]);

            // If everything is successful, commit the changes
            $this->connect()->commit();
            return true;

        } catch (PDOException $e) {
            // If a database error occurs, roll back all changes
            $this->connect()->rollBack();
            error_log("Account update failed: " . $e->getMessage());
            throw new Exception("A database error occurred during the update."); // Re-throw generic error
        }
    }

    public function updateSelfAccount(array $data): bool
    {
        try {
            $sql = "UPDATE accounts 
                    SET fname = :fname, mname = :mname, lname = :lname, email = :email";

            // Conditionally add password if a new one is provided
            if (!empty($data['password'])) {
                $sql .= ", password = :password";
            }
            // Conditionally add picture if a new one is provided
            if (!empty($data['pic_dir'])) {
                $sql .= ", pic_name = :pic_name, pic_dir = :pic_dir";
            }

            $sql .= " WHERE acc_id = :acc_id";

            $stmt = $this->connect()->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':fname', $data['fname']);
            $stmt->bindParam(':mname', $data['mname']);
            $stmt->bindParam(':lname', $data['lname']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':acc_id', $data['acc_id'], PDO::PARAM_INT);

            if (!empty($data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $hashedPassword);
            }
            if (!empty($data['pic_dir'])) {
                $stmt->bindParam(':pic_name', $data['pic_name']);
                $stmt->bindParam(':pic_dir', $data['pic_dir']);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Self account update failed: " . $e->getMessage());
            return false;
        }
    }

    public function fetchAccountDetails(int $acc_id)
    {
        // The SQL query joins four tables to collect all necessary data.
        $sql = "SELECT 
                    a.acc_id, 
                    a.fname, 
                    a.mname, 
                    a.lname, 
                    a.email, 
                    a.employee_id, 
                    a.pic_dir,
                    at.role_id, 
                    at.col_dep_id,
                    r.role_name,
                    cd.college, 
                    cd.department
                FROM 
                    accounts a
                INNER JOIN 
                    acc_type at ON a.acc_id = at.acc_id
                INNER JOIN 
                    acc_role r ON at.role_id = r.role_id
                INNER JOIN 
                    college_dept cd ON at.col_dep_id = cd.col_dep_id
                WHERE 
                    a.acc_id = :acc_id
                LIMIT 1";

        try {
            // Prepare the statement to prevent SQL injection.
            $query = $this->connect()->prepare($sql);
            
            // Bind the account ID parameter and execute the query.
            $query->execute([':acc_id' => $acc_id]);
            
            // Fetch the single row of data as an associative array.
            // fetch() returns false if no rows are found.
            return $query->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Log the error for debugging purposes.
            error_log("Error fetching account details: " . $e->getMessage());
            return false;
        }
    }

    public function isEmailTakenByOther(string $email, int $current_acc_id): bool
    {
        $sql = "SELECT acc_id FROM accounts WHERE email = :email AND acc_id != :acc_id LIMIT 1";

        try {
            // Prepare the statement to prevent SQL injection.
            $query = $this->connect()->prepare($sql);
            
            // Bind the parameters and execute the query.
            $query->execute([
                ':email' => $email,
                ':acc_id' => $current_acc_id
            ]);

            // rowCount() will be > 0 if a matching record was found.
            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            // Log the error for debugging and return false to be safe.
            error_log("Error in isEmailTakenByOther: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserProfile(array $data): bool
    {
        $acc_id = (int)$data['acc_id'];
        $new_role_id = (int)$data['role_id'];
        $new_col_dep_id = (int)$data['col_dep_id'];

        // --- Step 1: Business Rule Validation ---
        // This happens before we even start the database transaction.

        // Role ID 2 = Department Head
        if ($new_role_id === 2) {
            $current_holder_id = $this->isDeptHeadExist($new_col_dep_id);
            // If the position is filled AND it's not by the user we are currently editing
            if ($current_holder_id && $current_holder_id !== $acc_id) {
                throw new Exception("This Department Head position is already filled. Please choose a different role or department.");
            }
        }
        // Role ID 3 = Dean
        elseif ($new_role_id === 3) {
            // To check for an existing Dean, we first need the college name for the selected department.
            $sql_college = "SELECT college FROM college_dept WHERE col_dep_id = :col_dep_id LIMIT 1";
            $stmt_college = $this->connect()->prepare($sql_college);
            $stmt_college->execute([':col_dep_id' => $new_col_dep_id]);
            $college_info = $stmt_college->fetch(PDO::FETCH_ASSOC);
            
            if (!$college_info) {
                throw new Exception("Invalid department selected. Cannot verify Dean position.");
            }

            $current_holder_id = $this->isDeanExist($college_info['college']);
            // If the position is filled AND it's not by the user we are currently editing
            if ($current_holder_id && $current_holder_id !== $acc_id) {
                throw new Exception("The Dean position for this college is already filled. Please choose a different role.");
            }
        }

        // --- Step 2: Proceed with the Database Update inside a Transaction ---
        
        // Get the single, persistent database connection object ONCE.
        $db = $this->connect();

        try {
            // Start the transaction on our persistent connection object.
            $db->beginTransaction();

            // Query 1: Update personal info in the 'accounts' table.
            $sql_accounts = "UPDATE accounts SET fname = :fname, mname = :mname, lname = :lname, email = :email, isVerified = 0 WHERE acc_id = :acc_id";
            $stmt_accounts = $db->prepare($sql_accounts);
            $stmt_accounts->execute([
                ':fname' => $data['fname'],
                ':mname' => $data['mname'],
                ':lname' => $data['lname'],
                ':email' => $data['email'],
                ':acc_id' => $acc_id
            ]);

            // Query 2: Update role and department info in the 'acc_type' table.
            $sql_acc_type = "UPDATE acc_type SET role_id = :role_id, col_dep_id = :col_dep_id WHERE acc_id = :acc_id";
            $stmt_acc_type = $db->prepare($sql_acc_type);
            $stmt_acc_type->execute([
                ':role_id' => $new_role_id,
                ':col_dep_id' => $new_col_dep_id,
                ':acc_id' => $acc_id
            ]);

            // If both queries executed without error, commit the changes to the database.
            $db->commit();
            return true;

        } catch (PDOException $e) {
            // If any database error occurred, check if a transaction is active and roll it back.
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            // Log the detailed error for the developer.
            error_log("Profile update transaction failed: " . $e->getMessage());
            // Throw a generic, user-friendly error back to the form.
            throw new Exception("A database error occurred. Could not update the profile.");
        }
    }

    

}
 //$accObj = new Accounts();
// $user = $accObj->fetchAllVerifiedAccounts();
// var_dump($user);
?>
    