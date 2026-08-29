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

// Mark a payment as paid
if (isset($_GET["mark_paid"])) {
    $id = (int)$_GET["mark_paid"];
    $conn->query("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = $id");
    header("Location: payments.php");
    exit();
}

$payments = $conn->query(
    "SELECT p.id, b.booking_code, u.name AS customer, p.amount, p.method, p.status, p.paid_at
     FROM payments p
     JOIN bookings b ON p.booking_id = b.id
     JOIN users u ON b.user_id = u.id
     ORDER BY p.id DESC"
);

include "../includes/head.php";
?>


<div class="page-header">
    <h1>Payments</h1>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Paid At</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($payments->num_rows === 0): ?>
            <tr><td colspan="7" class="empty-state">No payment records yet.</td></tr>
        <?php endif; ?>
        <?php while ($p = $payments->fetch_assoc()): ?>
            <tr>
                <td><?php echo h($p["booking_code"]); ?></td>
                <td><?php echo h($p["customer"]); ?></td>
                <td>৳<?php echo number_format($p["amount"], 0); ?></td>
                <td><?php echo h(ucfirst(str_replace("_", " ", $p["method"]))); ?></td>
                <td><span class="badge badge-<?php echo h($p["status"]); ?>"><?php echo h(ucfirst($p["status"])); ?></span></td>
                <td><?php echo $p["paid_at"] ? h(date("d M Y g:i A", strtotime($p["paid_at"]))) : "—"; ?></td>
                <td>
                    <?php if ($p["status"] !== "paid"): ?>
                        <a class="icon-btn" href="payments.php?mark_paid=<?php echo (int)$p['id']; ?>" title="Mark as paid">✅</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>