<?php
require_once "../includes/auth.php";
require_role("admin");

$page_title = "Reports";
$active = "reports";
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

$total_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()["c"];
$total_revenue = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM bookings WHERE status IN ('confirmed','completed')")->fetch_assoc()["s"];
$cancelled_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status = 'cancelled'")->fetch_assoc()["c"];
$new_users = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'player'")->fetch_assoc()["c"];

// Revenue by turf, used for a simple text-based breakdown
$by_turf = $conn->query(
    "SELECT t.name, COUNT(b.id) AS total, COALESCE(SUM(b.amount),0) AS revenue
     FROM turfs t
     LEFT JOIN bookings b ON b.turf_id = t.id AND b.status IN ('confirmed','completed')
     GROUP BY t.id
     ORDER BY revenue DESC"
);

include "../includes/head.php";
?>