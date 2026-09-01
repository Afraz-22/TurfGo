<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Dashboard";
$active = "dashboard";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$today_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date = CURDATE()")->fetch_assoc()["c"];
$upcoming = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date > CURDATE() AND status = 'confirmed'")->fetch_assoc()["c"];
$checked_in = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date = CURDATE() AND status = 'completed'")->fetch_assoc()["c"];
$cancelled = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date = CURDATE() AND status = 'cancelled'")->fetch_assoc()["c"];

$todays = $conn->query(
    "SELECT b.start_time, b.end_time, t.name AS turf_name, u.name AS customer, b.status
     FROM bookings b
     JOIN turfs t ON b.turf_id = t.id
     JOIN users u ON b.user_id = u.id
     WHERE b.booking_date = CURDATE()
     ORDER BY b.start_time ASC"
);

include "../includes/head.php";
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Today's Bookings</div>
        <div class="value"><?php echo (int)$today_bookings; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Upcoming Bookings</div>
        <div class="value"><?php echo (int)$upcoming; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Checked-in Today</div>
        <div class="value"><?php echo (int)$checked_in; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Cancelled Today</div>
        <div class="value"><?php echo (int)$cancelled; ?></div>
    </div>
</div>

<div class="card">
    <div class="card-title">Today's Bookings</div>
    <table>
        <thead>
        <tr>
            <th>Time</th>
            <th>Turf</th>
            <th>Customer</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($todays->num_rows === 0): ?>
            <tr><td colspan="4" class="empty-state">No bookings scheduled for today.</td></tr>
        <?php endif; ?>
        <?php while ($row = $todays->fetch_assoc()): ?>
            <tr>
                <td><?php echo h(date("g:i A", strtotime($row["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($row["end_time"]))); ?></td>
                <td><?php echo h($row["turf_name"]); ?></td>
                <td><?php echo h($row["customer"]); ?></td>
                <td><span class="badge badge-<?php echo h($row["status"]); ?>"><?php echo h(ucfirst($row["status"])); ?></span></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
