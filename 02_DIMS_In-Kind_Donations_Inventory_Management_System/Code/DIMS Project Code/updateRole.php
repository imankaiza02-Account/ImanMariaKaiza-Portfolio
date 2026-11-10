<?php
session_start();
include "DonRegConnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die("Forbidden");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id']) || !isset($_POST['new_role'])) {
    header("Location: AdManageUser.php");
    exit;
}

$user_id = (int)$_POST['user_id'];
$new_role = $_POST['new_role'] === 'Admin' ? 'Admin' : 'Staff';
$me = (int)$_SESSION['user_id'];

//Cannot change your own role
if ($user_id === $me) {
    header("Location: AdManageUser.php");
    exit;
}

//If demoting an Admin to Staff cant remove the last Admin
if ($new_role !== 'Admin') {
    $r = $object->query("SELECT COUNT(*) as count FROM users WHERE role='Admin'");
    $count = (int)$r->fetch_assoc()['count'];
    //check if target user is currently admin
    $cur = $object->query("SELECT role FROM users WHERE user_id = $user_id")->fetch_assoc();
    if ($cur && $cur['role'] === 'Admin' && $count <= 1) {
        header("Location: AdManageUser.php");
        exit;
    }
}

//Perform update
$now = date("Y-m-d");
$adminId = (int)$_SESSION['user_id'];
$stmt = $object->prepare("UPDATE users SET role=?, roleModifiedByAdminID=?, roleModificationDate=? WHERE user_id=?");
$stmt->bind_param("sisi", $new_role, $adminId, $now, $user_id);
//Binding to the users table attributes: role (s), roleModifiedByAdminID (i), roleModificationDate (s), user_id (i)
$stmt = $object->prepare("UPDATE users SET role=?, roleModifiedByAdminID=?, roleModificationDate=? WHERE user_id=?");
$stmt->bind_param("sisi", $new_role, $adminId, $now, $user_id);
$stmt->execute();

header("Location: AdManageUser.php");
exit;
