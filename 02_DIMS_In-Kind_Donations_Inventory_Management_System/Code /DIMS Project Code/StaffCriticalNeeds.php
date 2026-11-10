<?php
session_start();
include 'DonRegConnection.php';

// staff-only guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: LoginPage.php");
    exit;
}
$userId = (int)$_SESSION['user_id'];
$userStmt = $object->prepare("SELECT role, username FROM users WHERE user_id = ? LIMIT 1");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
if (!$user || $user['role'] !== 'Staff') {
    header('Location: LoginPage.php'); 
    exit;
}

$success = '';
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// DELETING THE CRITICAL ITEM IN THE EXISITNG LIST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_critical') {
    $delId = (int)$_POST['delete_id'];

    $del = $object->prepare("DELETE FROM critical_items WHERE criticalItem_id = ?");
    $del->bind_param("i", $delId);
    $del->execute();

    $_SESSION['success'] = "Critical need deleted successfully."; 
    header("Location: StaffCriticalNeeds.php"); 
    exit;
}

// Handle POST: add or edit critical item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_critical') {
    $item_name = trim($_POST['item_name'] ?? '');
    $quantity_needed = (int)($_POST['quantity_needed'] ?? 0);
    $priority = in_array($_POST['priority'] ?? '', ['Urgent','High','Medium','Low']) ? $_POST['priority'] : 'Medium';
    $category_id = (!empty($_POST['category_id']) && $_POST['category_id'] !== 'null') ? (int)$_POST['category_id'] : null;
    $desc = trim($_POST['description'] ?? '');

    if ($item_name === '' || $quantity_needed <= 0) {
        $error = "Please provide item name and a quantity > 0.";
    } else {
        $ins = $object->prepare("INSERT INTO critical_items (category_id, item_name, description, quantity_needed, priority) VALUES (?, ?, ?, ?, ?)");
        // bind_param requires a type for nullable integer. Use 'i' and pass null as null -> use special handling:
        if ($category_id === null) {
            $ins->bind_param("issis", $nullCat = $category_id, $item_name, $desc, $quantity_needed, $priority);
        } else {
            $ins->bind_param("issis", $category_id, $item_name, $desc, $quantity_needed, $priority);
        }
        if ($ins->execute()) {
            $_SESSION['success'] = "Critical need added."; 
            header("Location: StaffCriticalNeeds.php"); 
            exit;
        } else {
            $error = "Database error: " . $object->error;
        }
    }
}

// Fetch categories grouped by perishable flag
$catsRes = $object->query("SELECT category_id, category_name, is_perishable FROM categories ORDER BY is_perishable DESC, category_name ASC");
$perishableCats = [];
$nonPerishableCats = [];
while ($c = $catsRes->fetch_assoc()) {
    if ((int)$c['is_perishable'] === 1) $perishableCats[] = $c;
    else $nonPerishableCats[] = $c;
}

// Filter: optionally show perishable/non perishable
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';
$where = "";
if ($filter === 'perishable') $where = "WHERE c.is_perishable = 1";
elseif ($filter === 'non') $where = "WHERE c.is_perishable = 0";

