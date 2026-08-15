<?php
/**
 * Layout Header
 *
 * Shared navigation header. The main app.php layout includes
 * navigation inline, but this file is available for controllers
 * that need a custom header.
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=escapeOutput($title ?? APP_NAME)?> | <?=APP_NAME?></title>
    <link rel="stylesheet" href="<?=APP_URL?>/assets/css/style.css">
</head>
<body>
<nav>
    <a href="<?=APP_URL?>/index.php?action=dashboard">Dashboard</a>
    <?php if(isLoggedIn()): ?>
        <a href="<?=APP_URL?>/index.php?action=students">Students</a>
        <a href="<?=APP_URL?>/index.php?action=rooms">Rooms</a>
        <a href="<?=APP_URL?>/index.php?action=fees">Fees</a>
        <a href="<?=APP_URL?>/index.php?action=visitors">Visitors</a>
        <a href="<?=APP_URL?>/index.php?action=complaints">Complaints</a>
        <a href="<?=APP_URL?>/index.php?action=reports">Reports</a>
        <a href="<?=APP_URL?>/index.php?action=profile">Profile</a>
        <a href="<?=APP_URL?>/index.php?action=logout">Logout</a>
    <?php endif ?>
</nav>
<main>
