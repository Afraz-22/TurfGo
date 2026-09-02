<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Bookings";
$active = "bookings";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

// Confirm a pending booking
if (isset($_GET["confirm"])) {
    $id = (int)$_GET["confirm"];
    $conn->query("UPDATE bookings SET status = 'confirmed' WHERE id = $id");
    header("Location: bookings.php");
    exit();
}

// Cancel a booking
if (isset($_GET["cancel"])) {
    $id = (int)$_GET["cancel"];
    $conn->query("UPDATE bookings SET status = 'cancelled' WHERE id = $id");
    header("Location: bookings.php");
    exit();
}

$filter = isset($_GET["status"]) ? $_GET["status"] : "all";

$sql = "SELECT b.id, b.booking_code, t.name AS turf_name, b.booking_date, b.start_time, b.end_time, u.name AS customer, b.status
        FROM bookings b
        JOIN turfs t ON b.turf_id = t.id
        JOIN users u ON b.user_id = u.id";
if ($filter !== "all") {
    $sql .= " WHERE b.status = '" . $conn->real_escape_string($filter) . "'";
}
$sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";

$bookings = $conn->query($sql);

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Bookings</h1>
</div>

<div class="tab-bar">
    <a href="bookings.php?status=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
    <a href="bookings.php?status=confirmed" class="<?php echo $filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
    <a href="bookings.php?status=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
    <a href="bookings.php?status=cancelled" class="<?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Turf</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($bookings->num_rows === 0): ?>
            <tr><td colspan="6" class="empty-state">No bookings found.</td></tr>
        <?php endif; ?>
        <?php while ($b = $bookings->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($b["booking_code"]); ?></td>
                <td><?php echo h($b["turf_name"]); ?></td>
                <td><?php echo h(date("d M Y", strtotime($b["booking_date"]))); ?></td>
                <td><?php echo h(date("g:i A", strtotime($b["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($b["end_time"]))); ?></td>
                <td><span class="badge badge-<?php echo h($b["status"]); ?>"><?php echo h(ucfirst($b["status"])); ?></span></td>
                <td>
                    <?php if ($b["status"] === "pending"): ?>
                        <a class="icon-btn" href="bookings.php?confirm=<?php echo (int)$b['id']; ?>" title="Confirm">✅</a>
                    <?php endif; ?>
                    <?php if ($b["status"] !== "cancelled"): ?>
                        <a class="icon-btn" href="bookings.php?cancel=<?php echo (int)$b['id']; ?>" title="Cancel" data-confirm="Cancel this booking?">❌</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
