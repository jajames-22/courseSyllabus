<?php

require_once "database.php";

class Syllabus extends Database {
    public $course_id = "";
    public $course_code = "";
    public $acc_id = "";
    public $col_dep_id = "";
    public $course_name = "";
    public $prerequisite = "";
    public $credit = "";
    public $with_lab = "";
    public $description = "";
    public $date_created = "";
    public $role_id = "";
    public $effective_date = "";
    public $file_name = "";
    public $file_dir = "";
    public $action = "";
    public $comment = "";

    protected $db;

    public function __construct(){
        $this->db = $this->connect();
    }

    public function addSyllabus(){
        $sql = "INSERT INTO syllabus (course_code, acc_id, col_dep_id, course_name, prerequisite, credit, with_lab, description, date_created, effective_date, file_name, file_dir) VALUES (:course_code, :acc_id, :col_dep_id, :course_name, :prerequisite, :credit, :with_lab, :description, :date_created, :effective_date, :file_name, :file_dir)";

        $query = $this->db->prepare($sql);

        $query->bindParam(":course_code", $this->course_code);
        $query->bindParam(":acc_id", $this->acc_id);
        $query->bindParam(":col_dep_id", $this->col_dep_id);
        $query->bindParam(":course_name", $this->course_name);
        $query->bindParam(":prerequisite", $this->prerequisite);
        $query->bindParam(":credit", $this->credit);
        $query->bindParam(":with_lab", $this->with_lab);
        $query->bindParam(":description", $this->description);
        $query->bindParam(":date_created", $this->date_created);
        $query->bindParam(":effective_date", $this->effective_date);
        $query->bindParam(":file_name", $this->file_name);
        $query->bindParam(":file_dir", $this->file_dir);
        
        $query->execute();

        $course_id = $this->db->lastInsertId();

        $sql2 = "INSERT INTO approval_log (acc_id, course_id, datetime, level_id, action, comment) VALUES (:acc_id, :course_id, :datetime, :level_id, :action, :comment)";

        $query2 = $this->db->prepare($sql2);

        $query2->bindParam(":acc_id", $this->acc_id);
        $query2->bindParam(":course_id", $course_id);
        $query2->bindParam(":datetime", $this->date_created);
        $query2->bindParam(":level_id",$this->role_id);
        $query2->bindParam(":action",$this->action);
        $query2->bindParam(":comment",$this->comment);

        return $query2->execute();
    }

    public function viewAllMySyllabusById($acc_id) {
        $sql = "SELECT s.*, 
                    al.acc_id AS interacted_by, 
                    al.datetime AS approved_datetime, 
                    al.level_id AS approval_level, 
                    al.comment AS latest_comment
                FROM syllabus s
                LEFT JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.log_id = (
                        SELECT MAX(log_id)
                        FROM approval_log
                        WHERE course_id = s.course_id
                    )
                WHERE s.acc_id = :acc_id
                ORDER BY s.date_created DESC";

        $query = $this->db->prepare($sql);
        $query->bindParam(":acc_id", $acc_id);
        $query->execute();

        return $query->fetchAll();
    }

    public function fetchSyllabus($sid) {
        $sql = "SELECT 
                    s.*, 
                    a.fname, 
                    a.lname, 
                    a.email,
                    cd.college,
                    cd.department,
                    al.comment AS latest_comment,
                    al.datetime AS latest_datetime,
                    al.level_id,
                    al.acc_id AS latest_acc_id,
                    CONCAT(a2.fname, ' ', a2.lname) AS latest_acc_name
                FROM syllabus s 
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON a.acc_id = at.acc_id
                JOIN college_dept cd ON at.col_dep_id = cd.col_dep_id
                LEFT JOIN (
                    SELECT al1.course_id, al1.comment, al1.datetime, al1.level_id, al1.acc_id
                    FROM approval_log al1
                    WHERE al1.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = al1.course_id
                    )
                ) AS al ON al.course_id = s.course_id
                LEFT JOIN accounts a2 ON al.acc_id = a2.acc_id
                WHERE s.course_id = :sid
                LIMIT 1";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":sid", $sid);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }



