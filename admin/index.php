<?php
require __DIR__.'/../includes/functions.php';
admin_required();
$counts=[];
foreach(['tours','reviews'] as $t) $counts[$t]=$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin</title><link rel="stylesheet" href="admin.css"></head><body>
<div class="top"><div class="wrap"><b><?=e(setting('site_name'))?> — Admin</b><a href="logout.php" style="color:#fff">Logout</a></div></div>
<div class="wrap layout"><aside class="side"><a href="index.php">Dashboard</a><a href="about.php">Homepage hero</a><a href="tours.php">Tours</a><a href="reviews.php">Reviews</a><a href="settings.php">Settings</a><a href="../" target="_blank">View website ↗</a></aside>
<main><div class="card"><h1>Content management</h1><p>Buradan saytın mətnlərini və şəkillərini dəyişə bilərsən.</p></div><div class="grid">
<div class="card"><h2><?=e($counts['tours'])?></h2><p>Tours</p><a class="button" href="tours.php">Manage tours</a></div>
<div class="card"><h2><?=e($counts['reviews'])?></h2><p>Reviews</p><a class="button" href="reviews.php">Manage reviews</a></div>
<div class="card"><h2>1</h2><p>Homepage hero</p><a class="button" href="about.php">Edit hero section</a></div>
</div></main></div></body></html>
