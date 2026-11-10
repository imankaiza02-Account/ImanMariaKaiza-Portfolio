<?php
session_start();
include "DonRegConnection.php";

//Authorisation of users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: LoginPage.php");
    exit;
}

$meId = (int)$_SESSION['user_id'];

//Fetch pending staff/admin (approvalStatus = 'Pending' and role in Staff/Admin)
$pendingSql = "
    SELECT user_id, username, email, role, contactDetails, dateJoined, approvalStatus
    FROM users
    WHERE approvalStatus = 'Pending' AND role IN ('Staff','Admin')
    ORDER BY dateJoined DESC
";
$pendingRes = $object->query($pendingSql);

//Fetch staff & admin users
$saSql = "
    SELECT user_id, username, email, role, contactDetails, approvalStatus, dateJoined
    FROM users
    WHERE role IN ('Staff','Admin')
    ORDER BY dateJoined DESC
";
$saRes = $object->query($saSql);

//Fetch donors
$donorSql = "
    SELECT user_id, username, email, contactDetails, approvalStatus, dateJoined
    FROM users
    WHERE role = 'Donor'
    ORDER BY dateJoined DESC
";
$donorRes = $object->query($donorSql);

//Count admins, so that can be used to ensure at least one admin is there
function countAdmins($object) {
    $r = $object->query("SELECT COUNT(*) as count FROM users WHERE role='Admin'")->fetch_assoc();
    return (int)$r['count'];
}
$adminCount = countAdmins($object);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="UserManage.css">
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
        <li><a href="AdminHome.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="AdManageUser.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="AdInven.php"><i class="fas fa-box"></i> Inventory</a></li>
        <li><a href="AdminFeedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
        <li><a href="AdminSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2)) ?></div>
        <div class="user-details">
            <h4><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></h4>
            <p><?= htmlspecialchars($_SESSION['role'] ?? 'Admin') ?></p>
        </div>
    </div>
</div>

<div class="main">
    <header>
        <h1>User Management</h1>
        <p>Approve, change roles, or remove users.</p>
    </header>

    <section class="card">
        <h2>Pending Staff/Admin Registrations</h2>
        <?php if ($pendingRes && $pendingRes->num_rows): ?>
            <table>
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $pendingRes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><?= htmlspecialchars($row['contactDetails']) ?></td>
                        <td><?= htmlspecialchars($row['dateJoined']) ?></td>
                        <td class="action-buttons">
                            <form method="post" action="approveUser.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                <button class="btn btn-success" type="submit">Approve</button>
                            </form>
                            <form method="post" action="rejectUser.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>">
                                <button class="btn btn-danger" type="submit">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending requests.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Staff & Administrators</h2>
        <div class="table-actions">
            <a class="btn btn-outline" href="GenerateS&APDF.php" target="_blank"><i class="fas fa-file-pdf"></i> Export Staff & Admin PDF</a>
            <a class="btn btn-outline" href="GenerateAllUsersPDF.php" target="_blank"><i class="fas fa-file-pdf"></i> Export All Users PDF</a>
        </div>
        <table>
            <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Approval</th>
                <th>Phone</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($u = $saRes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['approvalStatus']) ?></td>
                    <td><?= htmlspecialchars($u['contactDetails']) ?></td>
                    <td><?= htmlspecialchars($u['dateJoined']) ?></td>
                    <td class="action-buttons">
                        <!-- change role -->
                        <?php if ($u['user_id'] != $meId): ?>
                            <form method="post" action="updateRole.php" style="display:inline">
                                <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                <select name="new_role" class="role-select" onchange="this.form.submit()">
                                    <option value="Staff" <?= $u['role']=='Staff' ? 'selected' : '' ?>>Staff</option>
                                    <option value="Admin" <?= $u['role']=='Admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="note">You</span>
                        <?php endif; ?>

                        <!-- delete -->
                        <?php if ($u['user_id'] != $meId): ?>
                            <form method="post" action="deleteUser.php" style="display:inline" onsubmit="return confirm('Delete this user? This action cannot be undone.')">
                                <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Donors</h2>
        <div class="table-actions">
            <a class="btn btn-outline" href="GenerateDPDF.php" target="_blank"><i class="fas fa-file-pdf"></i> Export Donors PDF</a>
        </div>
        <table>
            <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Approval</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($d = $donorRes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($d['username']) ?></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= htmlspecialchars($d['contactDetails']) ?></td>
                    <td><?= htmlspecialchars($d['approvalStatus']) ?></td>
                    <td><?= htmlspecialchars($d['dateJoined']) ?></td>
                    <td class="action-buttons">
                        <form method="post" action="deleteUser.php" style="display:inline" onsubmit="return confirm('Delete this donor?')">
                            <input type="hidden" name="user_id" value="<?= (int)$d['user_id'] ?>">
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>