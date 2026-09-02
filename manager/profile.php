<?php
require_once "../includes/auth.php";
require_role("manager");

$page_title = "Profile";
$active = "profile";
$theme = "manager";
$nav_items = [
    ["key" => "dashboard", "href" => "dashboard.php", "icon" => "📊", "label" => "Dashboard"],
    ["key" => "manage-slots", "href" => "manage-slots.php", "icon" => "🕒", "label" => "Manage Slots"],
    ["key" => "bookings", "href" => "bookings.php", "icon" => "📅", "label" => "Bookings"],
    ["key" => "check-in", "href" => "check-in.php", "icon" => "✅", "label" => "Check-in"],
    ["key" => "schedule", "href" => "schedule.php", "icon" => "🗓️", "label" => "Schedule"],
    ["key" => "profile", "href" => "profile.php", "icon" => "👤", "label" => "Profile"],
];

$error = "";
$success = "";
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {
    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $_SESSION["name"] = $name;
        $user["name"] = $name;
        $user["phone"] = $phone;
        $user["address"] = $address;
    } else {
        $error = "Could not update profile.";
    }
    $stmt->close();
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>My Profile</h1>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="card">
    <div class="card-title">Profile Information</div>
    <form method="POST" action="profile.php">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo h($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?php echo h($user['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo h($user['phone']); ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" value="<?php echo h($user['address']); ?>">
        </div>
        <button type="submit" name="update_profile" class="btn">Edit Profile</button>
    </form>
</div>

<?php include "../includes/foot.php"; ?>
