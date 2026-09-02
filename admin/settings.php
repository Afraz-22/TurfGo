<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Settings";
$active = "settings";
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

$error = "";
$success = "";
$user_id = $_SESSION["user_id"];

$user_stmt = $conn->prepare("SELECT name, email, phone, address, password FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])) {
    $current_pass = $_POST["current_password"] ?? "";
    $new_pass = $_POST["new_password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if (!password_verify($current_pass, $user["password"])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new_pass !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $success = "Password changed successfully.";
            $user["password"] = $hashed;
        } else {
            $error = "Failed to update password.";
        }
        $stmt->close();
    }
}

include "../includes/head.php";
?>

<div class="page-header">
    <h1>Account Settings</h1>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

<div class="card">
    <div class="card-title">Profile Information</div>
    <form method="POST" action="settings.php">
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
        <button type="submit" name="update_profile" class="btn">Save Changes</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Change Password</div>
    <form method="POST" action="settings.php">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required placeholder="Enter current password">
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6" placeholder="Enter new password (min 6 characters)">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6" placeholder="Re-enter new password">
        </div>
        <button type="submit" name="change_password" class="btn btn-outline">Update Password</button>
    </form>
</div>

<?php include "../includes/foot.php"; ?>