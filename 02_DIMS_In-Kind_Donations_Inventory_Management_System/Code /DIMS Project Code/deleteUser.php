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

$target = (int)$_POST['user_id'];
$me = (int)$_SESSION['user_id'];

//Cannot delete yourself
if ($target === $me) {
    header("Location: AdManageUser.php");
    exit;
}

//Check role of target
$res = $object->query("SELECT role FROM users WHERE user_id = $target");
if (!$res || $res->num_rows === 0) {
    header("Location: AdManageUser.php");
    exit;
}
$row = $res->fetch_assoc();
$role = $row['role'];

//If target is Admin, ensure at least one other Admin remains
if ($role === 'Admin') {
    $r = $object->query("SELECT COUNT(*) as count FROM users WHERE role='Admin'");
    $count = (int)$r->fetch_assoc()['count'];
    if ($count <= 1) {
        header("Location: AdManageUser.php");
        exit;
    }
}

//Perform deletion
$stmt = $object->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $target);
$stmt->execute();

header("Location: AdManageUser.php");
exit;