    public function updateSyllabus($course_id) {
        $sql = "UPDATE syllabus 
                SET course_code = :course_code,
                    course_name = :course_name,
                    prerequisite = :prerequisite,
                    credit = :credit,
                    with_lab = :with_lab,
                    description = :description,
                    date_created = :date_created,
                    effective_date = :effective_date,
                    file_name = :file_name,
                    file_dir = :file_dir
                WHERE course_id = :course_id";

        $query = $this->connect()->prepare($sql);

        $query->bindParam(":course_code", $this->course_code);
        $query->bindParam(":course_name", $this->course_name);
        $query->bindParam(":prerequisite", $this->prerequisite);
        $query->bindParam(":credit", $this->credit);
        $query->bindParam(":with_lab", $this->with_lab);
        $query->bindParam(":description", $this->description);
        $query->bindParam(":date_created", $this->date_created);
        $query->bindParam(":effective_date", $this->effective_date);
        $query->bindParam(":file_name", $this->file_name);
        $query->bindParam(":file_dir", $this->file_dir);
        $query->bindParam(":course_id", $course_id);

        if ($query->execute()) {
            $sql2 = "INSERT INTO approval_log (acc_id, course_id, datetime, level_id, comment)
                    VALUES (:acc_id, :course_id, :datetime, :level_id, :comment)";
            $query2 = $this->connect()->prepare($sql2);
            $query2->bindParam(":acc_id", $this->acc_id);
            $query2->bindParam(":course_id", $course_id);
            $query2->bindParam(":datetime", $this->date_created);
            $query2->bindParam(":level_id", $this->role_id);
            $query2->bindParam(":comment", $this->comment);
            return $query2->execute();
        }
        return false;
    }

    public function getPendingforHead($department, $role_id){
        $sql = "SELECT 
                s.*, 
                al.*, 
                a.*, 
                cd.department,
                al.acc_id AS interacted_by, 
                al.datetime AS latest_date, 
                al.level_id AS approval_level, 
                al.comment AS latest_comment
                FROM syllabus s
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
                WHERE cd.department = :department 
                AND al.level_id = :role_id
                ORDER BY al.datetime DESC;";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":department", $department);
        $query->bindParam(":role_id", $role_id);
        $query->execute();

        return $query->fetchAll();
    }

    public function getPendingforDean($college, $role_id){
        $sql = "SELECT s.*, al.*, a.*, cd.department
            FROM syllabus s
            JOIN (
                SELECT al1.*
                FROM approval_log al1
                INNER JOIN (
                    SELECT course_id, MAX(datetime) AS latest_datetime
                    FROM approval_log
                    GROUP BY course_id
                ) al2 ON al1.course_id = al2.course_id AND al1.datetime = al2.latest_datetime
            ) al ON al.course_id = s.course_id
            JOIN accounts a ON s.acc_id = a.acc_id
            JOIN acc_type at ON at.acc_id = a.acc_id
            JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
            WHERE cd.college = :college AND al.level_id = :role_id
            ORDER BY al.datetime DESC";
        
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":college", $college);
        $query->bindParam(":role_id", $role_id);
        $query->execute();
        
