<?php
session_start();
require 'db.php';
require 'auth.php';
require 'image_optimizer.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: events.php'); exit; }

$stmt = $conn->prepare('SELECT * FROM events WHERE id=?');
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) { header('Location: events.php'); exit; }

$uploadDir    = __DIR__ . '/uploads/events/';
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$errors       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']       ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $edate = trim($_POST['event_date']  ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if ($desc  === '') $errors[] = 'Description is required.';
    if ($edate === '') $errors[] = 'Date is required.';

    $imgName = $event['image'];

    if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > 8 * 1024 * 1024) {
            $errors[] = 'Image must be under 8 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($_FILES['image']['tmp_name']);
            if (!in_array($mime, $allowedMimes, true)) {
                $errors[] = 'Invalid image type.';
            } else {
                $extMap  = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
                $safeName= 'event_' . bin2hex(random_bytes(8)) . '.' . $extMap[$mime];
                $destPath= $uploadDir . $safeName;
                $opt     = optimizeAndSave($_FILES['image']['tmp_name'], $mime, $destPath, 'event');
                if ($opt['ok']) {
                    // Delete old image
                    if (!empty($event['image'])) {
                        $old = $uploadDir . basename($event['image']);
                        if (file_exists($old)) @unlink($old);
                    }
                    $imgName = basename($opt['dest'] ?? $destPath);
                } else {
                    $errors[] = 'Image could not be saved.';
                }
            }
        }
    }

    if (empty($errors)) {
        $conn->prepare('UPDATE events SET title=?, description=?, image=?, event_date=? WHERE id=?')
             ->execute([$title, $desc, $imgName, $edate, $id]);
        $_SESSION['flash'] = ['success', 'Event updated successfully.'];
        header('Location: events.php');
        exit;
    }
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$curThumb = '';
if (!empty($event['image'])) {
    $p = $uploadDir . basename($event['image']);
    if (file_exists($p)) $curThumb = 'uploads/events/' . basename($event['image']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Event — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Event</h4>
        <a href="events.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Events</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="255"
                            value="<?= e($_POST['title'] ?? $event['title']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" required
                            value="<?= e($_POST['event_date'] ?? $event['event_date']) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required><?= e($_POST['description'] ?? $event['description']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Image <small class="text-muted">(leave blank to keep current)</small></label>
                        <?php if ($curThumb): ?>
                        <div class="mb-2">
                            <img src="<?= e($curThumb) ?>" style="height:90px;border-radius:8px;object-fit:cover;" alt="Current image">
                            <div class="text-muted" style="font-size:.78rem;">Current image</div>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:#f0f6ff;font-size:.82rem;">
                            <i class="bi bi-lightning-charge-fill text-primary"></i>
                            New image will be auto-optimised — max 1200×800px, EXIF stripped
                        </div>
                        <input type="file" name="image" id="imgFile" class="form-control"
                            accept="image/jpeg,image/png,image/gif,image/webp">
                        <img id="previewImg" style="max-width:180px;max-height:120px;border-radius:8px;object-fit:cover;display:none;margin-top:8px;" alt="New preview">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                    <a href="events.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggle  = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

document.getElementById('imgFile').addEventListener('change', function () {
    const prev = document.getElementById('previewImg');
    if (this.files && this.files[0]) {
        const r = new FileReader();
        r.onload = e => { prev.src = e.target.result; prev.style.display = 'block'; };
        r.readAsDataURL(this.files[0]);
    } else { prev.style.display = 'none'; }
});
</script>
<?php include 'session_warning.php'; ?>
</body>
</html>
