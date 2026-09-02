<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Schedule";
$active = "schedule";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$selected_date = isset($_GET["date"]) ? $_GET["date"] : date("Y-m-d");

$stmt = $conn->prepare(
    "SELECT t.name AS turf_name, b.start_time, b.end_time, u.name AS customer, b.status
     FROM bookings b
     JOIN turfs t ON b.turf_id = t.id
     JOIN users u ON b.user_id = u.id
     WHERE b.booking_date = ?
     ORDER BY t.name, b.start_time"
);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$schedule = $stmt->get_result();

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Daily Schedule</h1>
</div>

<form method="GET" action="schedule.php" class="filter-bar">
    <input type="date" name="date" value="<?php echo h($selected_date); ?>" onchange="this.form.submit()">
</form>

<div class="card">
    <div class="card-title">Facility Schedule - <?php echo h(date("d M Y", strtotime($selected_date))); ?></div>
    <table>
        <thead>
        <tr>
            <th>Turf</th>
            <th>Time</th>
            <th>Customer</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($schedule->num_rows === 0): ?>
            <tr><td colspan="4" class="empty-state">No matches scheduled for this date.</td></tr>
        <?php endif; ?>
        <?php while ($row = $schedule->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($row["turf_name"]); ?></td>
                <td><?php echo h(date("g:i A", strtotime($row["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($row["end_time"]))); ?></td>
                <td><?php echo h($row["customer"]); ?></td>
                <td><span class="badge badge-<?php echo h($row["status"]); ?>"><?php echo h(ucfirst($row["status"])); ?></span></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
