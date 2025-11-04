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

}
 //$accObj = new Accounts();
// $user = $accObj->fetchAllVerifiedAccounts();
// var_dump($user);
?>
    