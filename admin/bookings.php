<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Bookings";
$active = "bookings";
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

$filter = isset($_GET["status"]) ? $_GET["status"] : "all";

$sql = "SELECT b.booking_code, t.name AS turf_name, b.booking_date, b.start_time, b.end_time, u.name AS customer, b.status, b.amount
        FROM bookings b
        JOIN turfs t ON b.turf_id = t.id
        JOIN users u ON b.user_id = u.id";

if ($filter !== "all") {
    $sql .= " WHERE b.status = '" . $conn->real_escape_string($filter) . "'";
}
$sql .= " ORDER BY b.created_at DESC";

$bookings = $conn->query($sql);

include "../includes/head.php";
?>


<div class="page-header">
    <h1>All Bookings</h1>
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
            <th>Customer</th>
            <th>Amount</th>
            <th>Booking Status</th>
            <th>Payment</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($bookings->num_rows === 0): ?>
            <tr><td colspan="8" class="empty-state">No bookings found.</td></tr>
        <?php endif; ?>
        <?php while ($b = $bookings->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo h($b["booking_code"]); ?></strong></td>
                <td><?php echo h($b["turf_name"]); ?></td>
                <td><?php echo h(date("d M Y", strtotime($b["booking_date"]))); ?></td>
                <td><?php echo h(date("g:i A", strtotime($b["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($b["end_time"]))); ?></td>
                <td><?php echo h($b["customer"]); ?></td>
                <td>৳<?php echo number_format($b["amount"], 2); ?></td>
                <td><span class="badge badge-<?php echo h($b["status"]); ?>"><?php echo h(ucfirst($b["status"])); ?></span></td>
                <td>
                    <?php if ($b["status"] === "completed"): ?>
                        <span class="badge badge-paid">Paid</span>
                    <?php elseif ($b["status"] === "cancelled"): ?>
                        <span class="badge badge-cancelled">Cancelled</span>
                    <?php else: ?>
                        <span class="badge badge-unpaid">Unpaid</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>