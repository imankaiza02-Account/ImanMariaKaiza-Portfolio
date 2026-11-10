<?php
session_start();
include "DonRegConnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: LoginPage.php");
    exit;
}

$adminId = (int)$_SESSION['user_id'];
$adminStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ?");
$adminStmt->bind_param("i", $adminId);
$adminStmt->execute();
$admin = $adminStmt->get_result()->fetch_assoc();

$activeInv = $object->query("
    SELECT i.*, c.category_name, c.is_perishable
    FROM inventory_items i
    LEFT JOIN categories c ON i.category_id = c.category_id
    WHERE i.status = 'In Inventory'
    ORDER BY i.expiry_date ASC, i.created_at DESC
");

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
    <title>Admin Inventory</title>
    <link rel="stylesheet" href="AdInven.css">
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
        <li><a href="Adminhome.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="AdManageUser.php"><i class="fas fa-user"></i> Manage Users</a></li>
        <li><a href="AdInven.php" class="active"><i class="fas fa-box"></i> Inventory</a></li>
        <li><a href="AdminFeedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
        <li><a href="AdminSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($admin['username'],0,2)) ?></div>
        <div class="user-details">
            <h4><?= htmlspecialchars($admin['username']) ?></h4>
            <p><?= htmlspecialchars($admin['role']) ?></p>
        </div>
    </div>
</div>

<div class="main">
    <header>
        <h1>Inventory Overview</h1>
        <p>View-only list of all items in inventory and distributed.</p>
    </header>

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
                </tr>
            </thead>
            <tbody>
            <?php if ($activeInv->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px;">No items in inventory.</td></tr>
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
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

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
                </tr>
            </thead>
            <tbody>
            <?php if ($distInv->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No distributed items.</td></tr>
            <?php else: ?>
                <?php while ($row = $distInv->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= (int)$row['quantity'] ?></td>
                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                    <td><?= $row['expiry_date'] ?: '—' ?></td>
                    <td><?= $row['distributed_date'] ? date('M d, Y H:i', strtotime($row['distributed_date'])) : '—' ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>