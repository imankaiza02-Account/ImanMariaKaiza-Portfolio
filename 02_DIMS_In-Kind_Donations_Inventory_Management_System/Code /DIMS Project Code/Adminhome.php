<?php
session_start();
include "DonRegConnection.php";

//Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: LoginPage.php");
    exit();
}

$adminId = (int)$_SESSION['user_id'];

//Get admin user details
$userStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $adminId);
$userStmt->execute();
$admin_user = $userStmt->get_result()->fetch_assoc();

//Dashboard Statistics
//Total Users Count
$totalUsersResult = $object->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $totalUsersResult->fetch_assoc()['total'];

//Total Approved Donors
$totalDonorsResult = $object->query("SELECT COUNT(*) as total FROM users WHERE role='Donor' AND approvalStatus='Approved'");
$totalDonors = $totalDonorsResult->fetch_assoc()['total'];

//Total Approved Staff
$totalStaffResult = $object->query("SELECT COUNT(*) as total FROM users WHERE role='Staff' AND approvalStatus='Approved'");
$totalStaff = $totalStaffResult->fetch_assoc()['total'];

//Pending Staff and Additional Admin Approvals
$pendingUsersResult = $object->query("SELECT COUNT(*) as total FROM users WHERE approvalStatus='Pending' AND (role='Staff' OR role='Admin')");
$pendingUsers = $pendingUsersResult->fetch_assoc()['total'];

//Total Donations
$totalDonationsResult = $object->query("SELECT COUNT(*) as total FROM donations");
$totalDonations = $totalDonationsResult->fetch_assoc()['total'];

//Total Inventory Items
$totalInventoryResult = $object->query("SELECT COUNT(*) as total FROM inventory_items WHERE status='In Inventory'");
$totalInventory = $totalInventoryResult->fetch_assoc()['total'];

//Total Distributed Items
$totalDistributedResult = $object->query("SELECT COUNT(*) as total FROM inventory_items WHERE status='Distributed'");
$totalDistributed = $totalDistributedResult->fetch_assoc()['total'];

//Total Feedback 
$totalFeedbackResult = $object->query("SELECT COUNT(*) as total FROM donor_feedback");
$totalFeedback = $totalFeedbackResult->fetch_assoc()['total'];

//Recent Activities - Last 5 donations
$recentDonations = $object->query("
    SELECT d.donation_id, d.donation_date, u.username, d.total_items
    FROM donations d
    JOIN users u ON d.donor_id = u.user_id
    ORDER BY d.donation_date DESC
    LIMIT 5
");

//Recent User Registrations - Last 5 users
$recentUsers = $object->query("
    SELECT user_id, username, role, approvalStatus, dateJoined
    FROM users
    ORDER BY dateJoined DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DIMS</title>
    <link rel="stylesheet" href="AdminHome.css">
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
            <li><a href="AdminHome.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="AdManageUser.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="AdInven.php"><i class="fas fa-box"></i> Inventory</a></li>
            <li><a href="AdminFeedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="AdminSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($admin_user['username'], 0, 2)) ?></div>
            <div class="user-details">
                <h4><?= htmlspecialchars($admin_user['username']) ?></h4>
                <p><?= htmlspecialchars($admin_user['role']) ?></p>
            </div>
        </div>
    </div>

    <!--Main Content-->
    <div class="main">
        <header>
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($admin_user['username']) ?>!</p>
        </header>

        <!--Statistics Grid-->
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total Users</p>
                <h3 class="stat-value"><?= $totalUsers ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Total Approved Donors</p>
                <h3 class="stat-value"><?= $totalDonors ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Total Approved Staff Members</p>
                <h3 class="stat-value"><?= $totalStaff ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Pending Staff and Admin Approvals</p>
                <h3 class="stat-value"><?= $pendingUsers ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Total Donations</p>
                <h3 class="stat-value"><?= $totalDonations ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Items in Inventory</p>
                <h3 class="stat-value"><?= $totalInventory ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Distributed Items</p>
                <h3 class="stat-value"><?= $totalDistributed ?></h3>
            </div>

            <div class="stat-card">
                <p class="stat-label">Feedback Received</p>
                <h3 class="stat-value"><?= $totalFeedback ?></h3>
            </div>
        </div>

        <!--Recent Activities Section-->
        <div class="dashboard-grid">
            <!--Recent Donations-->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Donations</h3>
                </div>
                <div class="card-body">
                    <?php if ($recentDonations->num_rows === 0): ?>
                        <p class="no-data">No recent donations.</p>
                    <?php else: ?>
                        <table class="simple-table">
                            <thead>
                                <tr>
                                    <th>Donor</th>
                                    <th>Items</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($donation = $recentDonations->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($donation['username']) ?></td>
                                    <td><?= $donation['total_items'] ?></td>
                                    <td><?= date('M d, Y', strtotime($donation['donation_date'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent User Registrations -->
            <div class="card">
                <div class="card-header">
                    <h3>Recent Users</h3>
                </div>
                <div class="card-body">
                    <?php if ($recentUsers->num_rows === 0): ?>
                        <p class="no-data">No recent user registrations.</p>
                    <?php else: ?>
                        <table class="simple-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= $user['role'] ?></td>
                                    <td><?= $user['approvalStatus'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="management-section">
            <h2>Want to Manage and Overview Users?</h2>
            <p>Approved, Reject, Delete Users in the System</p>
            <a href="AdManageUser.php" class="btn-manage">
                <i class="fas fa-users"></i> Manage Users
            </a>
        </div>

        <!--Generate Report Section -->
        <div class="report-section">
            <h3>Generate Users Report</h3>
            <div class="report-option">
                <div class="report-info">
                    <h4>User Report</h4>
                    <p>Complete record of all users in the system</p>
                </div>
                <a href="GenerateAllUsersPDF.php" target="_blank" class="btn-generate">
                    <i class="fas fa-download"></i> Generate
                </a>
            </div>
        </div>
    </div>
</body>
</html>