$sql = "
SELECT ci.criticalItem_id, ci.item_name, ci.quantity_needed, ci.priority, ci.created_at, c.category_name
FROM critical_items ci
LEFT JOIN categories c ON c.category_id = ci.category_id
{$where}
ORDER BY FIELD(ci.priority,'Urgent','High','Medium','Low'), ci.created_at DESC
";
$items = $object->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Critical Needs</title>
    <link rel="stylesheet" href="StaffCNStyling.css">
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
            <li><a href="StaffDash.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="StaffCriticalNeeds.php" class="active"><i class="fas fa-heart"></i> Critical Needs</a></li>
            <li><a href="StaffDonations.php"><i class="fas fa-hand-holding-usd"></i> Manage Donations</a></li>
            <li><a href="StaffInventory.php"><i class="fas fa-archive"></i> Inventory</a></li>
            <li><a href="StaffFeedback.php"><i class="fas fa-history"></i> Feedback</a></li>
            <li><a href="StaffSettings.php"><i class="fas fa-cog"></i> Account Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr(trim($user['username']),0,2)) ?></div>
            <div class="user-details">
                <h4><?= htmlspecialchars($user['username']) ?></h4>
                <p><?= htmlspecialchars($user['role']) ?></p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
    <header>
        <h1>Critical Needs</h1>
        <p>Determine The Items Needed The Most</p>
    </header>

    <?php if (!empty($error)): ?><div class="panel" style="border-left:4px solid #ff6b6b;color:#a11;margin-bottom:10px;"><?=htmlspecialchars($error)?></div><?php endif;?>
    <?php if (!empty($success)): ?><div id="success-msg" class="panel" style="border-left:4px solid #0DB8A1;color:#064;margin-bottom:10px;"><?=htmlspecialchars($success)?></div><?php endif; ?>

    <form method="POST" class="filter-form">
        <label>Filter: </label>
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?= $filter==='all' ? 'selected':'' ?>>All</option>
            <option value="perishable" <?= $filter==='perishable' ? 'selected':'' ?>>Perishable</option>
            <option value="non" <?= $filter==='non' ? 'selected':'' ?>>Non-perishable</option>
        </select>
    </form>

    <div class="content-grid">
        <!-- Left: list -->
        <div class="needs-list">
            <h3>Existing Critical Needs</h3>
            <?php if ($items->num_rows === 0): ?>
                <p>No critical items found for this filter.</p>
            <?php else: while ($it = $items->fetch_assoc()): ?>
                <div class="panel need-item">
                    <div class="need-item-content">
                        <div>
                            <h4><?=htmlspecialchars($it['item_name'])?></h4>
                            <div><span class="chip"><?=htmlspecialchars($it['category_name'] ?: 'Uncategorized')?></span>
                                <span class="priority <?=strtolower($it['priority'])?>"><?=htmlspecialchars($it['priority'])?></span></div>
                        </div>
                        <div style="text-align:right">
                            <div>Needed: <strong><?= (int)$it['quantity_needed'] ?></strong></div>
                            <div style="font-size:12px;color:#666;margin-top:6px"><?=date('M j, Y', strtotime($it['created_at']))?></div>
                            <!--DELETE BUTTON-->
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="action" value="delete_critical">
                                <input type="hidden" name="delete_id" value="<?= $it['criticalItem_id'] ?>">
                                <button class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; endif; ?>
        </div>

        <!-- Right: add new -->
        <div class="add-form">
            <h3>Add Critical Need</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_critical">
                <div class="form-group">
                    <label>Perishable / Non-perishable</label>
                    <select id="perishSelect" required>
                        <option value="1">Perishable</option>
                        <option value="0">Non-perishable</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select id="categorySelect" name="category_id">
                        <option value="null">-- Select Type --</option>
                        <!--options filled by JS-->
                    </select>
                </div>

                <div class="form-group">
                    <label>Item name</label>
                    <input type="text" name="item_name" id="item-name" required>
                </div>

                <div class="form-group">
                    <label>Quantity needed</label>
                    <input type="number" name="quantity_needed" min="1" required>
                </div>

                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option>Urgent</option>
                        <option>High</option>
                        <option selected>Medium</option>
                        <option>Low</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description (optional)</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Add Critical Need</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Hide success message after 1 second
const msg = document.getElementById('success-msg');
if (msg) {
    setTimeout(() => {
        msg.style.display = 'none';
    }, 1000);
}
</script>
<script>
const itemName = document.getElementById('item-name');
//Item name restriction: allow only letters and numbers
itemName.addEventListener('input', function(event) {
    event.target.value = event.target.value.replace(/[^a-zA-Z0-9]/g, '');
});
</script>
<script>
    // Pass PHP arrays to JS: two arrays of categories
    const perishableCats = <?= json_encode($perishableCats) ?>;
    const nonPerishableCats = <?= json_encode($nonPerishableCats) ?>;

    const perishSelect = document.getElementById('perishSelect');
    const categorySelect = document.getElementById('categorySelect');

    function fillCategoryOptions() {
        const isPerishable = perishSelect.value === '1';
        const list = isPerishable ? perishableCats : nonPerishableCats;
        categorySelect.innerHTML = '<option value=\"null\">-- Select Type --</option>';
        list.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.category_id;
            opt.textContent = c.category_name;
            categorySelect.appendChild(opt);
        });
    }
    // initial fill
    fillCategoryOptions();
    perishSelect.addEventListener('change', fillCategoryOptions);
</script>
</body>
</html>