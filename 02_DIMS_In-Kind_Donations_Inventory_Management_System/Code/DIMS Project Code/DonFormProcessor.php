<?php
session_start();
header("Content-Type: application/json");
include "DonRegConnection.php";

//Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$donor_id = (int)$_SESSION['user_id'];

//Validate POST data
if (!isset($_POST['itemName']) || !isset($_POST['quantity'])) {
    echo json_encode(["success" => false, "message" => "Invalid form submission."]);
    exit;
}

$itemNames = $_POST['itemName'];
$quantities = $_POST['quantity'];
$descriptions = $_POST['description'] ?? [];

if (count($itemNames) === 0) {
    echo json_encode(["success" => false, "message" => "No items received."]);
    exit;
}

//Guarantee description array length matches items
for ($i = 0; $i < count($itemNames); $i++) {
    if (!isset($descriptions[$i])) {
        $descriptions[$i] = "";
    }
}

//Validate names + quantities
for ($i = 0; $i < count($itemNames); $i++) {
    $name = trim($itemNames[$i]);
    $qty = (int)$quantities[$i];

    if ($name === "" || $qty <= 0) {
        echo json_encode(["success" => false, "message" => "All items must have a name and quantity > 0."]);
        exit;
    }
}

//Start transaction
$object->begin_transaction();

try {
    // Insert donation record (NO status column anymore)
    $totalItems = count($itemNames);

    $stmt = $object->prepare("
        INSERT INTO donations (donor_id, total_items)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $donor_id, $totalItems);
    $stmt->execute();

    $donation_id = $stmt->insert_id;

    // Insert each donation item
    // Default SQL itemStatus = 'To be received'
    $itemQuery = $object->prepare("
        INSERT INTO donation_items (donation_id, item_name, quantity, description)
        VALUES (?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($itemNames); $i++) {
        $name = trim($itemNames[$i]);
        $qty = (int)$quantities[$i];
        $desc = trim($descriptions[$i]);

        $itemQuery->bind_param("isis", $donation_id, $name, $qty, $desc);
        $itemQuery->execute();
    }

    // Commit transaction
    $object->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {

    // Rollback on failure
    $object->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>