<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Check-in";
$active = "check-in";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$booking = null;
$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";

if ($query !== "") {
    $like = "%" . $query . "%";
    $stmt = $conn->prepare(
        "SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.status,
                t.name AS turf_name, u.name AS customer, u.email
         FROM bookings b
         JOIN turfs t ON b.turf_id = t.id
         JOIN users u ON b.user_id = u.id
         WHERE b.booking_code LIKE ? OR u.name LIKE ?
         ORDER BY b.booking_date DESC LIMIT 1"
    );
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Perform check-in (mark booking completed)
if (isset($_POST["checkin_id"])) {
    $id = (int)$_POST["checkin_id"];
    $conn->query("UPDATE bookings SET status = 'completed' WHERE id = $id");
    header("Location: check-in.php?q=" . urlencode($query));
    exit();
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Check-in</h1>
</div>

<div class="card">
    <form method="GET" action="check-in.php" class="filter-bar">
        <input type="text" name="q" placeholder="Search booking ID or name..." value="<?php echo h($query); ?>" style="flex:1;">
        <button type="submit" class="btn">Search</button>
    </form>
</div>

<?php if ($query !== "" && !$booking): ?>
    <div class="card empty-state">No matching booking found.</div>
<?php elseif ($booking): ?>
    <div class="card">
        <div class="card-title">Booking ID: <?php echo h($booking["booking_code"]); ?></div>
        <table>
            <tr><th>Customer</th><td><?php echo h($booking["customer"]); ?></td></tr>
            <tr><th>Turf</th><td><?php echo h($booking["turf_name"]); ?></td></tr>
            <tr><th>Date</th><td><?php echo h(date("d M Y", strtotime($booking["booking_date"]))); ?></td></tr>
            <tr><th>Time</th><td><?php echo h(date("g:i A", strtotime($booking["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($booking["end_time"]))); ?></td></tr>
            <tr><th>Status</th><td><span class="badge badge-<?php echo h($booking["status"]); ?>"><?php echo h(ucfirst($booking["status"])); ?></span></td></tr>
        </table>

        <?php if ($booking["status"] !== "completed" && $booking["status"] !== "cancelled"): ?>
            <form method="POST" action="check-in.php" style="margin-top:16px;">
                <input type="hidden" name="checkin_id" value="<?php echo (int)$booking['id']; ?>">
                <button type="submit" class="btn">Check-in Customer</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include "../includes/foot.php"; ?>
