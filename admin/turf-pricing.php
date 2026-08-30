<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Turf & Pricing";
$active = "turf-pricing";
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

// Add a new turf
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_turf"])) {
    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $price = (float)$_POST["price"];

    $stmt = $conn->prepare("INSERT INTO turfs (name, location, price_per_hour, status) VALUES (?, ?, ?, 'active')");
    $stmt->bind_param("ssd", $name, $location, $price);
    if ($stmt->execute()) {
        $success = "Turf added successfully.";
    } else {
        $error = "Could not add turf.";
    }
    $stmt->close();
}