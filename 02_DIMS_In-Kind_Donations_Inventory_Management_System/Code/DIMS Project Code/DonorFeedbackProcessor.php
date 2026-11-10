<?php
session_start();
include "DonRegConnection.php";

// Prevent direct access without form submission
if (!isset($_POST['submitFeedback']) && !isset($_POST['rating'])) {
    header("Location: DonorMainPage.php");
    exit;
}

$donor_id = (int)$_POST['donor_id'];
$rating   = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
$message  = trim($_POST['message']);

// Validate required fields
if ($donor_id <= 0 || !$rating || empty($message)) {
    header("Location: DonorMainPage.php?error=invalid_input");
    exit;
}

// Prepared insert statement
$stmt = $object->prepare("
    INSERT INTO donor_feedback (donor_id, rating, message, created_at)
    VALUES (?, ?, ?, NOW())
");

if (!$stmt) {
    die("Prepare failed: " . $object->error);
}

$stmt->bind_param("iis", $donor_id, $rating, $message);

if ($stmt->execute()) {
    header("Location: DonorMainPage.php?feedback=success");
    exit;
} else {
    die("Error saving feedback: " . $stmt->error);
}
?>
