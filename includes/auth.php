<?php
// Starts the session and provides simple role-based access helpers.
session_start();
require_once __DIR__ . "/../config/db.php";

// Redirect to login if nobody is logged in
function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: /turfgo/login.php");
        exit();
    }
}

// Redirect away if the logged-in user does not have the required role
function require_role($role) {
    require_login();
    if ($_SESSION["role"] !== $role) {
        header("Location: /turfgo/login.php");
        exit();
    }
}

// Small helper to safely print values inside HTML
function h($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>
