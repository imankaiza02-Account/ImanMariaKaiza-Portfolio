<?php
session_start();
include "DonRegConnection.php";

// Staff-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}

$staff_id = (int)$_SESSION['user_id'];

// Fetch staff profile
$stmt = $object->prepare("
    SELECT username, password_hash, contactDetails, role
    FROM users
    WHERE user_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    die("User not found.");
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {

    $username = trim($_POST['username']);
    $newPassword = trim($_POST['new_password']);
    $phone = trim($_POST['contactDetails']);

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $staff['password_hash']; // Keep existing password
    }

    $update = $object->prepare("
        UPDATE users 
        SET username = ?, password_hash = ?, contactDetails = ?
        WHERE user_id = ?
    ");
    $update->bind_param("sssi", $username, $hashedPassword, $phone, $staff_id);

    if ($update->execute()) {
        header("Location: StaffSettings.php?success=1");
        exit;
    } else {
        $error = "Failed to update profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Settings</title>
    <link rel="stylesheet" href="StaffSettings.css?v=1.1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-warehouse logo-icon"></i>
        <div class="logo-text">DIMS</div>
    </div>

    <ul class="nav-links">
        <li><a href="StaffDash.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="StaffCriticalNeeds.php"><i class="fas fa-heart"></i> Critical Needs</a></li>
        <li><a href="StaffDonations.php"><i class="fas fa-hand-holding-usd"></i> Manage Donations</a></li>
        <li><a href="StaffInventory.php"><i class="fas fa-archive"></i> Inventory</a></li>
        <li><a href="StaffFeedback.php"><i class="fas fa-history"></i> Feedback</a></li>
        <li><a href="StaffSettings.php" class="active"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <div class="user-info">
        <div class="user-avatar">
            <?= strtoupper(substr($staff['username'], 0, 1)) ?>
        </div>
        <div class="user-details">
            <h4><?= htmlspecialchars($staff['username']) ?></h4>
            <p><?= htmlspecialchars($staff['role']) ?></p>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="page-content" id="settings">
        <header>
            <div class="page-title">
                <h1>Account Settings</h1>
                <p>Manage and update your staff account information</p>
            </div>
        </header>
        <!-- SUCCESS -->
        <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">
            Profile updated successfully!
        </div>
        <?php endif; ?>
        <!-- ERROR -->
        <?php if (isset($error)): ?>
        <div class="error-msg">
            <?= $error ?>
        </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
                <div class="card-icon"><i class="fas fa-user"></i></div>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username"
                        value="<?= htmlspecialchars($staff['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password"
                        placeholder="Leave empty to keep existing password">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="contactDetails"
                        value="<?= htmlspecialchars($staff['contactDetails']) ?>">
                </div>
                <div class="form-group">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
</body>
</html>