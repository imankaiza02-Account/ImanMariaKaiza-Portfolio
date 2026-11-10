<?php
include "DonRegConnection.php";
if (!isset($_GET['id'])) {
    die("Invalid QR Code");
}
$invId = (int)$_GET['id'];

//Fetch full item details
$stmt = $object->prepare("SELECT * FROM inventory_items WHERE inventory_id=? LIMIT 1");
$stmt->bind_param("i", $invId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Item not found in inventory.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory Item Details</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: Poppins, sans-serif;
    background: #f5f6fa;
    padding: 30px;
}

.container {
    max-width: 450px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

h2 {
    text-align: center;
    font-weight: 600;
}

.info-box p {
    font-size: 16px;
    margin: 10px 0;
}

.label {
    font-weight: 600;
}

.btn {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    margin-top: 15px;
    cursor: pointer;
    font-weight: 600;
}

.btn-distribute {
    background: #28a745;
    color: white;
}

.btn-distribute:disabled {
    background: #bbb;
    cursor: not-allowed;
}

.success, .error {
    margin-top: 20px;
    padding: 12px;
    display: none;
}

.success {
    background: #d4edda;
    border-left: 4px solid #28a745;
}

.error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}
</style>
</head>
<body>
<div class="container">
    <h2>Inventory Item</h2>
    <div class="info-box">
        <p><span class="label">Item:</span> <?= htmlspecialchars($item['item_name']) ?></p>
        <p><span class="label">Quantity:</span> <?= (int)$item['quantity'] ?></p>
        <p><span class="label">Description:</span> <?= htmlspecialchars($item['description']) ?></p>
        <p><span class="label">Status:</span> <?= htmlspecialchars($item['status']) ?></p>
    </div>
    <?php if ($item['status'] === 'Distributed'): ?>
        <button class="btn btn-distribute" disabled>Already Distributed</button>
    <?php else: ?>
        <button class="btn btn-distribute" id="distributeBtn" data-id="<?= $invId ?>">
            Mark as Distributed
        </button>
    <?php endif; ?>
    <div id="msgSuccess" class="success">Item marked as distributed. Redirecting...</div>
    <div id="msgError" class="error">Something went wrong. Try again.</div>
</div>

<script>
document.getElementById("distributeBtn")?.addEventListener("click", function () {
    const id = this.dataset.id;
    const btn = this;
    btn.disabled = true;
    btn.innerText = "Processing...";
    fetch("MarkDistributed.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "inventory_id=" + id
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "OK") {
            document.getElementById("msgSuccess").style.display = "block";
            setTimeout(() => {
                window.location.href = "StaffInventory.php";
            }, 1500);
        } else {
            document.getElementById("msgError").style.display = "block";
            btn.disabled = false;
            btn.innerText = "Mark as Distributed";
        }
    });
});
</script>
</body>
</html>