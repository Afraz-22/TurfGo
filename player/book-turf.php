<?php
require_once "../includes/auth.php";
require_role("player");

$page_title = "Book Turf";
$active = "book-turf";
$theme = "player";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "book-turf", "href" => "book-turf.php", "icon" => "🏟️", "label" => "Book Turf"],
    ["key" => "my-bookings", "href" => "my-bookings.php", "icon" => "📅", "label" => "My Bookings"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

$selected_date = isset($_GET["date"]) ? $_GET["date"] : date("Y-m-d");
$selected_time = isset($_GET["time"]) ? $_GET["time"] : "";

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["book_turf"])) {
    $turf_id = (int)$_POST["turf_id"];
    $booking_date = $_POST["booking_date"];
    $start_time = $_POST["start_time"];
    $end_time = date("H:i:s", strtotime($start_time . " +1 hour"));

    // Check the slot isn't already taken
    $check = $conn->prepare(
        "SELECT id FROM bookings WHERE turf_id = ? AND booking_date = ? AND start_time = ? AND status != 'cancelled'"
    );
    $check->bind_param("iss", $turf_id, $booking_date, $start_time);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Sorry, this slot was just booked. Please choose another time.";
    } else {
        $turf_stmt = $conn->prepare("SELECT price_per_hour FROM turfs WHERE id = ?");
        $turf_stmt->bind_param("i", $turf_id);
        $turf_stmt->execute();
        $turf_row = $turf_stmt->get_result()->fetch_assoc();
        $turf_stmt->close();

        $amount = $turf_row ? $turf_row["price_per_hour"] : 0;
        $booking_code = "BKG" . strtoupper(substr(uniqid(), -5));

        $insert = $conn->prepare(
            "INSERT INTO bookings (booking_code, user_id, turf_id, booking_date, start_time, end_time, amount, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
        );
        $insert->bind_param("siisssd", $booking_code, $user_id, $turf_id, $booking_date, $start_time, $end_time, $amount);

        if ($insert->execute()) {
            $booking_id = $conn->insert_id;
            $pay = $conn->prepare("INSERT INTO payments (booking_id, amount, method, status) VALUES (?, ?, 'cash', 'unpaid')");
            $pay->bind_param("id", $booking_id, $amount);
            $pay->execute();
            $pay->close();

            $success = "Booking request submitted! Your booking ID is $booking_code. It will be confirmed by the turf manager shortly.";
        } else {
            $error = "Could not create booking. Please try again.";
        }
        $insert->close();
    }
    $check->close();
}

$turfs = $conn->query("SELECT * FROM turfs WHERE status = 'active' ORDER BY name");

// Fetch booked start times for quick lookup per turf on the selected date
$booked_map = [];
$res = $conn->query(
    "SELECT turf_id, start_time FROM bookings WHERE booking_date = '" . $conn->real_escape_string($selected_date) . "' AND status != 'cancelled'"
);
while ($row = $res->fetch_assoc()) {
    $booked_map[$row["turf_id"]][] = date("H:i", strtotime($row["start_time"]));
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Book a Turf</h1>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<form method="GET" action="book-turf.php" class="filter-bar">
    <input type="date" name="date" value="<?php echo h($selected_date); ?>">
    <button type="submit" class="btn">Search</button>
</form>

<?php $turfs->data_seek(0); while ($t = $turfs->fetch_assoc()): ?>
    <div class="card">
        <h3><?php echo h($t["name"]); ?></h3>
        <div class="loc"><?php echo h($t["location"]); ?></div>
        <div class="price">৳<?php echo number_format($t["price_per_hour"], 0); ?> / hour</div>

        <div class="slot-grid">
            <?php
            $booked = $booked_map[$t["id"]] ?? [];
            foreach (range(6, 21) as $hour):
                $start = sprintf("%02d:00", $hour);
                $end = sprintf("%02d:00", ($hour + 1) % 24);
                $is_booked = in_array($start, $booked);
            ?>
                <?php if ($is_booked): ?>
                    <div class="slot slot-booked">
                        <div class="time"><?php echo h(date("g:i A", strtotime($start))); ?></div>
                        <div>Booked</div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="book-turf.php">
                        <input type="hidden" name="turf_id" value="<?php echo (int)$t['id']; ?>">
                        <input type="hidden" name="booking_date" value="<?php echo h($selected_date); ?>">
                        <input type="hidden" name="start_time" value="<?php echo h($start); ?>">
                        <button type="submit" name="book_turf" class="slot slot-available" style="cursor:pointer; width:100%;">
                            <div class="time"><?php echo h(date("g:i A", strtotime($start))); ?></div>
                            <div>Book Now</div>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endwhile; ?>

<?php include "../includes/foot.php"; ?>
