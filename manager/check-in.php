<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Check-in";
$active = "check-in";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

// Perform check-in (ONLY if booking is confirmed)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["checkin_id"])) {
    $id = (int)$_POST["checkin_id"];

    // Check if booking is confirmed
    $stmt = $conn->prepare("UPDATE bookings SET status = 'completed' WHERE id = ? AND status = 'confirmed'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        // Automatically sync payment status to 'paid' upon check-in
        $check_pay = $conn->prepare("SELECT id FROM payments WHERE booking_id = ?");
        $check_pay->bind_param("i", $id);
        $check_pay->execute();
        $pay_res = $check_pay->get_result();
        $check_pay->close();

        if ($pay_res->num_rows > 0) {
            $pay_stmt = $conn->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE booking_id = ?");
            $pay_stmt->bind_param("i", $id);
            $pay_stmt->execute();
            $pay_stmt->close();
        } else {
            // Fetch booking amount to insert payment record
            $b_stmt = $conn->prepare("SELECT amount FROM bookings WHERE id = ?");
            $b_stmt->bind_param("i", $id);
            $b_stmt->execute();
            $b_row = $b_stmt->get_result()->fetch_assoc();
            $b_stmt->close();
            $amount = $b_row ? $b_row["amount"] : 0;

            $ins_pay = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status, paid_at) VALUES (?, ?, 'cash', 'paid', NOW())");
            $ins_pay->bind_param("id", $id, $amount);
            $ins_pay->execute();
            $ins_pay->close();
        }

        $redirect_url = "check-in.php?msg=checked_in";
    } else {
        $redirect_url = "check-in.php?err=not_confirmed";
    }

    if (!empty($_POST["redirect_q"])) {
        $redirect_url .= "&q=" . urlencode($_POST["redirect_q"]);
    }
    if (!empty($_POST["redirect_filter"])) {
        $redirect_url .= "&filter=" . urlencode($_POST["redirect_filter"]);
    }
    header("Location: " . $redirect_url);
    exit();
}

$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$filter = isset($_GET["filter"]) ? trim($_GET["filter"]) : "all";

// Fetch Stats for check-in
$stat_total = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()["c"];
$stat_today = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date = CURDATE()")->fetch_assoc()["c"];
$stat_ready = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'confirmed'")->fetch_assoc()["c"];
$stat_completed = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'completed'")->fetch_assoc()["c"];

// Build SQL query
$sql = "SELECT b.id, b.booking_code, b.booking_date, b.start_time, b.end_time, b.amount, b.status,
               t.name AS turf_name, u.name AS customer, u.email, u.phone
        FROM bookings b
        JOIN turfs t ON b.turf_id = t.id
        JOIN users u ON b.user_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

if ($filter === "today") {
    $sql .= " AND b.booking_date = CURDATE()";
} elseif ($filter === "ready") {
    $sql .= " AND b.status = 'confirmed'";
} elseif ($filter === "pending") {
    $sql .= " AND b.status = 'pending'";
} elseif ($filter === "completed") {
    $sql .= " AND b.status = 'completed'";
} elseif ($filter === "cancelled") {
    $sql .= " AND b.status = 'cancelled'";
}

if ($query !== "") {
    $sql .= " AND (b.booking_code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR t.name LIKE ?)";
    $like = "%" . $query . "%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
    $types .= "sssss";
}

