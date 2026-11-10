<?php
session_start();
include "DonRegConnection.php";
//Staff must be logged in to generate QR codes 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    die("Access denied");
}
if (!isset($_POST['inventory_id'])) {
    die("Invalid request");
}
$invId = (int)$_POST['inventory_id'];

//Fetch item info
$stmt = $object->prepare("
    SELECT item_name, quantity, description 
    FROM inventory_items 
    WHERE inventory_id = ?
");
$stmt->bind_param("i", $invId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) die("Item not found");

//Create QR folder if missing (absolute path)
$qrDir = __DIR__ . "/qr_codes/";
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0777, true);
}

//Include QR library
require __DIR__ . "/phpqrcode/qrlib.php";

//This code will get the IP address of the device automatically
$serverIP = gethostbyname(gethostname());

//URL contained inside the QR code
$qrData = "http://{$serverIP}/DIMS_FINAL/System/QRView.php?id=" . $invId;

//Save PNG file
$qrFile = $qrDir . "inv_" . $invId . ".png";

//Generate QR image file
QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 5);

//Save relative path to DB for <img src="qr_codes/...">
$relativePath = "qr_codes/inv_" . $invId . ".png";
$update = $object->prepare("
    UPDATE inventory_items 
    SET qr_code_path=? 
    WHERE inventory_id=?
");
$update->bind_param("si", $relativePath, $invId);
$update->execute();

echo "QR_GENERATED";
?>