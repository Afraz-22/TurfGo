<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Manage Slots";
$active = "manage-slots";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$turfs = $conn->query("SELECT * FROM turfs WHERE status = 'active' ORDER BY name");

$selected_turf = isset($_GET["turf"]) ? (int)$_GET["turf"] : 0;
$selected_date = isset($_GET["date"]) ? $_GET["date"] : date("Y-m-d");

if (!$selected_turf) {
    $first = $conn->query("SELECT id FROM turfs WHERE status = 'active' ORDER BY name LIMIT 1")->fetch_assoc();
    $selected_turf = $first ? (int)$first["id"] : 0;
}

// Fixed one-hour slots from 6 AM to 10 PM
$slot_hours = range(6, 21);

// Get booked slots for the selected turf/date
$booked_times = [];
if ($selected_turf) {
    $stmt = $conn->prepare("SELECT start_time, status FROM bookings WHERE turf_id = ? AND booking_date = ? AND status != 'cancelled'");
    $stmt->bind_param("is", $selected_turf, $selected_date);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $booked_times[date("H:i", strtotime($row["start_time"]))] = $row["status"];
    }
    $stmt->close();
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Manage Slots</h1>
</div>

<form method="GET" action="manage-slots.php" class="filter-bar">
    <select name="turf" onchange="this.form.submit()">
        <?php $turfs->data_seek(0); while ($t = $turfs->fetch_assoc()): ?>
            <option value="<?php echo (int)$t['id']; ?>" <?php echo $t['id'] == $selected_turf ? 'selected' : ''; ?>>
                <?php echo h($t['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <input type="date" name="date" value="<?php echo h($selected_date); ?>" onchange="this.form.submit()">
</form>

<div class="card">
    <div class="card-title">Slot Availability</div>
    <div class="slot-grid">
        <?php foreach ($slot_hours as $hour):
            $start = sprintf("%02d:00", $hour);
            $end_hour = ($hour + 1) % 24;
            $end = sprintf("%02d:00", $end_hour);
            $is_booked = isset($booked_times[$start]);
            $status_label = $is_booked ? ucfirst($booked_times[$start]) : "Available";
            $slot_class = $is_booked ? "slot-booked" : "slot-available";
        ?>
            <div class="slot <?php echo $slot_class; ?>">
                <div class="time"><?php echo h(date("g:i A", strtotime($start))); ?> - <?php echo h(date("g:i A", strtotime($end))); ?></div>
                <div><?php echo h($status_label); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "../includes/foot.php"; ?>
