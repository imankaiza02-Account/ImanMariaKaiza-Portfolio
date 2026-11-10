<?php
session_start();
include "DonRegConnection.php";

//Only admins may access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: LoginPage.php");
    exit;
}

$adminId = (int)$_SESSION['user_id'];

//Fetch admin user info for sidebar
$stmtUser = $object->prepare("SELECT username, role FROM users WHERE user_id = ?");
$stmtUser->bind_param("i", $adminId);
$stmtUser->execute();
$admin = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

//Fetch feedback along with donor username, staff/admin replier names (if any)
$feedbackSql = "
    SELECT f.*,
           d.username AS donorName,
           s.username AS staffReplyByName,
           a.username AS adminReplyByName
    FROM donor_feedback f
    JOIN users d ON f.donor_id = d.user_id
    LEFT JOIN users s ON f.staff_reply_by = s.user_id
    LEFT JOIN users a ON f.admin_reply_by = a.user_id
    ORDER BY f.created_at DESC
";
$feedbackRes = $object->query($feedbackSql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Donor Feedback | DIMS</title>
    <link rel="stylesheet" href="AdminFeedback.css?v=1.1">
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
        <li><a href="AdInven.php"><i class="fas fa-box"></i> Inventory</a></li>
        <li><a href="AdminFeedback.php" class="active"><i class="fas fa-comments"></i> Feedback</a></li>
        <li><a href="AdminSettings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="user-info">
        <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($admin['username'] ?? 'AD', 0, 2))) ?></div>
        <div class="user-details">
            <h4><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></h4>
            <p><?= htmlspecialchars($admin['role'] ?? 'Admin') ?></p>
        </div>
    </div>
</div>

<div class="main">
    <header>
        <h1>Donor Feedback</h1>
        <p>View donor feedback, read staff replies, and add admin replies.</p>
    </header>

    <div class="feedback-table">
        <table>
            <thead>
                <tr>
                    <th>Donor</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Staff Reply</th>
                    <th>Admin Reply</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$feedbackRes || $feedbackRes->num_rows === 0): ?>
                <tr><td colspan="7">No feedback yet.</td></tr>
            <?php else: ?>
                <?php while ($f = $feedbackRes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($f['donorName']) ?></td>
                    <td><?= str_repeat("⭐", (int)$f['rating']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($f['message'], 0, 60, '...')) ?></td>
                    <td><?= htmlspecialchars($f['created_at']) ?></td>

                    <td>
                        <?php if ($f['staff_reply']): ?>
                            <div class="small-note"><?= htmlspecialchars(mb_strimwidth($f['staff_reply'], 0, 80, '...')) ?></div>
                            <div class="meta">By: <?= htmlspecialchars($f['staffReplyByName'] ?? 'Staff') ?></div>
                        <?php else: ?>
                            <span class="status pending">No staff reply</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($f['admin_reply']): ?>
                            <div class="small-note"><?= htmlspecialchars(mb_strimwidth($f['admin_reply'], 0, 80, '...')) ?></div>
                            <div class="meta">By: <?= htmlspecialchars($f['adminReplyByName'] ?? 'Admin') ?></div>
                        <?php else: ?>
                            <span class="status pending">No admin reply</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($f['admin_reply']): ?>
                            <button class="btn-edit"
                                data-id="<?= (int)$f['feedback_id'] ?>"
                                data-message="<?= htmlspecialchars($f['message'], ENT_QUOTES) ?>"
                                data-staff="<?= htmlspecialchars($f['staff_reply'] ?? '', ENT_QUOTES) ?>"
                                data-admin="<?= htmlspecialchars($f['admin_reply'] ?? '', ENT_QUOTES) ?>"
                            >View / Edit</button>
                        <?php else: ?>
                            <button class="btn-reply" 
                                data-id="<?= (int)$f['feedback_id'] ?>"
                                data-message="<?= htmlspecialchars($f['message'], ENT_QUOTES) ?>"
                                data-staff="<?= htmlspecialchars($f['staff_reply'] ?? '', ENT_QUOTES) ?>"
                            >Reply</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="replyModal">
    <div class="modal-content">
        <h2>Feedback</h2>

        <label>Donor Message:</label>
        <div class="donor-message" id="modalMessage"></div>

        <label>Staff Reply (read-only):</label>
        <div class="staff-message" id="modalStaff"></div>

        <label id="adminLabel">Your Reply (Admin):</label>
        <textarea id="adminReply" placeholder="Write your reply here..."></textarea>

        <div class="modal-actions">
            <button id="saveReplyBtn" class="btn-primary">Submit Reply</button>
            <button id="closeModal" class="btn-secondary">Close</button>
        </div>

        <div id="msgSuccess" class="success">Reply sent successfully!</div>
        <div id="msgError" class="error">Could not send reply.</div>
        <div id="msgCannotEdit" class="error" style="display:none;">Admin reply already exists and cannot be edited</div>
    </div>
</div>

<script>
const modal = document.getElementById('replyModal');
const modalMsg = document.getElementById('modalMessage');
const modalStaff = document.getElementById('modalStaff');
const adminReply = document.getElementById('adminReply');
const saveBtn = document.getElementById('saveReplyBtn');
const closeBtn = document.getElementById('closeModal');
const msgSuccess = document.getElementById('msgSuccess');
const msgError = document.getElementById('msgError');
const msgCannotEdit = document.getElementById('msgCannotEdit');

let currentId = null;

function showReplyModal(id, message, staffReply, adminReplyValue, isEditable) {
    currentId = id;
    modalMsg.innerText = message || '';
    modalStaff.innerText = staffReply || 'No staff reply yet.';
    adminReply.value = adminReplyValue || '';
    
    msgSuccess.style.display = 'none';
    msgError.style.display = 'none';
    msgCannotEdit.style.display = 'none';

    if (isEditable) {
        adminReply.removeAttribute('readonly');
        saveBtn.style.display = 'inline-block';
        document.getElementById('adminLabel').innerText = 'Your Reply (Admin):';
    } else {
        adminReply.setAttribute('readonly', 'readonly');
        saveBtn.style.display = 'none';
        document.getElementById('adminLabel').innerText = 'Admin Reply:';
    }
    modal.style.display = 'flex';
}

document.querySelectorAll('.btn-reply').forEach(btn => {
    btn.addEventListener('click', () => {
        showReplyModal(
            btn.dataset.id,
            btn.dataset.message,
            btn.dataset.staff,
            '',
            true
        );
    });
});

document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        showReplyModal(
            btn.dataset.id,
            btn.dataset.message,
            btn.dataset.staff,
            btn.dataset.admin,
            true
        );
    });
});

closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

saveBtn.onclick = () => {
    if (!currentId) return;
    const payload = new URLSearchParams();
    payload.append('feedback_id', currentId);
    payload.append('reply', adminReply.value);

    msgSuccess.style.display = 'none';
    msgError.style.display = 'none';
    msgCannotEdit.style.display = 'none';

    fetch('AdminFeedbackProcessor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload.toString()
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.ok) {
            msgSuccess.style.display = 'block';
            setTimeout(() => location.reload(), 900);
        } else {
            if (resp.error && resp.error.includes('already exists and cannot be edited')) {
                msgCannotEdit.style.display = 'block';
            } else {
                msgError.innerText = resp.error || 'Failed';
                msgError.style.display = 'block';
            }
        }
    })
    .catch(err => {
        msgError.innerText = 'Network error';
        msgError.style.display = 'block';
    });
};
</script>
</body>
</html>