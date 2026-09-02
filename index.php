<?php
session_start();

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION["role"] === "manager") {
        header("Location: manager/dashboard.php");
    } else {
        header("Location: player/dashboard.php");
    }
} else {
    header("Location: login.php");
}
exit();
?>
