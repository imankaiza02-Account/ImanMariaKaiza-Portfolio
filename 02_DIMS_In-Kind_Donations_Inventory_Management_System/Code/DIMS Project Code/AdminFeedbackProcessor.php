<?php
session_start();
header('Content-Type: application/json');
include "DonRegConnection.php";

//Basic checks
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['ok' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

if (!isset($_POST['feedback_id']) || !isset($_POST['reply'])) {
    echo json_encode(['ok' => false, 'error' => 'Missing parameters']);
    exit;
}

$fid = (int)$_POST['feedback_id'];
$reply = trim($_POST['reply']);
$adminId = (int)$_SESSION['user_id'];

//Basic validation
if ($fid <= 0 || $reply === '') {
    echo json_encode(['ok' => false, 'error' => 'Invalid feedback ID or empty reply']);
    exit;
}

//Check if feedback exists
$chk = $object->prepare("SELECT feedback_id FROM donor_feedback WHERE feedback_id = ?");
$chk->bind_param("i", $fid);
$chk->execute();
$res = $chk->get_result();
$row = $res->fetch_assoc();
$chk->close();

if (!$row) {
    echo json_encode(['ok' => false, 'error' => 'Feedback not found']);
    exit;
}

//Update admin reply (allows both new replies and editing existing ones)
$now = date("Y-m-d H:i:s");
$upd = $object->prepare("
    UPDATE donor_feedback
    SET admin_reply = ?, admin_reply_by = ?, admin_reply_date = ?
    WHERE feedback_id = ?
");
$upd->bind_param("sisi", $reply, $adminId, $now, $fid);

if ($upd->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Database error']);
}
$upd->close();
exit;