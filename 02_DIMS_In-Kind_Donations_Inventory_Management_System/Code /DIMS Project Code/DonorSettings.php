<?php
session_start();
include "DonRegConnection.php";

// User must be logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: LoginPage.php");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// Fetch user details
$stmt = $object->prepare("
    SELECT username, password_hash, contactDetails, role 
    FROM users 
    WHERE user_id = ? LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Handle updating profile
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {

    $username = trim($_POST['username']);
    $newPassword = trim($_POST['new_password']);
    $phone = trim($_POST['contactDetails']);

    if (!empty($newPassword)) {
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    } 
    else {
    // Keep existing hash
    $hashedPassword = $user['password_hash'];
    }

    $update = $object->prepare("
    UPDATE users 
    SET username = ?, password_hash = ?, contactDetails = ?
    WHERE user_id = ?");

$update->bind_param("sssi", $username, $hashedPassword, $phone, $user_id);

    if ($update->execute()) {
        header("Location: DonorSettings.php?success=1");
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
    <title>Donor Settings</title>
    <link rel="stylesheet" href="DonorSettings.css">
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
            <li><a href="DonorMainPage.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="CriticalPage.php" class="nav-link"><i class="fas fa-heart"></i> Critical Needs</a></li>
            <li><a href="DonForm.php" class="nav-link"><i class="fas fa-hand-holding-usd"></i> Donate</a></li>
            <li><a href="TrackDon.php" class="nav-link"><i class="fas fa-history"></i> Track Donations</a></li>
            <li><a href="DonorSettings.php" class="nav-link active4"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="logout.php" class="nav-link"> <i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <div class="user-details">
                <h4><?= htmlspecialchars($user['username']) ?></h4>
                <p><?= htmlspecialchars($user['role']) ?></p>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="page-content" id="settings">
            <header>
                <div class="page-title">
                    <h1>Account Settings</h1>
                    <p>Manage and update your account information</p>
                </div>
            </header>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div style="padding: 10px; background: #d4edda; color: #155724; margin-bottom: 20px; border-radius: 8px;">
                    Profile updated successfully!
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div style="padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 20px; border-radius: 8px;">
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
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" class="form-control" id="password" name="new_password" placeholder="Enter new password (leave it empty to keep old)">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="contactDetails"
                               value="<?= htmlspecialchars($user['contactDetails']) ?>">
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