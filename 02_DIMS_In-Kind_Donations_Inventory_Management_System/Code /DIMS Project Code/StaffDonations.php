<?php
session_start();
include 'DonRegConnection.php';

// Authenticate staff only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$uStmt = $object->prepare("SELECT username, role FROM users WHERE user_id=? LIMIT 1");
$uStmt->bind_param("i", $userId);
$uStmt->execute();
$user = $uStmt->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}

// UPDATE ITEM QUANTITY ONLY
if (isset($_POST['update_item_quantity'])) {
    $itemId = intval($_POST['item_id']);
    $newQuantity = intval($_POST['quantity']);
    
    if ($newQuantity < 0) {
        echo "ERROR: Quantity cannot be negative";
        exit;
    }
    
    $stmt = $object->prepare("UPDATE donation_items SET quantity=? WHERE item_id=?");
    $stmt->bind_param("ii", $newQuantity, $itemId);
    $stmt->execute();
    
    echo "SUCCESS";
    exit;
}

// UPDATE EXPIRY DATE
if (isset($_POST['update_expiry_date'])) {
    $itemId = intval($_POST['item_id']);
    $expiryDate = $_POST['expiry_date'] ?: NULL;
    
    $stmt = $object->prepare("UPDATE donation_items SET expiry_date=? WHERE item_id=?");
    $stmt->bind_param("si", $expiryDate, $itemId);
    $stmt->execute();
    
    echo "SUCCESS";
    exit;
}

