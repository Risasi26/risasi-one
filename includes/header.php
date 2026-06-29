<?php
// Header include used by every protected admin page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>risasi_one Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <header class="topbar">
        <div class="brand">
            <div class="logo-circle">R</div>
            <div>
                <h1>Risasi One</h1>
                <p>Tenant utility alerts</p>
            </div>
        </div>
        <a href="logout.php" class="button button-secondary">Logout</a>
    </header>
    <div class="layout">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert"><?php echo e($_SESSION['flash_success']); ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert error"><?php echo e($_SESSION['flash_error']); ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
