<?php
session_start();
require_once "../class/syllabus.php";
$syllabusObj = new Syllabus();

$action = $_POST["action"];
$notif_id = intval($_POST["notif_id"]);
$acc_id = $_SESSION["acc_id"];

if ($action === "delete") {
    $syllabusObj->deleteNotification($notif_id, $acc_id);
}
elseif ($action === "toggle_read") {
    $syllabusObj->toggleReadStatus($notif_id, $acc_id);
}

echo "OK";