$sql .= " ORDER BY b.booking_date DESC, b.start_time DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
    $stmt->close();
} else {
    $bookings_result = $conn->query($sql);
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Player Check-in</h1>
</div>

<?php if (isset($_GET["msg"]) && $_GET["msg"] === "checked_in"): ?>
    <div class="alert alert-success">✅ Player has been successfully checked in!</div>
<?php elseif (isset($_GET["err"]) && $_GET["err"] === "not_confirmed"): ?>
    <div class="alert alert-error">⚠️ Cannot check in: The booking must be confirmed first in the Bookings section before check-in.</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Total Bookings</div>
        <div class="value"><?php echo (int)$stat_total; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Today's Bookings</div>
        <div class="value"><?php echo (int)$stat_today; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Ready for Check-in</div>
        <div class="value"><?php echo (int)$stat_ready; ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Completed Check-ins</div>
        <div class="value"><?php echo (int)$stat_completed; ?></div>
    </div>
</div>

<div class="tab-bar">
    <a href="check-in.php?filter=all<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All Players</a>
    <a href="check-in.php?filter=ready<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'ready' ? 'active' : ''; ?>">Ready (Confirmed)</a>
    <a href="check-in.php?filter=pending<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
    <a href="check-in.php?filter=today<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'today' ? 'active' : ''; ?>">Today's Bookings</a>
    <a href="check-in.php?filter=completed<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'completed' ? 'active' : ''; ?>">Checked-in</a>
    <a href="check-in.php?filter=cancelled<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>" class="<?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
</div>

<div class="card">
    <form method="GET" action="check-in.php" class="filter-bar" style="margin-bottom:0;">
        <input type="hidden" name="filter" value="<?php echo h($filter); ?>">
        <input type="text" name="q" placeholder="Search by booking ID, player name, phone, email, or turf..." value="<?php echo h($query); ?>" style="flex:1;">
        <button type="submit" class="btn">Search</button>
        <?php if ($query !== ""): ?>
            <a href="check-in.php?filter=<?php echo h($filter); ?>" class="btn btn-outline" style="line-height:1.2;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-title">Booked Players List</div>
    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Player / Customer</th>
            <th>Turf</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$bookings_result || $bookings_result->num_rows === 0): ?>
            <tr>
                <td colspan="8" class="empty-state">No booked players found matching your criteria.</td>
            </tr>
        <?php else: ?>
            <?php while ($b = $bookings_result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo h($b["booking_code"]); ?></strong></td>
                    <td>
                        <div><strong><?php echo h($b["customer"]); ?></strong></div>
                        <?php if (!empty($b["phone"])): ?>
                            <div style="font-size:12px; color:var(--text-muted);">📞 <?php echo h($b["phone"]); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($b["email"])): ?>
                            <div style="font-size:12px; color:var(--text-muted);">✉️ <?php echo h($b["email"]); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo h($b["turf_name"]); ?></td>
                    <td><?php echo h(date("d M Y", strtotime($b["booking_date"]))); ?></td>
                    <td><?php echo h(date("g:i A", strtotime($b["start_time"]))); ?> - <?php echo h(date("g:i A", strtotime($b["end_time"]))); ?></td>
                    <td>৳<?php echo h(number_format((float)$b["amount"], 2)); ?></td>
                    <td><span class="badge badge-<?php echo h($b["status"]); ?>"><?php echo h(ucfirst($b["status"])); ?></span></td>
                    <td>
                        <?php if ($b["status"] === "confirmed"): ?>
                            <form method="POST" action="check-in.php" style="display:inline;">
                                <input type="hidden" name="checkin_id" value="<?php echo (int)$b['id']; ?>">
                                <input type="hidden" name="redirect_q" value="<?php echo h($query); ?>">
                                <input type="hidden" name="redirect_filter" value="<?php echo h($filter); ?>">
                                <button type="submit" class="btn btn-sm" onclick="return confirm('Confirm check-in for <?php echo h(addslashes($b['customer'])); ?> (<?php echo h($b['booking_code']); ?>)?');">Check In</button>
                            </form>
                        <?php elseif ($b["status"] === "pending"): ?>
                            <span style="color:var(--warning); font-size:12px; font-weight:600;" title="Confirm this booking in the Bookings tab first">⏳ Needs Confirmation</span>
                        <?php elseif ($b["status"] === "completed"): ?>
                            <span style="color:var(--success); font-weight:600; font-size:13px;">✓ Checked In</span>
                        <?php elseif ($b["status"] === "cancelled"): ?>
                            <span style="color:var(--danger); font-size:13px;">Cancelled</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/foot.php"; ?>
