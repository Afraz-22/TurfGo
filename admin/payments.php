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