<?php
session_start(); require "db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: events.php"); exit; }
$stmt = $conn->prepare("SELECT * FROM events WHERE id=?"); $stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { header("Location: events.php"); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title=$_POST['title']??''; $desc=$_POST['description']??''; $date=$_POST['event_date']??'';
    if(empty($title)) $errors[]="Title required.";
    if(empty($desc))  $errors[]="Description required.";
    if(empty($date))  $errors[]="Date required.";
    if(empty($errors)){
        $img = $event['image'];
        if(isset($_FILES['image'])&&$_FILES['image']['error']===UPLOAD_ERR_OK){
            $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
            $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($_FILES['image']['tmp_name']);
            if(!isset($allowed[$mime])){ $errors[]="Invalid image type."; }
            elseif($_FILES['image']['size']>5*1024*1024){ $errors[]="Image must be under 5 MB."; }
            else {
                $dir=__DIR__.'/uploads/events/';
                if(!is_dir($dir)) mkdir($dir,0755,true);
                $name='event_'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
                if(move_uploaded_file($_FILES['image']['tmp_name'],$dir.$name)){
                    if(!empty($event['image'])){$old=$dir.basename($event['image']);if(file_exists($old))@unlink($old);}
                    $img=basename($name);
                } else { $errors[]="Upload failed."; }
            }
        }
        if(empty($errors)){
            $conn->prepare("UPDATE events SET title=?,description=?,image=?,event_date=? WHERE id=?")->execute([$title,$desc,$img,$date,$id]);
            $_SESSION['flash']=['success','Event updated.']; header("Location: events.php"); exit;
        }
    }
}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Edit Event</title><link rel="stylesheet" href="../css/bootstrap.min.css"></head>
<body class="bg-light"><div class="container mt-4" style="max-width:700px">
<div class="d-flex justify-content-between align-items-center mb-3">
<img src="../img/logo.png" style="height:50px" alt="Logo">
<div class="d-flex gap-2"><a href="events.php" class="btn btn-secondary btn-sm">Back</a><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></div></div>
<h4>Edit Event</h4>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data">
<div class="mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" value="<?=e($_POST['title']??$event['title'])?>" required></div>
<div class="mb-3"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="5" required><?=e($_POST['description']??$event['description'])?></textarea></div>
<div class="mb-3"><label class="form-label">Event Date *</label><input type="date" name="event_date" class="form-control" value="<?=e($_POST['event_date']??$event['event_date'])?>" required></div>
<div class="mb-3"><label class="form-label">Image <small class="text-muted">(leave blank to keep current)</small></label>
<?php if(!empty($event['image'])): ?><div class="mb-2"><img src="uploads/events/<?=e(basename($event['image']))?>" style="height:80px;border-radius:6px" alt="current"></div><?php endif; ?>
<input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"></div>
<button type="submit" class="btn btn-primary w-100">Save Changes</button>
</form></div></body></html>
