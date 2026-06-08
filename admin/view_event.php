<?php
session_start(); require "db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
$id=intval($_GET['id']??0); if($id<=0){header("Location: events.php");exit;}
$stmt=$conn->prepare("SELECT * FROM events WHERE id=?"); $stmt->execute([$id]);
$event=$stmt->fetch(); if(!$event){header("Location: events.php");exit;}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>View Event</title><link rel="stylesheet" href="../css/bootstrap.min.css"></head>
<body class="bg-light"><div class="container mt-4" style="max-width:800px">
<div class="d-flex justify-content-between align-items-center mb-3">
<img src="../img/logo.png" style="height:50px" alt="Logo">
<div class="d-flex gap-2"><a href="edit_event.php?id=<?=$event['id']?>" class="btn btn-warning btn-sm">Edit</a><a href="events.php" class="btn btn-secondary btn-sm">Back</a><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></div></div>
<?php $img='uploads/events/'.basename($event['image']??'');
if(!empty($event['image'])&&file_exists(__DIR__.'/'.$img)): ?>
<img src="<?=e($img)?>" class="w-100 rounded mb-3" style="max-height:360px;object-fit:cover" alt="">
<?php else: ?><div class="bg-light text-center text-muted py-5 rounded mb-3">No image</div><?php endif; ?>
<span class="badge bg-primary"><?=e(date('d F Y',strtotime($event['event_date'])))?></span>
<h2 class="mt-2"><?=e($event['title'])?></h2>
<p style="white-space:pre-line"><?=e($event['description'])?></p>
<hr><p class="text-muted small">Created: <?=e($event['created_at']??'—')?></p>
</div></body></html>
