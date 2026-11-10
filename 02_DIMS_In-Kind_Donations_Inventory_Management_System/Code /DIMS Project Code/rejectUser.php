<?php
session_start();
include "DonRegConnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die("Forbidden");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    header("Location: AdManageUser.php");
    exit;
}

$user_id = (int)$_POST['user_id'];
$admin_id = (int)$_SESSION['user_id'];

$stmt = $object->prepare("UPDATE users SET approvalStatus='Rejected', validatedByAdminID=? WHERE user_id=?");
$stmt->bind_param("ii", $admin_id, $user_id);
$stmt->execute();

header("Location: AdManageUser.php");
exit;