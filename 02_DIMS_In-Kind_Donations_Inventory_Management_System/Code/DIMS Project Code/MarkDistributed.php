<?php
session_start();
include 'DonRegConnection.php';

if (isset($_POST['inventory_id'])) {
    $invId = intval($_POST['inventory_id']);
    
    // Get donation_item_id
    $stmt = $object->prepare("SELECT donation_item_id FROM inventory_items WHERE inventory_id=?");
    $stmt->bind_param("i", $invId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $donationItemId = $result['donation_item_id'];
        
        // Update both inventory and donation item status
        $updateInv = $object->prepare("UPDATE inventory_items SET status='Distributed', distributed_date=NOW() WHERE inventory_id=?");
        $updateInv->bind_param("i", $invId);
        $updateInv->execute();
        
        // Update donation item to match
        $updateDonation = $object->prepare("UPDATE donation_items SET itemStatus='Distributed' WHERE item_id=?");
        $updateDonation->bind_param("i", $donationItemId);
        $updateDonation->execute();
        
        echo "OK";
    } else {
        echo "ITEM_NOT_FOUND";
    }
    exit;
}
?>