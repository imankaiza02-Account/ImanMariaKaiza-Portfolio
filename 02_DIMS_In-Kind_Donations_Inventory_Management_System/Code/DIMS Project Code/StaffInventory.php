<?php
session_start();
include "DonRegConnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}

$staffId = (int)$_SESSION['user_id'];
$userStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $staffId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Pull active inventory items with category name and expiry date
$activeInv = $object->query("
    SELECT i.*, c.category_name, c.is_perishable
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE i.status = 'In Inventory'
    ORDER BY i.expiry_date ASC, i.created_at DESC
");

// Pull distributed items with category name
$distInv = $object->query("
    SELECT i.*, c.category_name, c.is_perishable
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE i.status = 'Distributed'
    ORDER BY i.distributed_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory - DIMS Staff</title>
    <link rel="stylesheet" href="StaffInventory.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i class="fas fa-warehouse logo-icon"></i>
        <div class="logo-text">DIMS</div>
    </div>

    <ul class="nav-links">
        <li><a href="StaffDash.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="StaffCriticalNeeds.php"><i class="fas fa-heart"></i> Critical Needs</a></li>
        <li><a href="StaffDonations.php"><i class="fas fa-hand-holding-usd"></i> Manage Donations</a></li>
        <li><a href="StaffInventory.php" class="active"><i class="fas fa-archive"></i> Inventory</a></li>
        <li><a href="StaffFeedback.php"><i class="fas fa-history"></i> Feedback</a></li>
        <li><a href="StaffSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <div class="user-info">
        <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
        <div class="user-details">
            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
            <p><?php echo htmlspecialchars($user['role']); ?></p>
        </div>
    </div>
</div>

<div class="main">
    <header>
        <h1>Inventory Management</h1>
        <p>View and manage all items currently in inventory or distributed.</p>
    </header>

    <!-- REPORT BUTTONS -->
    <div class="report-buttons">
        <a href="DownloadFullInventoryPDF.php" target="_blank" class="btn-report"><i class="fas fa-file-pdf"></i> Full Inventory Report</a>
        <a href="DownloadActiveInventoryPDF.php" target="_blank" class="btn-report"><i class="fas fa-file-pdf"></i> Items In Inventory</a>
        <a href="DownloadDistributedItemsPDF.php" target="_blank" class="btn-report"><i class="fas fa-file-pdf"></i> Distributed Items</a>
    </div>
    
    <h2 class="section-title">Items Currently In Inventory</h2>
    <div class="inventory-table">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Expiry Date</th>
                    <th>Perishable</th>
                    <th>Date Added</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($activeInv->num_rows === 0): ?>
                <tr><td colspan="8" style="text-align: center; padding: 20px;">No items in inventory.</td></tr>
            <?php else: ?>
                <?php while ($row = $activeInv->fetch_assoc()): 
                    $isPerishable = $row['is_perishable'] ? 'Yes' : 'No';
                    $expiryClass = '';
                    if ($row['expiry_date']) {
                        $expiryDate = new DateTime($row['expiry_date']);
                        $today = new DateTime();
                        if ($expiryDate < $today) {
                            $expiryClass = 'expired';
                        } elseif ($today->diff($expiryDate)->days <= 7) {
                            $expiryClass = 'expiring-soon';
                        }
                    }
                ?>
                <tr class="<?= $expiryClass ?>">
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= (int)$row['quantity'] ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                    <td><?= $row['expiry_date'] ?: '—' ?></td>
                    <td><?= $isPerishable ?></td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <?php if ($row['qr_code_path']): ?>
                            <img src="<?= $row['qr_code_path'] ?>" class="qr-preview" alt="QR Code">
                            <br>
                            <a href="<?= $row['qr_code_path'] ?>" download class="download-link">Download</a>
                        <?php else: ?>
                            <button class="btn-small generateQR" data-id="<?= $row['inventory_id'] ?>">
                                Generate QR
                            </button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn-small distributeBtn" data-id="<?= $row['inventory_id'] ?>">
                            Mark Distributed
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- DISTRIBUTED ITEMS SECTION -->
    <h2 class="section-title">Distributed Items</h2>
    <div class="inventory-table">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Expiry Date</th>
                    <th>Distributed Date</th>
                    <th>QR Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($distInv->num_rows === 0): ?>
                <tr><td colspan="7" style="text-align: left; padding: 20px;">No distributed items yet.</td></tr>
            <?php else: ?>
                <?php while ($row = $distInv->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= (int)$row['quantity'] ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                    <td><?= $row['expiry_date'] ?: '—' ?></td>
                    <td><?= $row['distributed_date'] ? date('M d, Y H:i', strtotime($row['distributed_date'])) : '—' ?></td>
                    <td>
                        <?php if ($row['qr_code_path']): ?>
                            <img src="<?= $row['qr_code_path'] ?>" class="qr-preview" alt="QR Code">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="status-distributed">Distributed</td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="StaffInventory.js"></script>
</body>
</html>