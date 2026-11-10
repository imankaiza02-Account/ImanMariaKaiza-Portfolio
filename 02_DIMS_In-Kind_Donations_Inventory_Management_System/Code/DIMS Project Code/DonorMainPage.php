<?php
session_start();
include 'DonRegConnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: LoginPage.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Fetch donor info
$userStmt = $object->prepare("
    SELECT username, role
    FROM users 
    WHERE user_id = ? 
    LIMIT 1
");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc() ?? ['username' => 'Donor', 'role' => 'Donor'];

//FUNCTION
function computeDonationStatus($object, $donationId) {
    $q = $object->prepare("
        SELECT itemStatus 
        FROM donation_items 
        WHERE donation_id = ?
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

// Total donations
$totalDon = $object->prepare("SELECT COUNT(*) AS cnt FROM donations WHERE donor_id = ?");
$totalDon->bind_param("i", $userId);
$totalDon->execute();
$totalCount = $totalDon->get_result()->fetch_assoc()['cnt'];

//Pending = at least one item "To be received"
$pendingDon = $object->prepare("
    SELECT COUNT(DISTINCT d.donation_id) AS cnt
    FROM donations d
    JOIN donation_items di ON di.donation_id = d.donation_id
    WHERE d.donor_id = ?
      AND di.itemStatus = 'To be received'
");
$pendingDon->bind_param("i", $userId);
$pendingDon->execute();
$pendingCount = $pendingDon->get_result()->fetch_assoc()['cnt'];

//Distributed = ALL items "Distributed"
$distributedDon = $object->prepare("
    SELECT COUNT(*) AS cnt
    FROM donations d
    WHERE d.donor_id = ?
      AND NOT EXISTS (
          SELECT 1 FROM donation_items di
          WHERE di.donation_id = d.donation_id
          AND di.itemStatus <> 'Distributed'
      )
");
$distributedDon->bind_param("i", $userId);
$distributedDon->execute();
$distCount = $distributedDon->get_result()->fetch_assoc()['cnt'];

$totals = [
    'total'       => (int)$totalCount,
    'pending'     => (int)$pendingCount,
    'distributed' => (int)$distCount
];

//RECENT DONATIONS
$recent = [];
$recentStmt = $object->prepare("
    SELECT donation_id, total_items, donation_date
    FROM donations
    WHERE donor_id = ?
    ORDER BY donation_date DESC
    LIMIT 5
");
$recentStmt->bind_param("i", $userId);
$recentStmt->execute();
$recentRes = $recentStmt->get_result();

while ($r = $recentRes->fetch_assoc()) {
    $r['status'] = computeDonationStatus($object, $r['donation_id']);
    $recent[] = $r;
}

//Critical Needs
$critical = [];
if ($object->query("SHOW TABLES LIKE 'critical_items'")->num_rows > 0) {
    $critStmt = $object->prepare("
        SELECT criticalItem_id, item_name, description, quantity_needed 
        FROM critical_items 
        ORDER BY criticalItem_id DESC 
        LIMIT 5
    ");
    $critStmt->execute();
    $critRes = $critStmt->get_result();
    while ($c = $critRes->fetch_assoc()) {
        $critical[] = $c;
    }
}

//Feedback
$feedback = [];
$fbStmt = $object->prepare("
    SELECT feedback_id, message, created_at,
           staff_reply, staff_reply_by, staff_reply_date,
           admin_reply, admin_reply_by, admin_reply_date
    FROM donor_feedback
    WHERE donor_id = ?
    ORDER BY feedback_id DESC
");
$fbStmt->bind_param("i", $userId);
$fbStmt->execute();
$fbRes = $fbStmt->get_result();

while ($f = $fbRes->fetch_assoc()) {
    $feedback[] = $f;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Donor Dashboard</title>
<link rel="stylesheet" href="DonorMainPage.css?v=1.1">
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
          <li><a href="DonorMainPage.php" class="nav-link active1" data-page="dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li><a href="CriticalPage.php" class="nav-link active2" data-page="critical-needs"><i class="fas fa-heart"></i> Critical Needs</a></li>
          <li><a href="DonForm.php" class="nav-link active3" data-page="donate"><i class="fas fa-hand-holding-usd"></i> Donate</a></li>
          <li><a href="TrackDon.php" class="nav-link active4" data-page="track-donations"><i class="fas fa-history"></i> Track Donations</a></li>
          <li><a href="DonorSettings.php" class="nav-link active5" data-page="settings"><i class="fas fa-cog"></i> Account Settings</a></li>
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
      <div class="page-content" id="Dashboard">
          <header>
              <h1>Dashboard</h1>
              <p>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
          </header>
      </div>

      <!-- Dashboard Grid -->
      <div class="dashboard-grid">
          <!-- Critical Needs Card -->
          <div class="card">
              <div class="card-header">
                  <h3 class="card-title">Critical Needs</h3>
                  <div class="card-icon"><i class="fas fa-exclamation-circle"></i></div>
              </div>
              <ul class="critical-needs-list" style="list-style-type: none;">
                <?php if (count($critical)===0): ?>
                  <li>No critical needs listed.</li>
                <?php else: foreach($critical as $c): ?>
                  <li>
                    <strong><?php echo htmlspecialchars($c['item_name']); ?></strong>
                    <div><?php echo htmlspecialchars($c['description'] ?? ''); ?></div>
                    <div>Needed: <?php echo (int)$c['quantity_needed']; ?></div>
                  </li>
                <?php endforeach; endif; ?>
              </ul>
          </div>

          <!-- Donation Stats Card -->
          <div class="card">
              <div class="card-header">
                  <h3 class="card-title">Donations Made</h3>
                  <div class="card-icon"><i class="fas fa-chart-line"></i></div>
              </div>

              <div class="donation-stats">
                  <div class="stat">
                      <div class="stat-value"><?php echo $totals['total']; ?></div>
                      <div class="stat-label">Total Donations</div>
                  </div>
              </div>
              <div class="donation-note">
                  <p>Pending: <?php echo $totals['pending'];?> Distributed: <?php echo $totals['distributed'];?></p>
              </div>
          </div>

          <!-- Recent Donations Card -->
          <div class="card">
              <div class="card-header">
                  <h3 class="card-title">Recent Donations</h3>
                  <div class="card-icon"><i class="fas fa-history"></i></div>
              </div>
              <div class="recent-donations">
                <?php if (count($recent)===0): ?>
                  <p>No recent donations found.</p>
                <?php else: foreach($recent as $d):?>
                  <div class="donation-item">
                      <div class="donation-info">
                          <h4>Donation Number: <?php echo $d['donation_id']; ?></h4>
                          <p><?php echo date('M j, Y', strtotime($d['donation_date'])); ?></p>
                      </div>
                      <div class="donation-status <?php echo 'status-'.strtolower(str_replace(' ', '-', $d['status'])); ?>">
                          <?php echo htmlspecialchars($d['status']); ?>
                      </div>
                  </div>
                <?php endforeach; endif; ?>
              </div>
          </div>
      </div>

      <!-- CTA Section -->
      <div class="cta-section">
          <h2 class="cta-title">Ready to Make a Difference?</h2>
          <p class="cta-description">Your donation can change lives. Start a new donation today.</p>
          <a class="btn cta-button" id="ctaDonationBtn" href="DonForm.php" style="text-decoration: none;"><i class="fas fa-hand-holding-heart"></i> START NEW DONATION</a>
      </div>

      <!-- Feedback & Reports Section -->
      <div class="action-section">
          <!-- Feedback Card -->
          <div class="card">
            <div class="card-header-feedback">
                <h3 class="card-title">Share Your Feedback</h3>
                <div class="card-icon"><i class="fas fa-comment-alt"></i></div>
            </div>

            <div class="feedback-form">
                <form action="DonorFeedbackProcessor.php" method="POST">

                    <div class="rating">
                        <input type="radio" id="5Star" name="rating" value="5"><label for="5Star">★</label>
                        <input type="radio" id="4Star" name="rating" value="4"><label for="4Star">★</label>
                        <input type="radio" id="3Star" name="rating" value="3"><label for="3Star">★</label>
                        <input type="radio" id="2Star" name="rating" value="2"><label for="2Star">★</label>
                        <input type="radio" id="1Star" name="rating" value="1"><label for="1Star">★</label>
                    </div>

                    <textarea name="message" placeholder="Tell us about your experience..."></textarea>
                    <input type="hidden" name="donor_id" value="<?= $userId; ?>">

                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                </form>
            </div>
        </div>

        <!--Staff/Admin Response Section-->
        <?php
// Fetch ALL feedback made by this donor
$fbStmt = $object->prepare("
    SELECT feedback_id, message, created_at,
        staff_reply, staff_reply_by, staff_reply_date,
        admin_reply, admin_reply_by, admin_reply_date
    FROM donor_feedback
    WHERE donor_id = ?
    ORDER BY feedback_id DESC
");
$fbStmt->bind_param("i", $userId);
$fbStmt->execute();
$feedback = $fbStmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Your Feedback & Responses</h3>
        <div class="card-icon"><i class="fas fa-comments"></i></div>
    </div>
    <div class="">
    <?php if ($feedback->num_rows === 0): ?>
        <p style="padding:12px;">You have not submitted any feedback yet.</p>
    <?php else: ?>
        <?php while ($fb = $feedback->fetch_assoc()): ?>
            <div class="feedback-section">
                <!--FEEDBACK MESSAGE-->
                <p class="feedback-label"><strong>Your Feedback:</strong></p>
                <p class="reply-boxstyle"><?= htmlspecialchars($fb['message']) ?></p>
                <!--STAFF RESPONSE (IF EXISTS)-->
                <?php if (!empty($fb['staff_reply'])): ?>
                    <p style="color: #0DB8A1;"><strong>Staff Reply:</strong></p>
                    <p><?= htmlspecialchars($fb['staff_reply']) ?></p>
                    <small><?= $fb['staff_reply_date'] ?></small>
                <?php endif; ?>
                <!--ADMIN RESPONSE (IF EXISTS)-->
                <?php if (!empty($fb['admin_reply'])): ?>
                    <p style="color: #ff7b00;"><strong>Admin Reply:</strong></p>
                    <p><?= htmlspecialchars($fb['admin_reply']) ?></p>
                    <small><?= $fb['admin_reply_date'] ?></small>
                <?php endif; ?>
                <!--IF NO RESPONSES AT ALL-->
                <?php if (empty($fb['staff_reply']) && empty($fb['admin_reply'])): ?>
                    <p style="color:#aa0000;">
                        <em>Staff and Administrators will respond shortly...</em>
                    </p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
</div>
</div>
</body>
</html>