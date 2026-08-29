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

$user_stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
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
    $new_pass = $_POST["new_password"];
    $confirm = $_POST["confirm_password"];

    if ($new_pass !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $stmt->close();
        $success = "Password changed successfully.";
    }
}

include "../includes/head.php";
?>