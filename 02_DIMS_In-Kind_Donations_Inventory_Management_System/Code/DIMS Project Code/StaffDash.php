<?php
session_start();
include 'DonRegConnection.php';

//Authenticate: staff-only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}
$userId = (int)$_SESSION['user_id'];
$uStmt = $object->prepare("SELECT username, role, firstName, lastName FROM users WHERE user_id=? LIMIT 1");
$uStmt->bind_param("i", $userId);
$uStmt->execute();
$user = $uStmt->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'Staff') {
    header('Location: LoginPage.php');
    exit;
}

//DASHBOARD STATISTICS 
//Pending donations = donations with at least 1 pending item
$pendingDonations = $object->query("
    SELECT COUNT(DISTINCT donation_id) AS cnt
    FROM donation_items
    WHERE itemStatus = 'To be received'
")->fetch_assoc()['cnt'];

//Total items in inventory
$totalInventory = $object->query("
    SELECT COALESCE(SUM(quantity),0) AS total
    FROM inventory_items
")->fetch_assoc()['total'];

//Recent donations 
$recentDonations = $object->query("
    SELECT d.donation_id, d.donation_date, d.total_items, u.username
    FROM donations d
    LEFT JOIN users u ON u.user_id = d.donor_id
    ORDER BY d.donation_date DESC
    LIMIT 4
");

//Recent donation items
$recentItems = $object->query("
    SELECT di.item_id, di.item_name, di.quantity, di.itemStatus, di.donation_id, d.donor_id, u.username
    FROM donation_items di
    LEFT JOIN donations d ON d.donation_id = di.donation_id
    LEFT JOIN users u ON u.user_id = d.donor_id
    ORDER BY di.item_id DESC
    LIMIT 4
");

//Critical needs
$critRes = $object->query("
    SELECT ci.item_name, ci.quantity_needed, ci.priority, c.category_name, c.is_perishable
    FROM critical_items ci
    LEFT JOIN categories c ON c.category_id = ci.category_id
    ORDER BY FIELD(ci.priority,'Urgent','High','Medium','Low'), ci.created_at DESC
    LIMIT 4
");

//FUNCTION: COMPUTE DONATION STATUS FROM ITEMS
function computeDonationStatus($object, $donationId) {
    $q = $object->prepare("
        SELECT itemStatus FROM donation_items WHERE donation_id=?
    ");
    $q->bind_param("i", $donationId);
    $q->execute();
    $res = $q->get_result();

    if ($res->num_rows === 0) return "No Items";

    $statuses = [];
    while ($r = $res->fetch_assoc()) {
        $statuses[] = $r['itemStatus'];
    }

    $unique = array_unique($statuses);

    if (count($unique) > 1) return "Mixed";
    return $unique[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Main Page</title>
    <link rel="stylesheet" href="StaffDashStyling.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!--Sidebar-->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="logo-text">DIMS</div>
        </div>
        <ul class="nav-links">
            <li><a href="StaffDash.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="StaffCriticalNeeds.php"><i class="fas fa-heart"></i> Critical Needs</a></li>
            <li><a href="StaffDonations.php"><i class="fas fa-hand-holding-usd"></i> Manage Donations</a></li>
            <li><a href="StaffInventory.php"><i class="fas fa-archive"></i> Inventory</a></li>
            <li><a href="StaffFeedback.php"><i class="fas fa-history"></i> Feedback</a></li>
            <li><a href="StaffSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr(trim($user['username']),0,2)) ?></div>
            <div class="user-details">
                <h4><?= htmlspecialchars($user['username']) ?></h4>
                <p><?= htmlspecialchars($user['role']) ?></p>
            </div>
        </div>
    </div>
    <!--Main Content-->
    <div class="main">
        <div class="page-content" id="Dashboard">
            <header>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($user['username']) ?>!</p>
            </header>
            <!--Dashboard Grid-->
            <div class="dashboard-grid">
                <!--Overview-->
                <div class="card overview-card">
                    <div class="card-header"><h3 class="card-title">Overview</h3></div>
                    <div class="stat">
                        <div class="stat-value"><?= (int)$pendingDonations ?></div>
                        <div class="stat-label">Donations to receive</div>
                    </div>
                </div>
                <!--Recent Donations-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Donations</h3>
                        <div class="card-icon"><i class="fas fa-history"></i></div>
                    </div>
                    <div class="recent-donations">
                        <?php if ($recentDonations->num_rows === 0): ?>
                            <p class="no-data">No recent donations found.</p>
                        <?php else: while ($d = $recentDonations->fetch_assoc()): ?>
                            <?php 
                                $computedStatus = computeDonationStatus($object, $d['donation_id']);
                                $statusClass = strtolower(str_replace(" ", "-", $computedStatus));
                            ?>
                            <div class="donation-item">
                                <div class="donation-info">
                                    <h4>Donation Number: <?= $d['donation_id'] ?> — <?= htmlspecialchars($d['username'] ?: 'Unknown') ?></h4>
                                    <p><?= date('M j, Y', strtotime($d['donation_date'])) ?> | Items: <?= (int)$d['total_items'] ?></p>
                                </div>
                                <div class="donation-status status-<?= $statusClass ?>">
                                    <?= htmlspecialchars($computedStatus) ?>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>
                <!--Recent Items-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Items</h3>
                        <div class="card-icon"><i class="fas fa-box-open"></i></div>
                    </div>
                    <div class="recent-donations">
                        <?php if ($recentItems->num_rows === 0): ?>
                            <p class="no-data">No recent items.</p>
                        <?php else: while ($it = $recentItems->fetch_assoc()): ?>
                            <div class="donation-item">
                                <div class="donation-info">
                                    <h4><?= htmlspecialchars($it['item_name']) ?></h4>
                                    <p>Quantity: <?= (int)$it['quantity'] ?> | Donor: <?= htmlspecialchars($it['username'] ?: 'Unknown') ?></p>
                                </div>
                                <div class="donation-status status-<?= strtolower(str_replace(' ','-',$it['itemStatus'])) ?>">
                                    <?= htmlspecialchars($it['itemStatus']) ?>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>
                <!--Critical Needs-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Critical Needs</h3>
                        <div class="card-icon"><i class="fas fa-exclamation-circle"></i></div>
                    </div>
                    <div class="critical-needs-content">
                        <?php if ($critRes->num_rows === 0): ?>
                            <p class="no-data">No critical items configured.</p>
                        <?php else: while ($c = $critRes->fetch_assoc()): ?>
                            <div class="critical-item">
                                <div class="critical-item-info">
                                    <strong><?= htmlspecialchars($c['item_name']) ?></strong>
                                    <div class="critical-item-category"><?= htmlspecialchars($c['category_name'] ?: 'Uncategorized') ?></div>
                                </div>
                                <div class="critical-item-details">
                                    <span class="priority <?= strtolower($c['priority']) ?>"><?= htmlspecialchars($c['priority']) ?></span>
                                    <div class="quantity-needed">Needed: <?= (int)$c['quantity_needed'] ?></div>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>
            </div>
            <!--CTA-->
            <div class="cta-section">
                <h2 class="cta-title">Want to Manage and Overview Inventory?</h2>
                <p class="cta-description">Manage Inventory Items and Distribute Them</p>
                <a href="#" style="text-decoration:none;">
                    <button class="btn cta-button" onclick="window.location.href='StaffInventory.php'">
                        <i class="fas fa-hand-holding-heart"></i> Manage Inventory
                    </button>
                </a>
            </div>
            <!--Reports-->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Generate Inventory Report</h3>
                    <div class="card-icon"><i class="fas fa-file-alt"></i></div>
                </div>
                <div class="report-options">
                    <div class="report-option">
                        <div class="report-info">
                            <h4>Inventory</h4>
                            <p>Complete record of all the items currently in inventory</p>
                        </div>
                        <a href="DownloadActiveInventoryPDF.php" style="text-decoration: none;" target="_blank">
                            <button class="btn btn-secondary download-report">
                            <i class="fas fa-download"></i> Generate
                        </button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>