// UPDATE ITEM STATUS
if (isset($_POST['update_item_status'])) {
    $itemId = intval($_POST['item_id']);
    $newStatus = $_POST['status'];
    $oldStatus = $_POST['old_status'] ?? '';
    
    // Prevent changing from "In Inventory" or "Distributed"
    if ($oldStatus === 'In Inventory') {
        echo "ERROR: Item is in inventory - manage from Inventory section";
        exit;
    }
    
    if ($oldStatus === 'Distributed') {
        echo "ERROR: Cannot change status of distributed items";
        exit;
    }
    
    $stmt = $object->prepare("UPDATE donation_items SET itemStatus=? WHERE item_id=?");
    $stmt->bind_param("si", $newStatus, $itemId);
    $stmt->execute();
    
    // If moving to "In Inventory", trigger the move
    if ($newStatus === 'In Inventory') {
        // Get item details including category and expiry
        $getItem = $object->prepare("SELECT item_name, quantity, description, expiry_date FROM donation_items WHERE item_id=?");
        $getItem->bind_param("i", $itemId);
        $getItem->execute();
        $item = $getItem->get_result()->fetch_assoc();
        
        if ($item) {
            // Check if category was set
            $catCheck = $object->prepare("SELECT category_id FROM donation_items WHERE item_id=?");
            $catCheck->bind_param("i", $itemId);
            $catCheck->execute();
            $catResult = $catCheck->get_result()->fetch_assoc();
            
            $categoryId = $catResult['category_id'] ?? 12; // Default to Uncategorized
            
            // Insert into inventory
            $insertStmt = $object->prepare("
                INSERT INTO inventory_items 
                (donation_item_id, item_name, category_id, quantity, expiry_date, status) 
                VALUES (?, ?, ?, ?, ?, 'In Inventory')
            ");
            $insertStmt->bind_param(
                "isiis", 
                $itemId, 
                $item['item_name'], 
                $categoryId, 
                $item['quantity'], 
                $item['expiry_date']
            );
            $insertStmt->execute();
        }
    }
    
    echo "SUCCESS";
    exit;
}

// UPDATE CATEGORY
if (isset($_POST['update_category'])) {
    $itemId = intval($_POST['item_id']);
    $categoryId = intval($_POST['category_id']);
    
    // Add category_id column to donation_items if it doesn't exist
    $checkCol = $object->query("SHOW COLUMNS FROM donation_items LIKE 'category_id'");
    if ($checkCol->num_rows === 0) {
        $object->query("ALTER TABLE donation_items ADD COLUMN category_id INT(11) DEFAULT NULL");
        $object->query("ALTER TABLE donation_items ADD FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL");
    }
    
    $stmt = $object->prepare("UPDATE donation_items SET category_id=? WHERE item_id=?");
    $stmt->bind_param("ii", $categoryId, $itemId);
    $stmt->execute();
    
    echo "SUCCESS";
    exit;
}

// Fetch categories
$categories = $object->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

// Fetch donations with items
$q = "
SELECT d.*, u.username AS donor_name
FROM donations d
JOIN users u ON u.user_id = d.donor_id
ORDER BY d.created_at DESC
";
$donations = $object->query($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management - DIMS Staff</title>
    <link rel="stylesheet" href="StaffDonations.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-warehouse logo-icon"></i>
        <div class="logo-text">DIMS</div>
    </div>
    <ul class="nav-links">
        <li><a href="StaffDash.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="StaffCriticalNeeds.php"><i class="fas fa-heart"></i> Critical Needs</a></li>
        <li><a href="StaffDonations.php" class="active"><i class="fas fa-hand-holding-usd"></i> Manage Donations</a></li>
        <li><a href="StaffInventory.php"><i class="fas fa-archive"></i> Inventory</a></li>
        <li><a href="StaffFeedback.php"><i class="fas fa-history"></i> Feedback</a></li>
        <li><a href="StaffSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr(trim($user['username']), 0, 2)) ?></div>
        <div class="user-details">
            <h4><?= htmlspecialchars($user['username']) ?></h4>
            <p><?= htmlspecialchars($user['role']) ?></p>
        </div>
    </div>
</div>

<div class="main">
    <header>
        <h1>Donation Management</h1>
        <p>Process donation items here. Set categories, quantities, expiry dates, and move items to inventory.</p>
    </header>
    
    <div class="toolbar">
        <h2>Recent Donations</h2>
        <div class="toolbar-controls">
            <input type="text" class="search-box" placeholder="Search for items or donors...">
        </div>
    </div>
    
    <div class="donation-cards">
        <?php if ($donations->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fas fa-box-open fa-3x"></i>
                <h3>No Donations Yet</h3>
                <p>No donations have been submitted yet.</p>
            </div>
        <?php else: ?>
            <?php while ($don = $donations->fetch_assoc()): ?>
                <?php
                $itemQ = $object->prepare("SELECT di.*, c.category_name FROM donation_items di LEFT JOIN categories c ON di.category_id = c.category_id WHERE di.donation_id=?");
                $itemQ->bind_param("i", $don['donation_id']);
                $itemQ->execute();
                $items = $itemQ->get_result();
                ?>
                
                <div class="donation-card">
                    <div class="card-header">
                        <span class="category">Donation Number: <?= $don['donation_id'] ?></span>
                        <span class="date"><?= date("M d, Y", strtotime($don['donation_date'])) ?></span>
                    </div>
                    <div class="donation-details">
                        <p><span class="detail-label">Donor:</span> <?= htmlspecialchars($don['donor_name']) ?></p>
                        <p><span class="detail-label">Total Items:</span> <?= $don['total_items'] ?></p>
                        
                        <div class="detail-label" style="font-weight:600;margin:15px 0 10px 0;">Items:</div>
                        
                        <?php if ($items->num_rows === 0): ?>
                            <p class="no-items">No items in this donation</p>
                        <?php else: ?>
                            <ul style="list-style-type:none; padding-left:0;">
                            <?php while ($it = $items->fetch_assoc()): 
                                $statusClass = 'status-' . strtolower(str_replace(' ', '-', $it['itemStatus']));
                                $isReadOnly = in_array($it['itemStatus'], ['In Inventory', 'Distributed']);
                            ?>
                                <li class="donation-item">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <strong style="font-size: 1.1em;"><?= htmlspecialchars($it['item_name']) ?></strong>
                                        <span class="status-badge <?= $statusClass ?>"><?= $it['itemStatus'] ?></span>
                                    </div>
                                    
                                    <?php if ($isReadOnly): ?>
                                        <div class="itemConfirmSection">
                                            <strong class="itemConfirmMessage">✓ Item in <?= $it['itemStatus'] === 'In Inventory' ? 'Inventory' : 'Distributed' ?></strong>
                                            <p style="margin: 5px 0 0 0; color: #555; font-size: 0.9em;">
                                                <?= $it['itemStatus'] === 'In Inventory' ? 'Manage this item in the Inventory section' : 'This item has been distributed and cannot be modified' ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <!-- Quantity Edit -->
                                        <div style="margin-bottom: 10px;">
                                            <label><strong>Quantity:</strong></label>
                                            <input type="number" id="qty_<?= $it['item_id'] ?>" 
                                                   value="<?= $it['quantity'] ?>" min="0" 
                                                   style="width:80px; margin-left: 8px; padding: 4px;">
                                            <button class="btn-update buttonS" onclick="updateQuantity(<?= $it['item_id'] ?>)">
                                                Update
                                            </button>
                                        </div>
                                        
                                        <!-- Category Selection -->
                                        <div style="margin-bottom: 10px;">
                                            <label><strong>Category:</strong></label>
                                            <select id="cat_<?= $it['item_id'] ?>" style="margin-left: 8px; padding: 4px;">
                                                <option value="">Select Category</option>
                                                <?php 
                                                $categories->data_seek(0);
                                                while ($cat = $categories->fetch_assoc()): 
                                                    $selected = ($it['category_id'] == $cat['category_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $cat['category_id'] ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($cat['category_name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <button class="btn-update buttonS" onclick="updateCategory(<?= $it['item_id'] ?>)">
                                                Update
                                            </button>
                                        </div>
                                        
                                        <!-- Expiry Date -->
                                        <div style="margin-bottom: 10px;">
                                            <label><strong>Expiry Date:</strong></label>
                                            <input type="date" id="exp_<?= $it['item_id'] ?>" 
                                                   value="<?= $it['expiry_date'] ?>" 
                                                   style="margin-left: 8px; padding: 4px;">
                                            <button class="btn-update buttonS" onclick="updateExpiryDate(<?= $it['item_id'] ?>)">
                                                Update
                                            </button>
                                        </div>
                                        
                                        <!-- Description (READ ONLY) -->
                                        <?php if (!empty($it['description'])): ?>
                                        <div style="margin-bottom: 10px;">
                                            <label><strong>Donor Note:</strong></label>
                                            <span style="margin-left: 8px; font-style: italic; color: #666;">
                                                <?= htmlspecialchars($it['description']) ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Status Update -->
                                        <div style="margin-bottom: 8px;">
                                            <label><strong>Status:</strong></label>
                                            <select onchange="updateItemStatus(<?= $it['item_id'] ?>, this.value, '<?= $it['itemStatus'] ?>')"
                                                    id="status_<?= $it['item_id'] ?>"
                                                    style="margin-left: 8px; padding: 4px;">
                                                <option value="To be received" <?= $it['itemStatus']=="To be received"?"selected":"" ?>>To be received</option>
                                                <option value="Processing" <?= $it['itemStatus']=="Processing"?"selected":"" ?>>Processing</option>
                                                <option value="Rejected" <?= $it['itemStatus']=="Rejected"?"selected":"" ?>>Rejected</option>
                                                <option value="In Inventory" <?= $it['itemStatus']=="In Inventory"?"selected":"" ?>>In Inventory</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(itemId) {
    const quantity = document.getElementById('qty_' + itemId).value;
    if (quantity === '' || parseInt(quantity) < 0) {
        alert("Please enter a valid quantity (0 or greater)");
        return;
    }
    
    let data = new FormData();
    data.append("update_item_quantity", 1);
    data.append("item_id", itemId);
    data.append("quantity", quantity);
    
    fetch("StaffDonations.php", {
        method: "POST",
        body: data
    })
    .then(r => r.text())
    .then(res => {
        if (res === "SUCCESS") {
            alert("✓ Quantity updated successfully!");
            location.reload();
        } else {
            alert("✗ Error: " + res);
        }
    })
    .catch(err => {
        console.error(err);
        alert("✗ Network error. Please try again.");
    });
}

function updateCategory(itemId) {
    const categoryId = document.getElementById('cat_' + itemId).value;
    if (!categoryId) {
        alert("Please select a category");
        return;
    }
    
    let data = new FormData();
    data.append("update_category", 1);
    data.append("item_id", itemId);
    data.append("category_id", categoryId);
    
    fetch("StaffDonations.php", {
        method: "POST",
        body: data
    })
    .then(r => r.text())
    .then(res => {
        if (res === "SUCCESS") {
            alert("✓ Category updated successfully!");
            location.reload();
        } else {
            alert("✗ Error: " + res);
        }
    })
    .catch(err => {
        console.error(err);
        alert("✗ Network error. Please try again.");
    });
}

function updateExpiryDate(itemId) {
    const expiryDate = document.getElementById('exp_' + itemId).value;
    
    if (expiryDate) {
        const today = new Date().toISOString().split('T')[0];
        if (expiryDate < today) {
            if (!confirm("This expiry date is in the past. Continue anyway?")) {
                return;
            }
        }
    }
    
    let data = new FormData();
    data.append("update_expiry_date", 1);
    data.append("item_id", itemId);
    data.append("expiry_date", expiryDate);
    
    fetch("StaffDonations.php", {
        method: "POST",
        body: data
    })
    .then(r => r.text())
    .then(res => {
        if (res === "SUCCESS") {
            alert("✓ Expiry date updated successfully!");
            location.reload();
        } else {
            alert("✗ Error: " + res);
        }
    })
    .catch(err => {
        console.error(err);
        alert("✗ Network error. Please try again.");
    });
}

function updateItemStatus(itemId, newStatus, oldStatus) {
    if (oldStatus === 'In Inventory') {
        alert("Item is in inventory! Manage it from the Inventory section.");
        document.getElementById('status_' + itemId).value = oldStatus;
        return;
    }
    
    if (oldStatus === 'Distributed') {
        alert("Cannot change status of distributed items!");
        document.getElementById('status_' + itemId).value = oldStatus;
        return;
    }
    
    if (newStatus === 'In Inventory') {
        const categoryId = document.getElementById('cat_' + itemId).value;
        if (!categoryId) {
            alert("Please select a category before moving to inventory!");
            document.getElementById('status_' + itemId).value = oldStatus;
            return;
        }
        
        if (!confirm("Move this item to inventory?\n\nMake sure all edits are complete.")) {
            document.getElementById('status_' + itemId).value = oldStatus;
            return;
        }
    }
    
    if (!confirm("Change status from '" + oldStatus + "' to '" + newStatus + "'?")) {
        document.getElementById('status_' + itemId).value = oldStatus;
        return;
    }
    
    let data = new FormData();
    data.append("update_item_status", 1);
    data.append("item_id", itemId);
    data.append("status", newStatus);
    data.append("old_status", oldStatus);
    
    fetch("StaffDonations.php", {
        method: "POST",
        body: data
    })
    .then(r => r.text())
    .then(res => {
        if (res === "SUCCESS") {
            alert("✓ Status updated successfully!");
            location.reload();
        } else {
            alert("✗ Error: " + res);
            document.getElementById('status_' + itemId).value = oldStatus;
        }
    })
    .catch(err => {
        console.error(err);
        alert("✗ Network error. Please try again.");
        document.getElementById('status_' + itemId).value = oldStatus;
    });
}

// Live Search
document.querySelector('.search-box').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    const cards = document.querySelectorAll('.donation-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        const isVisible = text.includes(term);
        card.style.display = isVisible ? 'block' : 'none';
        if (isVisible) visibleCount++;
    });
    
    const resultsMsg = document.getElementById('results-message');
    if (!resultsMsg && cards.length > 0) {
        const msg = document.createElement('div');
        msg.id = 'results-message';
        msg.style.cssText = 'text-align: center; padding: 20px; color: #666;';
        if (visibleCount === 0 && term !== '') {
            msg.innerHTML = `<i class="fas fa-search"></i> No donations match "${term}"`;
            document.querySelector('.donation-cards').prepend(msg);
        }
    } else if (resultsMsg) {
        if (visibleCount === 0 && term !== '') {
            resultsMsg.innerHTML = `<i class="fas fa-search"></i> No donations match "${term}"`;
            resultsMsg.style.display = 'block';
        } else {
            resultsMsg.style.display = 'none';
        }
    }
});
</script>
</body>
</html>