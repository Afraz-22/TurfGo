<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Turf & Pricing";
$active = "turf-pricing";
$theme = "admin";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "turf-pricing", "href" => "turf-pricing.php", "icon" => "🏟️", "label" => "Turf & Pricing"],
    ["key" => "staff-users", "href" => "staff-users.php", "icon" => "👥", "label" => "Staff & Users"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "payments", "href" => "payments.php", "icon" => "💳", "label" => "Payments"],
    ["key" => "reports", "href" => "reports.php", "icon" => "📈", "label" => "Reports"],
    ["key" => "settings", "href" => "settings.php", "icon" => "⚙️", "label" => "Settings"],
];


$error = "";
$success = "";

// Add a new turf
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_turf"])) {
    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $price = (float)$_POST["price"];

    $stmt = $conn->prepare("INSERT INTO turfs (name, location, price_per_hour, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param("ssd", $name, $location, $price);
    if ($stmt->execute()) {
        $success = "Turf added successfully.";
    } else {
        $error = "Could not add turf.";
    }
    $stmt->close();
}


// Toggle turf status
if (isset($_GET["toggle"])) {
    $id = (int)$_GET["toggle"];
    $conn->query("UPDATE turfs SET status = IF(status='active','inactive','active') WHERE id = $id");
    header("Location: turf-pricing.php");
    exit();
}

// Delete turf
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $conn->query("DELETE FROM turfs WHERE id = $id");
    header("Location: turf-pricing.php");
    exit();
}

$turfs = $conn->query("SELECT * FROM turfs ORDER BY created_at DESC");

include "../includes/head.php";
?>

<div class="page-header">
    <h1>All Turfs</h1>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="card">
    <div class="card-title">Add New Turf</div>
    <form method="POST" action="turf-pricing.php" class="filter-bar">
        <input type="text" name="name" placeholder="Turf name" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="number" name="price" placeholder="Price per hour" step="0.01" required>
        <button type="submit" name="add_turf" class="btn">+ Add Turf</button>
    </form>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Turf Name</th>
            <th>Price (per hour)</th>
            <th>Location</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($turfs->num_rows === 0): ?>
            <tr><td colspan="5" class="empty-state">No turfs added yet.</td></tr>
        <?php endif; ?>
        <?php while ($t = $turfs->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($t["name"]); ?></td>
                <td>৳<?php echo number_format($t["price_per_hour"], 0); ?></td>
                <td><?php echo h($t["location"]); ?></td>
                <td><span class="badge badge-<?php echo h($t["status"]); ?>"><?php echo h(ucfirst($t["status"])); ?></span></td>
                <td>
                    <a class="icon-btn" href="turf-pricing.php?toggle=<?php echo (int)$t['id']; ?>" title="Toggle status">🔁</a>
                    <a class="icon-btn" href="turf-pricing.php?delete=<?php echo (int)$t['id']; ?>" title="Delete" data-confirm="Delete this turf?">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
