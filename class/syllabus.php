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

    public function isCourseCodeExist($course_code) {
        $sql = "
            SELECT COUNT(*) 
            FROM syllabus s
            INNER JOIN approval_log al 
                ON s.course_id = al.course_id
            WHERE s.course_code = :course_code
            AND al.action = 'Published'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindParam(':course_code', $course_code, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchColumn() > 0;
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

    public function approvalHistory($sid){
        $sql = "SELECT CONCAT(a.fname, ' ', a.lname) AS interacted,  datetime, level_id, action, comment 
                FROM approval_log al
                JOIN accounts a ON a.acc_id = al.acc_id
                JOIN acc_type at ON at.acc_id = a.acc_id
                JOIN acc_role ar ON ar.role_id = at.role_id
                WHERE al.course_id = :sid
                ORDER BY datetime DESC";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":sid", $sid);
        $query->execute();

        return $query->fetchAll();
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

    public function getPublishedSyllabusPerCollege() {
        $sql = "
            SELECT
                cd.college,
                COUNT(p.course_id) AS published_count
            FROM
                college_dept cd
            LEFT JOIN
                syllabus s ON cd.col_dep_id = s.col_dep_id
            LEFT JOIN
            (
                SELECT DISTINCT course_id
                FROM approval_log
                WHERE action = 'Published'
            ) p ON s.course_id = p.course_id
            WHERE cd.college != 'Office of the Academic Affairs'
            GROUP BY
                cd.college
        ";

        $query = $this->conn->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovalTimes() {
        $sql = "
            SELECT 
                cd.college,
                AVG(TIMESTAMPDIFF(HOUR, sub.datetime, pub.datetime)) AS avg_approval_hours
            FROM college_dept cd
            LEFT JOIN syllabus s ON cd.col_dep_id = s.col_dep_id
            LEFT JOIN (
                SELECT course_id, MIN(datetime) AS datetime
                FROM approval_log
                WHERE action = 'Submitted'
                GROUP BY course_id
            ) sub ON s.course_id = sub.course_id
            LEFT JOIN (
                SELECT course_id, datetime
                FROM approval_log
                WHERE action = 'Published'
            ) pub ON s.course_id = pub.course_id
            WHERE cd.college != 'Office of the Academic Affairs'
            GROUP BY cd.college
            ORDER BY cd.college;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSyllabusStatusPerCollege() {
        $sql = "
            SELECT 
                cd.college,
                alog.action,
                COUNT(DISTINCT alog.course_id) AS total
            FROM college_dept cd
            LEFT JOIN syllabus s ON cd.col_dep_id = s.col_dep_id
            LEFT JOIN (
                SELECT DISTINCT course_id, action
                FROM approval_log
            ) alog ON s.course_id = alog.course_id
            
            WHERE cd.college != 'Office of the Academic Affairs'
            GROUP BY cd.college, alog.action
            ORDER BY cd.college
        ";
        $query = $this->conn->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendNotifications($recipient_acc_id, $sender_acc_id = null, $type, $message, $dir)
    {
        $sql = "
            INSERT INTO notifications 
                (recipient_acc_id, sender_acc_id, type, message, dir)
            VALUES
                (:recipient_acc_id, :sender_acc_id, :type, :message, :dir)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':recipient_acc_id', $recipient_acc_id, PDO::PARAM_INT);
        $stmt->bindParam(':sender_acc_id', $sender_acc_id, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':dir', $dir, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getAllNotifications($acc_id) {
        $sql = "SELECT * FROM notifications WHERE recipient_acc_id = :acc_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteNotification($notif_id, $acc_id){
        $sql = "
            DELETE FROM notifications 
            WHERE notif_id = :notif_id AND recipient_acc_id = :acc_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':notif_id', $notif_id, PDO::PARAM_INT);
        $stmt->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);

        return $stmt->execute();
    }


    public function toggleReadStatus($notif_id, $acc_id){
        // Step 1: Check current status
        $sql = "
            SELECT is_read 
            FROM notifications 
            WHERE notif_id = :notif_id AND recipient_acc_id = :acc_id
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':notif_id', $notif_id, PDO::PARAM_INT);
        $stmt->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        // Toggle
        $newStatus = ($row["is_read"] == 1) ? 0 : 1;

        // Step 2: Update new status
        $sql = "
            UPDATE notifications 
            SET is_read = :new_status 
            WHERE notif_id = :notif_id AND recipient_acc_id = :acc_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
        $stmt->bindParam(':notif_id', $notif_id, PDO::PARAM_INT);
        $stmt->bindParam(':acc_id', $acc_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getSyllabiFromApprovalLog(int $approverAccId): array|false
    {
        // This SQL query joins the necessary tables to get all required information.
        $sql = "
            SELECT
                syl.course_id,
                syl.course_name,
                syl.course_code,
                syl.description,
                author.fname,
                author.lname,
                cd.department,
                cd.college,
                -- We use MAX() with GROUP BY to get the timestamp of the latest action for sorting.
                MAX(al.datetime) as last_action_date
            FROM
                approval_log AS al
            -- Join syllabus details from the syllabus table
            INNER JOIN
                syllabus AS syl ON al.course_id = syl.course_id
            -- Join author (creator) details from the accounts table
            INNER JOIN
                accounts AS author ON syl.acc_id = author.acc_id
            -- Join department/college details
            INNER JOIN
                college_dept AS cd ON syl.col_dep_id = cd.col_dep_id
            WHERE
                -- Condition 1: Find all logs associated with the person viewing the page.
                al.acc_id = :approver_acc_id
                -- Condition 2: Exclude syllabi that this person created themselves.
                AND syl.acc_id != :approver_acc_id
            -- Group by the syllabus ID to ensure each syllabus appears only once.
            GROUP BY
                syl.course_id
            -- Order by the most recent action so the newest items are on top.
            ORDER BY
                last_action_date DESC
        ";

        try {
            $stmt = $this->conn->prepare($sql);

            // Bind the provided account ID to the placeholder in the query.
            $stmt->bindParam(':approver_acc_id', $approverAccId, PDO::PARAM_INT);

            $stmt->execute();
            
            // Fetch all matching rows as an associative array.
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Log the error to the server's error log for debugging.
            error_log("Database Error in getSyllabiFromApprovalLog: " . $e->getMessage());
            return false;
        }
    }

    public function getDistinctCollegesForSyllabus(): array
    {
        // Fetches colleges that actually have a published syllabus
        $sql = "SELECT DISTINCT cd.college 
                FROM syllabus s
                JOIN college_dept cd ON s.col_dep_id = cd.col_dep_id
                JOIN approval_log al ON al.course_id = s.course_id
                WHERE al.action = 'Published'
                ORDER BY cd.college ASC";
                
        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

 
    public function searchPublishedSyllabus(string $searchTerm = '', string $filterCollege = '', string $sortOrder = 'asc'): array
    {
       
        $sql = "SELECT 
                    s.course_id,
                    s.course_code,
                    s.course_name,
                    cd.college,
                    cd.department,
                    CONCAT(a.fname, ' ', a.lname) AS creator_name,
                    s.file_dir
                FROM syllabus s
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN college_dept cd ON s.col_dep_id = cd.col_dep_id
                JOIN approval_log al ON al.course_id = s.course_id
                WHERE 
                    al.action = 'Published'"; // We filter only for published syllabi

        $params = [];

        // Dynamically add search and filter conditions
        if (!empty($searchTerm)) {
            $sql .= " AND (s.course_code LIKE :search_term OR s.course_name LIKE :search_term)";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }

        if (!empty($filterCollege)) {
            $sql .= " AND cd.college = :college";
            $params[':college'] = $filterCollege;
        }
        
        // Add sorting. Whitelist to prevent SQL injection.
        $sortDirection = (strtolower($sortOrder) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY s.course_code $sortDirection";

        $query = $this->connect()->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistinctCollegesForPendingSyllabus(): array
    {
        $sql = "SELECT DISTINCT cd.college 
                FROM syllabus s
                JOIN college_dept cd ON s.col_dep_id = cd.col_dep_id
                JOIN approval_log al ON al.course_id = s.course_id
                WHERE al.level_id = 4 -- Corresponds to 'Pending VP' status
                ORDER BY cd.college ASC";
                
        $query = $this->connect()->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function searchPendingSyllabus(string $searchTerm = '', string $filterCollege = '', string $sortOrder = 'asc'): array
    {
        // Base query to find the latest log entry for each syllabus
        $sql = "SELECT 
                    s.course_id,
                    s.course_code,
                    s.course_name,
                    cd.college,
                    cd.department,
                    CONCAT(a.fname, ' ', a.lname) AS creator_name
                FROM syllabus s
                JOIN accounts a ON s.acc_id = a.acc_id
                JOIN college_dept cd ON s.col_dep_id = cd.col_dep_id
                JOIN approval_log al 
                    ON al.course_id = s.course_id
                    AND al.datetime = (
                        SELECT MAX(al2.datetime)
                        FROM approval_log al2
                        WHERE al2.course_id = s.course_id
                    )
                WHERE al.level_id = 4"; // Base condition: Must be pending VP approval

        $params = [];

        // Dynamically add search and filter conditions
        if (!empty($searchTerm)) {
            $sql .= " AND (s.course_code LIKE :search_term OR s.course_name LIKE :search_term)";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }

        if (!empty($filterCollege)) {
            $sql .= " AND cd.college = :college";
            $params[':college'] = $filterCollege;
        }
        
        // Add sorting. Whitelist to prevent SQL injection.
        $sortDirection = (strtolower($sortOrder) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY s.course_code $sortDirection";

        $query = $this->connect()->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    
}

// $syllabusObj = new syllabus();
// $syllabus = $syllabusObj->fetchSyllabus(39);
// $syllabus = $syllabusObj->getRoleByAccId(39);
// var_dump($syllabus);