<?php
session_start();
include "DonRegConnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}

$staffId = (int)$_SESSION['user_id'];

// Fetch staff details
$userStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $staffId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Fetch feedback
$feedbackRes = $object->query("
    SELECT f.*, u.username AS donorName
    FROM donor_feedback f
    JOIN users u ON f.donor_id = u.user_id
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feedback - DIMS Staff</title>
    <link rel="stylesheet" href="StaffFeedback.css">
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
        <li><a href="StaffInventory.php"><i class="fas fa-archive"></i> Inventory</a></li>
        <li><a href="StaffFeedback.php" class="active"><i class="fas fa-history"></i> Feedback</a></li>
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
        <h1>Donor Feedback</h1>
        <p>View and respond to feedback sent by donors.</p>
    </header>
    <div class="feedback-table">
        <table>
            <thead>
                <tr>
                    <th>Donor</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($feedbackRes->num_rows === 0): ?>
                <tr><td colspan="6">No feedback yet.</td></tr>
            <?php else: ?>
                <?php while ($f = $feedbackRes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($f['donorName']) ?></td>
                    <td><?= str_repeat("⭐", (int)$f['rating']) ?></td>
                    <td><?= htmlspecialchars(substr($f['message'], 0, 40)) ?>...</td>
                    <td><?= $f['created_at'] ?></td>
                    <td>
                        <?php if ($f['staff_reply']): ?>
                            <span class="status replied">Replied</span>
                        <?php else: ?>
                            <span class="status pending">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button 
                            class="btn-reply" 
                            data-id="<?= $f['feedback_id'] ?>" 
                            data-message="<?= htmlspecialchars($f['message'], ENT_QUOTES) ?>"
                            data-reply="<?= htmlspecialchars($f['staff_reply'], ENT_QUOTES) ?>"
                        >
                            <?= $f['staff_reply'] ? 'View / Edit' : 'Reply' ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal" id="replyModal">
    <div class="modal-content">
        <h2>Feedback Reply</h2>
        <label>Donor Message:</label>
        <div class="donor-message" id="modalMessage"></div>
        <label>Your Reply:</label>
        <textarea id="replyText"></textarea>
        <button id="saveReplyBtn">Submit Reply</button>
        <button id="closeModal">Cancel</button>
        <div id="msgSuccess" class="success">Reply sent successfully!</div>
    </div>
</div>
<script>
const modal = document.getElementById("replyModal");
const msgBox = document.getElementById("modalMessage");
const replyBox = document.getElementById("replyText");
let currentId = null;
document.querySelectorAll(".btn-reply").forEach(btn => {
    btn.addEventListener("click", function() {
        currentId = this.dataset.id;
        msgBox.innerText = this.dataset.message;
        replyBox.value = this.dataset.reply;

        modal.style.display = "block";
    });
});
document.getElementById("closeModal").onclick = () => modal.style.display = "none";
document.getElementById("saveReplyBtn").onclick = function() {
    fetch("StaffFeedbackReplyProcessor.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "feedback_id=" + currentId + "&reply=" + encodeURIComponent(replyBox.value)
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("msgSuccess").style.display = "block";
        setTimeout(() => location.reload(), 1000);
    });
};
</script>
</body>
</html>