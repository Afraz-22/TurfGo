<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo h($page_title); ?> - TurfGo</title>
    <link rel="stylesheet" href="/turfgo/css/style.css">
</head>
<body class="theme-<?php echo h($theme); ?>">
<div class="app">

    <aside class="sidebar">
        <div class="sidebar-logo">⚽ TurfGo</div>
        <nav>
            <?php foreach ($nav_items as $item): ?>
                <a href="<?php echo h($item['href']); ?>"
                   class="<?php echo ($active === $item['key']) ? 'active' : ''; ?>">
                    <?php echo h($item['icon']); ?> <?php echo h($item['label']); ?>
                </a>
            <?php endforeach; ?>
            <a href="/turfgo/logout.php" data-confirm="Are you sure you want to logout?">🚪 Logout</a>
        </nav>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title"><?php echo h($page_title); ?></div>
            <div class="topbar-user">
                <span class="role-badge"><?php echo h(ucfirst($_SESSION['role'])); ?></span>
                <span><?php echo h($_SESSION['name']); ?></span>
                <div class="avatar"><?php echo h(strtoupper(substr($_SESSION['name'], 0, 1))); ?></div>
            </div>
        </div>
        <div class="content">
