<?php
session_start();
include 'DonRegConnection.php';

// Require user logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: LoginPage.php");
    exit;
}
$donor_id = (int)$_SESSION['user_id'];

// Fetching donor username for header/avatar
$userStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ? LIMIT 1");
$userStmt->bind_param("i", $donor_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc() ?? ['username' => 'Donor', 'role' => 'Donor'];

// Fetch all donations for this donor (most recent first)
// REMOVED donation.status because it no longer exists
$donations = [];
$donStmt = $object->prepare("
    SELECT donation_id, total_items, donation_date
    FROM donations
    WHERE donor_id = ?
    ORDER BY donation_date DESC
");
$donStmt->bind_param("i", $donor_id);
$donStmt->execute();
$donRes = $donStmt->get_result();
while ($row = $donRes->fetch_assoc()) {
    $donations[] = $row;
}

// Helper to fetch items for a donation
function getItemsForDonation($db, $donation_id) {
    $items = [];
    $stmt = $db->prepare("
        SELECT item_id, item_name, quantity, description, itemStatus
        FROM donation_items
        WHERE donation_id = ?
        ORDER BY item_id ASC
    ");
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $items[] = $r;
    return $items;
}

function statusBadgeClass($status) {
    $s = strtolower($status);
    if (strpos($s,'reject') !== false) return 'status-rejected';
    if (strpos($s,'process') !== false) return 'status-processing';
    if (strpos($s,'received') !== false) return 'status-received';
    if (strpos($s,'distributed') !== false) return 'status-completed';
    return 'status-pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Track Donations — DIMS</title>
<link rel="stylesheet" href="TrackDon.css">
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
          <li><a href="DonorMainPage.php" class="nav-link" data-page="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li><a href="CriticalPage.php" class="nav-link" data-page="critical-needs"><i class="fas fa-heart"></i> Critical Needs</a></li>
          <li><a href="DonForm.php" class="nav-link" data-page="donate"><i class="fas fa-hand-holding-usd"></i> Donate</a></li>
          <li><a href="TrackDon.php" class="nav-link active4" data-page="track-donations"><i class="fas fa-history"></i> Track Donations</a></li>
          <li><a href="DonorSettings.php" class="nav-link" data-page="settings"><i class="fas fa-cog"></i> Account Settings</a></li>
          <li><a href="logout.php" class="nav-link"> <i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
      <div class="user-info">
          <div class="user-avatar"><?php echo strtoupper(substr($user['username'],0,2)); ?></div>
          <div class="user-details">
              <h4><?php echo htmlspecialchars($user['username']); ?></h4>
              <p><?php echo htmlspecialchars($user['role']); ?></p>
          </div>
      </div>
  </div>

  <div class="main">
      <header>
          <div class="page-title">
              <h1>Track Your Donations</h1>
              <p>See donation history, statuses and download reports</p>
              <p><strong style="color: #128272;">Disclamer:</strong> Quantity adjustments are as a result of staff assessment and selection of items that meet the centre's quality standards</p>
          </div>
      </header>

      <div class="card">
          <div class="card-header">
              <h3 class="card-title">Donation Tracking</h3>
              <div class="card-icon"><i class="fas fa-file"></i></div>
          </div>
          <div class="card-body">
              <?php if (count($donations) === 0): ?>
                  <p>No donations found. <a class="classLink" href="DonForm.php">Make your first donation</a></p>
              <?php else: ?>
                  <div class="donation-list">

                      <?php foreach ($donations as $d):
                          $items = getItemsForDonation($object, (int)$d['donation_id']);
                      ?>
                          <div class="donation-card">
                              <div class="donation-header">
                                  <div class="donation-meta">
                                      <div class="donation-number">Donation Number: <?php echo (int)$d['donation_id']; ?></div>
                                      <div class="donation-date"><?php echo date('M j, Y', strtotime($d['donation_date'])); ?></div>
                                  </div>
                                  <div class="donation-actions">
                                      <a class="btn pdf-btn" href="DownloadDonationPDF.php?id=<?php echo (int)$d['donation_id']; ?>" target="_blank" title="Download donation as PDF">
                                          <i class="fas fa-download"></i> PDF
                                      </a>
                                  </div>
                              </div>

                              <!-- Items table -->
                              <div class="donation-items-table">
                                  <table>
                                      <thead>
                                          <tr>
                                              <th>Item</th>
                                              <th>Quantity</th>
                                              <th>Status</th>
                                              <th>Description</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php if (count($items) === 0): ?>
                                              <tr><td colspan="4">No items logged for this donation.</td></tr>
                                          <?php else: foreach ($items as $it): ?>
                                              <tr>
                                                  <td><?php echo htmlspecialchars($it['item_name']); ?></td>
                                                  <td><?php echo (int)$it['quantity']; ?></td>
                                                  <td><span class="donation-status <?php echo statusBadgeClass($it['itemStatus']); ?>"><?php echo htmlspecialchars($it['itemStatus']); ?></span></td>
                                                  <td><?php echo htmlspecialchars($it['description'] ?: '—'); ?></td>
                                              </tr>
                                          <?php endforeach; endif; ?>
                                      </tbody>
                                  </table>
                              </div>

                          </div>
                      <?php endforeach; ?>

                  </div>

                  <div style="margin-top:18px;">
                      <a class="btn pdf-btn" href="DownloadAllDonationsPDF.php" target="_blank" title="Download all donations as PDF">
                          <i class="fas fa-file-pdf"></i> Download All Donations (PDF)
                      </a>
                  </div>

              <?php endif; ?>
          </div>
      </div>
  </div>
</body>
</html>