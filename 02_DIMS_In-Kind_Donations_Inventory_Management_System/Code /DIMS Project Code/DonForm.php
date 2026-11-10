<?php
session_start();
include 'DonRegConnection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: LoginPage.php");
    exit;
}
$userId = (int)$_SESSION['user_id'];
//Fetch user info (for header/avatar)
$userStmt=$object->prepare("SELECT username, role FROM users WHERE user_id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userRes=$userStmt->get_result();
$user=$userRes->fetch_assoc() ?? ['username'=>'Donor', 'role'=>'Donor'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - DIMS</title>
    <link rel="stylesheet" href="DonForm.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!--SIDEBAR-->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-warehouse logo-icon"></i>
            <div class="logo-text">DIMS</div>
        </div>
        <ul class="nav-links">
            <li><a href="DonorMainPage.php" class="nav-link" data-page="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="CriticalPage.php" class="nav-link" data-page="critical-needs"><i class="fas fa-heart"></i> Critical Needs</a></li>
            <li><a href="DonForm.php" class="nav-link active1" data-page="donate"><i class="fas fa-hand-holding-usd"></i> Donate</a></li>
            <li><a href="TrackDon.php" class="nav-link" data-page="track-donations"><i class="fas fa-history"></i> Track Donations</a></li>
            <li><a href="DonorSettings.php" class="nav-link" data-page="settings"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="logout.php" class="nav-link"> <i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <div class="user-info">
          <div class="user-avatar"><?php echo strtoupper(substr($user['username'],0,2));?></div>
          <div class="user-details">
              <h4><?php echo htmlspecialchars($user['username']); ?></h4>
              <p><?php echo htmlspecialchars($user['role']); ?></p>
          </div>
      </div>
  </div>
  <div class="main">
    <header>
      <h1>Make a Donation</h1>
      <p>Please provide us with information on what you're donating</p>
    </header>
    <div class="donation-container">
      <form id="donationForm" method="post" novalidate>
        <input type="hidden" name="donor_id" value="<?php echo $userId; ?>">

        <div id="itemsList">
          <!-- one item template rendered by JS too; keep one default -->
          <div class="item-card" data-index="0">
            <div class="item-row">
              <label>Item Name</label>
              <input type="text" name="itemName[]" required placeholder="e.g. blankets">
            </div>
            <div class="item-row half">
              <label>Quantity</label>
              <input type="number" name="quantity[]" min="1" max="1000" value="1" required>
            </div>
            <div class="item-row">
              <label>Description</label>
              <textarea name="description[]" rows="2" placeholder="This is optional, but you can include a short description of the item"></textarea>
            </div>
            <div class="item-actions">
              <button type="button" class="removeItemBtn" title="Remove item">Remove</button>
            </div>
          </div>
        </div>

        <div class="donation-controls">
          <button type="button" id="addItemBtn" class="btn">+ Add item</button>
          <span class="item-limit-note">You can add up to<strong id="maxItems">10</strong>items</span>
        </div>

        <div class="submit-row">
          <button type="submit" id="submitDonation" class="btn btn-primary">Submit Donation</button>
        </div>

        <div id="formMessage" aria-live="polite"></div>
      </form>
    </div>
  </div>
<script src="DonForm.js"></script>
</body>
</html>