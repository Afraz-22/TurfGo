<?php
require_once "../includes/auth.php";
require_role("player");

$page_title = "My Bookings";
$active = "my-bookings";
$theme = "player";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "book-turf", "href" => "book-turf.php", "icon" => "🏟️", "label" => "Book Turf"],
    ["key" => "my-bookings", "href" => "my-bookings.php", "icon" => "📅", "label" => "My Bookings"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$user_id = $_SESSION["user_id"];

// Cancel a booking (only the owner can cancel their own booking)
if (isset($_GET["cancel"])) {
    $id = (int)$_GET["cancel"];
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: my-bookings.php");
    exit();
}

$filter = isset($_GET["status"]) ? $_GET["status"] : "all";

$sql = "SELECT b.id, b.booking_code, t.name AS turf_name, b.booking_date, b.start_time, b.end_time, b.status
        FROM bookings b
        JOIN turfs t ON b.turf_id = t.id
        WHERE b.user_id = ?";

if ($filter === "upcoming") {
    $sql .= " AND b.status IN ('pending','confirmed') AND b.booking_date >= CURDATE()";
} elseif ($filter === "completed") {
    $sql .= " AND b.status = 'completed'";
} elseif ($filter === "cancelled") {
    $sql .= " AND b.status = 'cancelled'";
}
$sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();

include "../includes/head.php";
?>

<div class="page-header">
    <h1>My Bookings</h1>
</div>

<div class="tab-bar">
    <a href="my-bookings.php?status=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
    <a href="my-bookings.php?status=upcoming" class="<?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
    <a href="my-bookings.php?status=completed" class="<?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed</a>
    <a href="my-bookings.php?status=cancelled" class="<?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
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
                    <?php if (in_array($b["status"], ["pending", "confirmed"])): ?>
                        <a class="icon-btn" href="my-bookings.php?cancel=<?php echo (int)$b['id']; ?>" title="Cancel" data-confirm="Cancel this booking?">❌</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
