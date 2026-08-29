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