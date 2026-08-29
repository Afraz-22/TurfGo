<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Dashboard";
$active = "dashboard";
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

// Stats
$total_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()["c"];
$today_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date = CURDATE()")->fetch_assoc()["c"];
$total_revenue = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM bookings WHERE status IN ('confirmed','completed')")->fetch_assoc()["s"];
$active_turfs = $conn->query("SELECT COUNT(*) AS c FROM turfs WHERE status = 'active'")->fetch_assoc()["c"];

$recent = $conn->query(
    "SELECT b.booking_code, t.name AS turf_name, b.booking_date, b.start_time, b.end_time, u.name AS customer, b.status
     FROM bookings b
     JOIN turfs t ON b.turf_id = t.id
     JOIN users u ON b.user_id = u.id
     ORDER BY b.created_at DESC LIMIT 6"
);

include "../includes/head.php";
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Total Bookings</div>
        <div class="value"><?php echo (int)$total_bookings; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Today's Bookings</div>
        <div class="value"><?php echo (int)$today_bookings; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Total Revenue</div>
        <div class="value">৳<?php echo number_format($total_revenue, 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Active Turfs</div>
        <div class="value"><?php echo (int)$active_turfs; ?></div>
    </div>
</div>

<div class="card">
    <div class="card-title">Recent Bookings</div>
    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Turf</th>
            <th>Date</th>
            <th>Time</th>
            <th>Customer</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($recent->num_rows === 0): ?>
            <tr><td colspan="6" class="empty-state">No bookings yet.</td></tr>
        <?php endif; ?>
        <?php while ($row = $recent->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($row["booking_code"]); ?></td>
                <td><?php echo h($row["turf_name"]); ?></td>
                <td><?php echo h(date("d M Y", strtotime($row["booking_date"]))); ?></td>
                <td><?php echo h(date("g:i A", strtotime($row["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($row["end_time"]))); ?></td>
                <td><?php echo h($row["customer"]); ?></td>
                <td><span class="badge badge-<?php echo h($row["status"]); ?>"><?php echo h(ucfirst($row["status"])); ?></span></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
