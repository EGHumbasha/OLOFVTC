<?php
session_start();
require 'db.php';
require 'auth.php';
require 'image_optimizer.php';

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$uploadDir    = __DIR__ . '/uploads/events/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$errors = [];
$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Add Event ─────────────────────────────────────────────────────────────────
if (isset($_POST['add_event'])) {
    $title   = trim($_POST['title']        ?? '');
    $desc    = trim($_POST['description']  ?? '');
    $edate   = trim($_POST['event_date']   ?? '');

    if ($title  === '') $errors[] = 'Title is required.';
    if ($desc   === '') $errors[] = 'Description is required.';
    if ($edate  === '') $errors[] = 'Event date is required.';

    $imgName = '';
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
                    $imgName = basename($opt['dest'] ?? $destPath);
                } else {
                    $errors[] = 'Image could not be saved: ' . ($opt['error'] ?? '');
                }
            }
        }
    } elseif (empty($errors) && empty($imgName) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Image upload failed (error code: ' . $_FILES['image']['error'] . ').';
    }

    if (empty($errors)) {
        $conn->prepare("INSERT INTO events (title, description, image, event_date, created_at) VALUES (?,?,?,?,NOW())")
             ->execute([$title, $desc, $imgName, $edate]);
        $_SESSION['flash'] = ['success', 'Event "' . htmlspecialchars($title) . '" added successfully.'];
        header('Location: events.php');
        exit;
    }
}

// ── Delete Event ──────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id   = intval($_GET['delete']);
    $stmt = $conn->prepare('SELECT image FROM events WHERE id=?');
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    if ($row && !empty($row['image'])) {
        $img = $uploadDir . basename($row['image']);
        if (file_exists($img)) @unlink($img);
    }
    $conn->prepare('DELETE FROM events WHERE id=?')->execute([$id]);
    $_SESSION['flash'] = ['success', 'Event deleted.'];
    header('Location: events.php');
    exit;
}

$events = $conn->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Events — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.event-thumb { width:80px; height:55px; object-fit:cover; border-radius:6px; }
.preview-img { max-width:180px; max-height:120px; border-radius:8px; object-fit:cover; display:none; margin-top:8px; }
</style>
</head>
<body>

<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-success"></i>Manage Events</h4>
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash[0]) ?> alert-dismissible fade show">
        <?= e($flash[1]) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Add Event Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <strong><i class="bi bi-plus-circle me-2 text-success"></i>Add New Event</strong>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="255"
                            value="<?= e($_POST['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" required
                            value="<?= e($_POST['event_date'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" required><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Image <small class="text-muted">(optional · JPG PNG WEBP · max 8 MB)</small></label>
                        <div class="d-flex align-items-center gap-2 mb-1 p-2 rounded" style="background:#f0f6ff;font-size:.82rem;">
                            <i class="bi bi-lightning-charge-fill text-primary"></i>
                            Auto-optimised on upload — max 1200×800px, EXIF stripped
                        </div>
                        <input type="file" name="image" id="imgFile" class="form-control"
                            accept="image/jpeg,image/png,image/gif,image/webp">
                        <img id="previewImg" class="preview-img" alt="Preview">
                    </div>
                </div>
                <button type="submit" name="add_event" class="btn btn-success mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Add Event
                </button>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-list-ul me-2"></i>All Events <span class="badge bg-secondary"><?= count($events) ?></span></strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px;">Image</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($events)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-calendar-x me-2"></i>No events yet.</td></tr>
                <?php else: ?>
                <?php foreach ($events as $ev):
                    $thumb = '';
                    if (!empty($ev['image'])) {
                        $p = $uploadDir . basename($ev['image']);
                        if (file_exists($p)) $thumb = 'uploads/events/' . basename($ev['image']);
                    }
                ?>
                <tr>
                    <td>
                        <?php if ($thumb): ?>
                        <img src="<?= e($thumb) ?>" class="event-thumb" alt="">
                        <?php else: ?>
                        <div class="event-thumb d-flex align-items-center justify-content-center bg-light text-muted rounded">
                            <i class="bi bi-image"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= e($ev['title']) ?></div>
                        <small class="text-muted"><?= e(substr($ev['description'], 0, 80)) ?>…</small>
                    </td>
                    <td class="text-nowrap"><?= date('d M Y', strtotime($ev['event_date'])) ?></td>
                    <td class="text-nowrap">
                        <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                        <a href="edit_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                        <a href="events.php?delete=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-danger"
                            title="Delete" onclick="return confirm('Delete event: <?= e(addslashes($ev['title'])) ?>?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggle  = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

// Image preview
document.getElementById('imgFile').addEventListener('change', function () {
    const prev = document.getElementById('previewImg');
    if (this.files && this.files[0]) {
        const r = new FileReader();
        r.onload = e => { prev.src = e.target.result; prev.style.display = 'block'; };
        r.readAsDataURL(this.files[0]);
    } else {
        prev.style.display = 'none';
    }
});
</script>
<?php include 'session_warning.php'; ?>
</body>
</html>
