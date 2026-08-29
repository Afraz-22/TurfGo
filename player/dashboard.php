<?php
require_once "../includes/auth.php";
require_role("player");

$page_title = "Dashboard";
$active = "dashboard";
$theme = "player";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "book-turf", "href" => "book-turf.php", "icon" => "🏟️", "label" => "Book Turf"],
    ["key" => "my-bookings", "href" => "my-bookings.php", "icon" => "📅", "label" => "My Bookings"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT b.booking_code, t.name AS turf_name, b.booking_date, b.start_time, b.end_time
     FROM bookings b
     JOIN turfs t ON b.turf_id = t.id
     WHERE b.user_id = ? AND b.status = 'confirmed' AND b.booking_date >= CURDATE()
     ORDER BY b.booking_date ASC LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_assoc();
$stmt->close();

include "../includes/head.php";
?>

<div class="card">
    <div class="card-title">Hello, <?php echo h($_SESSION["name"]); ?>! ⚽</div>
    <p style="color:var(--text-muted); font-size:14px;">Welcome back to TurfGo.</p>
</div>

<div class="card">
    <div class="card-title">Upcoming Booking</div>
    <?php if ($upcoming): ?>
        <p><strong><?php echo h($upcoming["turf_name"]); ?></strong></p>
        <p style="color:var(--text-muted); font-size:13px; margin:6px 0;">
            <?php echo h(date("d M Y", strtotime($upcoming["booking_date"]))); ?> ·
            <?php echo h(date("g:i A", strtotime($upcoming["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($upcoming["end_time"]))); ?>
        </p>
        <p style="color:var(--text-muted); font-size:13px; margin-bottom:14px;">Booking ID: <?php echo h($upcoming["booking_code"]); ?></p>
        <a href="my-bookings.php" class="btn">View Details</a>
    <?php else: ?>
        <p class="empty-state">You have no upcoming bookings.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-title">Quick Actions</div>
    <a href="book-turf.php" class="btn" style="margin-right:10px;">Book a Turf</a>
    <a href="my-bookings.php" class="btn btn-outline">My Bookings</a>
</div>

<?php include "../includes/foot.php"; ?>
