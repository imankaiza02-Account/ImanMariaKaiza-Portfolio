<?php
session_start();
include "DonRegConnection.php";

//Ensuring that the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: LoginPage.php");
    exit;
}
$donor_id = (int)$_SESSION['user_id'];

//Fetching the logged-in donor info
$userStmt = $object->prepare("SELECT username, role FROM users WHERE user_id = ? LIMIT 1");
$userStmt->bind_param("i", $donor_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc() ?? ['username' => 'Donor', 'role' => 'Member'];

//Fetching all critical items with category join
$query= 
"
    SELECT ci.criticalItem_id, ci.item_name, ci.quantity_needed, ci.priority, ci.description,
           c.category_name
    FROM critical_items ci
    LEFT JOIN categories c ON ci.category_id = c.category_id
    ORDER BY c.category_name ASC,
             FIELD(ci.priority, 'Urgent','High','Medium','Low'),
             ci.quantity_needed DESC
";

$result=$object->query($query);

//Grouping by category name
$categories=[];
while ($row=$result->fetch_assoc()) {

    //If category is null, assign "Uncategorized"
    $categoryKey=$row['category_name'] ?? "Uncategorized";

    $categories[$categoryKey][]=$row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical Needs</title>
    <link rel="stylesheet" href="CriticalPage.css">
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
        <li><a href="DonorMainPage.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="CriticalPage.php" class="nav-link active2"><i class="fas fa-heart"></i> Critical Needs</a></li>
        <li><a href="DonForm.php" class="nav-link"><i class="fas fa-hand-holding-usd"></i> Donate</a></li>
        <li><a href="TrackDon.php" class="nav-link"><i class="fas fa-history"></i> Track Donations</a></li>
        <li><a href="DonorSettings.php" class="nav-link"><i class="fas fa-cog"></i> Account Settings</a></li>
        <li><a href="logout.php" class="nav-link"> <i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <div class="user-info">
        <div class="user-avatar">
            <?= strtoupper(substr($user['username'], 0, 2)) ?>
        </div>

        <div class="user-details">
            <h4><?= htmlspecialchars($user['username']) ?></h4>
            <p><?= htmlspecialchars($user['role']) ?></p>
        </div>
    </div>
</div>

<!--Main-->
<div class="main">
    <header>
        <h1>Critical Needs</h1>
        <p>Items Urgently Needed Most</p>
    </header>

    <div class="dashboard-grid">
        <?php foreach ($categories as $categoryName=>$items): ?>
        <div class="card">

            <div class="card-header">
                <h3 class="card-title"><?= htmlspecialchars($categoryName) ?></h3>

                <div class="card-icon">
                    <?php if (stripos($categoryName, "Medical") !== false): ?>
                        <i class="fas fa-briefcase-medical"></i>
                    <?php elseif (stripos($categoryName, "Food") !== false): ?>
                        <i class="fas fa-utensils"></i>
                    <?php elseif (stripos($categoryName, "Education") !== false): ?>
                        <i class="fas fa-book"></i>
                    <?php elseif (stripos($categoryName, "Clothing") !== false): ?>
                        <i class="fas fa-shirt"></i>
                    <?php else: ?>
                        <i class="fas fa-tags"></i>
                    <?php endif; ?>
                </div>
            </div>

            <ul class="critical-needs-list">
                <?php foreach ($items as $item): ?>
                <li>
                    <div class="need-details">
                        <h4><?= htmlspecialchars($item['item_name']) ?></h4>

                        <p>Amount Needed: <?= (int)$item['quantity_needed'] ?></p>

                        <p class="priority <?= strtolower($item['priority']) ?>">
                            PRIORITY: <?= strtoupper($item['priority']) ?>
                        </p>

                        <?php if (!empty($item['description'])): ?>
                            <p style="font-size: 12px; color: #555; margin-top: 5px;">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>