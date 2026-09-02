<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Payments";
$active = "payments";
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

// Stats
$total_collected = $conn->query("SELECT COALESCE(SUM(amount), 0) AS s FROM bookings WHERE status = 'completed'")->fetch_assoc()["s"];
$total_pending_pay = $conn->query("SELECT COALESCE(SUM(amount), 0) AS s FROM bookings WHERE status IN ('pending', 'confirmed')")->fetch_assoc()["s"];
$count_paid = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'completed'")->fetch_assoc()["c"];
$count_unpaid = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status != 'completed'")->fetch_assoc()["c"];

// Query all bookings with payment info (Payment is 'paid' when checked-in / completed)
$sql = "SELECT b.id AS booking_id, b.booking_code, u.name AS customer, b.amount, b.status AS booking_status,
               COALESCE(p.method, 'cash') AS method, p.paid_at,
               CASE 
                   WHEN b.status = 'completed' THEN 'paid'
                   WHEN b.status = 'cancelled' THEN 'cancelled'
                   ELSE 'unpaid'
               END AS payment_status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        LEFT JOIN payments p ON p.booking_id = b.id";

if ($filter === "paid") {
    $sql .= " WHERE b.status = 'completed'";
} elseif ($filter === "unpaid") {
    $sql .= " WHERE b.status IN ('pending', 'confirmed')";
} elseif ($filter === "cancelled") {
    $sql .= " WHERE b.status = 'cancelled'";
}

$sql .= " ORDER BY b.id DESC";

$payments = $conn->query($sql);

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Payments Overview</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Total Paid (Collected)</div>
        <div class="value">৳<?php echo number_format($total_collected, 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Pending Payment (Unchecked)</div>
        <div class="value">৳<?php echo number_format($total_pending_pay, 0); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Paid Bookings</div>
        <div class="value"><?php echo (int)$count_paid; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Unpaid / Pending</div>
        <div class="value"><?php echo (int)$count_unpaid; ?></div>
    </div>
</div>

<div class="tab-bar">
    <a href="payments.php?status=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
    <a href="payments.php?status=paid" class="<?php echo $filter === 'paid' ? 'active' : ''; ?>">Paid (Checked In)</a>
    <a href="payments.php?status=unpaid" class="<?php echo $filter === 'unpaid' ? 'active' : ''; ?>">Unpaid (Not Checked In)</a>
    <a href="payments.php?status=cancelled" class="<?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
</div>

<div class="card">
    <div class="card-title">Payment Records (Automatically updated via Manager Check-in)</div>
    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Payment Status</th>
            <th>Paid / Checked-in At</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$payments || $payments->num_rows === 0): ?>
            <tr><td colspan="6" class="empty-state">No payment records found.</td></tr>
        <?php else: ?>
            <?php while ($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo h($p["booking_code"]); ?></strong></td>
                    <td><?php echo h($p["customer"]); ?></td>
                    <td>৳<?php echo number_format($p["amount"], 2); ?></td>
                    <td><?php echo h(ucfirst(str_replace("_", " ", $p["method"]))); ?></td>
                    <td>
                        <?php if ($p["payment_status"] === "paid"): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php elseif ($p["payment_status"] === "cancelled"): ?>
                            <span class="badge badge-cancelled">Cancelled</span>
                        <?php else: ?>
                            <span class="badge badge-unpaid">Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if ($p["payment_status"] === "paid") {
                            echo $p["paid_at"] ? h(date("d M Y g:i A", strtotime($p["paid_at"]))) : "Checked In";
                        } else {
                            echo "—";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>