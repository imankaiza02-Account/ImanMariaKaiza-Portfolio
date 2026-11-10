<?php
session_start();
include "DonRegConnection.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    die("Access denied");
}

if (!isset($_POST['feedback_id']) || !isset($_POST['reply'])) {
    die("Invalid request");
}

$fid = (int)$_POST['feedback_id'];
$reply = trim($_POST['reply']);
$staffId = (int)$_SESSION['user_id'];
$now = date("Y-m-d H:i:s");

$stmt = $object->prepare("
    UPDATE donor_feedback
    SET staff_reply=?, staff_reply_by=?, staff_reply_date=?
    WHERE feedback_id=?
");
$stmt->bind_param("sisi", $reply, $staffId, $now, $fid);
$stmt->execute();

echo "OK";