        return $query->fetchAll();
    }

    public function getRecentLog($sid){
        $sql = "SELECT * FROM approval_log 
            WHERE course_id = :sid 
            ORDER BY datetime DESC 
            LIMIT 1";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":sid", $sid);
        $query->execute();

        return $query->fetch();
    }

    public function approveSyllabus($acc_id, $course_id){
        $sql="INSERT INTO approval_log (acc_id, course_id, datetime, level_id, action, comment) VALUES (:acc_id, :course_id, :datetime, :level_id, :action, :comment)";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":acc_id", $acc_id);
        $query->bindParam(":course_id", $course_id);
        $query->bindParam(":datetime", $this->date_created);
        $query->bindParam(":level_id", $this->level_id);
        $query->bindParam(":action", $this->action);
        $query->bindParam(":comment", $this->comment);
        
        return $query->execute();
    }

    public function getRoleIdByAccId($acc_id){
        $sql = "SELECT at.role_id FROM accounts a JOIN acc_type at ON a.acc_id = at.acc_id WHERE a.acc_id = :acc_id LIMIT 1";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":acc_id", $acc_id);
        $query->execute();

        $result = $query->fetch();
        return $result ? $result["role_id"] : null;
    }

    public function fetchAllPublishedSyllabus(){
        $sql = "SELECT 
                    s.course_id,
                    s.course_code,
                    s.course_name,
                    cd.college,
                    cd.department,
                    al.level_id,
                    CONCAT(a.fname, ' ', a.lname) AS creator_name
                FROM syllabus s
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                WHERE al.level_id = 5
                ORDER BY s.course_code ASC";

        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllPendingSyllabus(){
        $sql = "SELECT 
                    s.course_id,
                    s.course_code,
                    s.course_name,
                    cd.college,
                    cd.department,
                    al.level_id,
                    CONCAT(a.fname, ' ', a.lname) AS creator_name
                FROM syllabus s
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                WHERE al.level_id = 4
                ORDER BY s.course_code ASC";

        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    public function deleteSyllabus($course_id) {
        try {
            $conn = $this->connect();
            $conn->beginTransaction();

            // Delete related approval logs first
            $sqlApproval = "DELETE FROM approval_log WHERE course_id = ?";
            $stmtApproval = $conn->prepare($sqlApproval);
            $stmtApproval->execute([$course_id]);

            // Delete the syllabus itself
            $sqlSyllabus = "DELETE FROM syllabus WHERE course_id = ?";
            $stmtSyllabus = $conn->prepare($sqlSyllabus);
            $stmtSyllabus->execute([$course_id]);

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            return false;
        }
    }

    public function countSyllabusByAccId($acc_id) {
        $sql = "SELECT COUNT(*) AS total FROM syllabus WHERE acc_id = :acc_id";
        $query = $this->db->prepare($sql);
        $query->bindParam(":acc_id", $acc_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countMySyllabusLevel5($acc_id) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM syllabus s
            LEFT JOIN approval_log al 
                ON al.course_id = s.course_id
                AND al.log_id = (
                    SELECT MAX(log_id)
                    FROM approval_log
                    WHERE course_id = s.course_id
                )
            WHERE s.acc_id = :acc_id
            AND al.level_id = 5
        ";

        $query = $this->db->prepare($sql);
        $query->bindParam(":acc_id", $acc_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countReturn($department, $role_id, $acc_id) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM syllabus s
            JOIN approval_log al 
                ON al.course_id = s.course_id
                AND al.datetime = (
                    SELECT MAX(al2.datetime)
                    FROM approval_log al2
                    WHERE al2.course_id = s.course_id
                )
            JOIN accounts a ON s.acc_id = a.acc_id
            JOIN acc_type at ON at.acc_id = a.acc_id
            JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
            WHERE cd.department = :department 
            AND al.level_id = :role_id
            AND s.acc_id = :acc_id
        ";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":department", $department);
        $query->bindParam(":role_id", $role_id);
        $query->bindParam(":acc_id", $acc_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function countPendingforHead($department, $role_id){
        $sql = "SELECT COUNT(*) AS numrows
                FROM syllabus s
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
                WHERE cd.department = :department 
                AND al.level_id = :role_id;";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":department", $department);
        $query->bindParam(":role_id", $role_id);
        $query->execute();

        return $query->fetchColumn(); 
    }

    public function countPendingforDean($college, $role_id){
        $sql = "SELECT COUNT(*) AS numrows
                FROM syllabus s
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN college_dept cd ON cd.col_dep_id = at.col_dep_id
                WHERE cd.college = :college 
                AND al.level_id = :role_id;";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":college", $college);
        $query->bindParam(":role_id", $role_id);
        $query->execute();

        return $query->fetchColumn(); 
    }

    public function countSyllabusLevel5(){
        $sql = "SELECT COUNT(*) AS numrows
                FROM approval_log al
                JOIN syllabus s ON s.course_id = al.course_id
                WHERE al.level_id = 5;";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchColumn(); // returns the count only
    }

    public function countSyllabusLevel4(){
        $sql = "SELECT COUNT(*) AS numrows
                FROM syllabus s
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                WHERE al.level_id = 4;";

        $query = $this->connect()->prepare($sql);
        $query->execute();

        return $query->fetchColumn(); // returns the count
    }

    

}

// $syllabusObj = new syllabus();
// $syllabus = $syllabusObj->fetchSyllabus(39);
// $syllabus = $syllabusObj->getRoleByAccId(39);
// var_dump($syllabus);