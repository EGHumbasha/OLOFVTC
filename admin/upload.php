<?php
/**
 * admin/upload.php — Gallery & Event image uploader with built-in optimiser
 *
 * Every uploaded image is automatically:
 *   - Validated by real MIME type (finfo, not extension)
 *   - Resized if wider than 1600px (gallery) or 1200px (event)
 *   - Re-encoded at quality 82 — visually identical, 40-60% smaller
 *   - EXIF stripped (privacy + size)
 *   - Given a cryptographically random safe filename
 */
session_start();
require 'db.php';
require 'auth.php';
require 'image_optimizer.php';

$type = $_GET['type'] ?? 'gallery';
if (!in_array($type, ['gallery', 'event'], true)) $type = 'gallery';

$categories = [
    'hairdressing', 'bakerystudies', 'catering', 'cuttingdesigning',
    'interiorhome_decor', 'beautytherapy', 'computers',
    'weldingsheet_metalwork', 'woodwork', 'eventsdecor', 'graduation',
];
$catLabels = [
    'hairdressing'         => 'Hairdressing',
    'bakerystudies'        => 'Bakery Studies',
    'catering'             => 'Catering',
    'cuttingdesigning'     => 'Cutting & Designing',
    'interiorhome_decor'   => 'Interior Home Decor',
    'beautytherapy'        => 'Beauty Therapy',
    'computers'            => 'Computers',
    'weldingsheet_metalwork' => 'Welding & Sheet Metalwork',
    'woodwork'             => 'Woodwork',
    'eventsdecor'          => 'Events Decor',
    'graduation'           => 'Graduation',
];

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$baseDir = $type === 'event'
    ? __DIR__ . '/uploads/events/'
    : __DIR__ . '/uploads/gallery/';

if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

$results  = []; // per-file result rows
$uploaded = 0;
$totalSaved = 0;
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = $_POST['category'] ?? '';

    if ($type === 'gallery' && !in_array($cat, $categories, true)) {
        $formErrors[] = 'Please select a valid category.';
    } elseif (empty($_FILES['images']['name'][0])) {
        $formErrors[] = 'Please select at least one image.';
    } else {
        $files = $_FILES['images'];
        $count = count($files['name']);

        if ($count > 20) {
            $formErrors[] = 'Maximum 20 images per upload batch.';
        } else {
            for ($i = 0; $i < $count; $i++) {
                $origName = $files['name'][$i];

                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    $results[] = ['name' => $origName, 'ok' => false,
                        'msg' => 'Upload error code: ' . $files['error'][$i]];
                    continue;
                }
                if ($files['size'][$i] > 8 * 1024 * 1024) {
                    $results[] = ['name' => $origName, 'ok' => false, 'msg' => 'Exceeds 8 MB limit.'];
                    continue;
                }

                // Real MIME check (not extension)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($files['tmp_name'][$i]);

                if (!in_array($mime, $allowedMimes, true)) {
                    $results[] = ['name' => $origName, 'ok' => false, 'msg' => 'Not a valid image type (' . $mime . ').'];
                    continue;
                }

                // Build safe destination filename
                $extMap   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $ext      = $extMap[$mime];
                $prefix   = $type === 'event' ? 'event' : $cat;
                $safeName = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $destPath = $baseDir . $safeName;

                // ── Optimise & save ───────────────────────────────────────────
                $opt = optimizeAndSave($files['tmp_name'][$i], $mime, $destPath, $type === 'event' ? 'event' : 'gallery');

                if (!$opt['ok']) {
                    $results[] = ['name' => $origName, 'ok' => false, 'msg' => $opt['error'] ?? 'Save failed.'];
                    continue;
                }

                // Use actual saved filename (PNG→JPG conversion may have changed it)
                $finalName = basename($opt['dest'] ?? $destPath);

                $sizeMsg = '';
                if (isset($opt['saved_bytes']) && $opt['saved_bytes'] > 1024) {
                    $sizeMsg = ' Saved ' . formatBytes($opt['saved_bytes'])
                        . ' (' . formatBytes($opt['original']) . ' → ' . formatBytes($opt['final']) . ')';
                }

                $dimensionMsg = '';
                if (!empty($opt['width'])) {
                    $dimensionMsg = ' · ' . $opt['width'] . '×' . $opt['height'] . 'px';
                }

                $results[] = [
                    'name' => $origName,
                    'ok'   => true,
                    'msg'  => 'Uploaded' . $dimensionMsg . $sizeMsg,
                    'file' => $finalName,
                    'path' => ($type === 'event' ? 'uploads/events/' : 'uploads/gallery/') . $finalName,
                ];
                $totalSaved += $opt['saved_bytes'] ?? 0;
                $uploaded++;
            }
        }
    }
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Upload <?= ucfirst($type) === 'Event' ? 'Event Image' : 'Gallery Images' ?> — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.drop-zone {
    border: 2px dashed #adb5bd;
    border-radius: 10px;
    padding: 36px 24px;
    text-align: center;
    color: #6c757d;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    user-select: none;
}
.drop-zone.drag-over { border-color: #005f99; background: #f0f6ff; }
#preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 8px;
    margin-top: 12px;
}
#preview-grid .thumb {
    position: relative;
    border-radius: 7px;
    overflow: hidden;
    aspect-ratio: 4/3;
    background: #f0f0f0;
}
#preview-grid .thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
#preview-grid .thumb .rm {
    position: absolute;
    top: 3px;
    right: 3px;
    background: rgba(220,53,69,.85);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 11px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
#preview-grid .thumb .fname {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,.55);
    color: #fff;
    font-size: 10px;
    padding: 2px 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.result-row { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 6px; margin-bottom: 5px; font-size: .88rem; }
.result-row.ok  { background: #d1fae5; color: #065f46; }
.result-row.err { background: #fee2e2; color: #991b1b; }
.opt-badge { background: #005f99; color: #fff; padding: 2px 8px; border-radius: 999px; font-size: .75rem; white-space: nowrap; }
</style>
</head>
<body>

<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <?= $type === 'event' ? '<i class="bi bi-calendar-plus me-2 text-success"></i>Upload Event Image' : '<i class="bi bi-images me-2 text-primary"></i>Upload Gallery Images' ?>
            </h4>
            <small class="text-muted">Images are automatically optimised on upload — smaller file size, same quality</small>
        </div>
        <div class="d-flex gap-2">
            <a href="upload.php?type=gallery" class="btn btn-sm <?= $type === 'gallery' ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="bi bi-images me-1"></i>Gallery
            </a>
            <a href="upload.php?type=event" class="btn btn-sm <?= $type === 'event' ? 'btn-success' : 'btn-outline-success' ?>">
                <i class="bi bi-calendar-plus me-1"></i>Event
            </a>
        </div>
    </div>

    <!-- Upload results -->
    <?php if (!empty($formErrors)): ?>
    <div class="alert alert-danger mb-3">
        <?php foreach ($formErrors as $fe): ?><div><i class="bi bi-exclamation-triangle me-1"></i><?= e($fe) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>
                <i class="bi bi-check2-all me-2 text-success"></i>
                Upload Results — <?= $uploaded ?> of <?= count($results) ?> succeeded
            </strong>
            <?php if ($totalSaved > 1024): ?>
            <span class="opt-badge"><i class="bi bi-lightning-charge-fill me-1"></i>Saved <?= formatBytes($totalSaved) ?> total</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php foreach ($results as $r): ?>
            <div class="result-row <?= $r['ok'] ? 'ok' : 'err' ?>">
                <i class="bi bi-<?= $r['ok'] ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
                <span class="fw-semibold"><?= e($r['name']) ?></span>
                <span><?= e($r['msg']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upload form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">

                <?php if ($type === 'gallery'): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">— Select a category —</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c) ?>" <?= (($_POST['category'] ?? '') === $c) ? 'selected' : '' ?>>
                            <?= e($catLabels[$c]) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <input type="hidden" name="category" value="event">
                <div class="alert alert-info mb-3" style="font-size:.88rem;">
                    <i class="bi bi-info-circle me-2"></i>
                    Upload a single image for an event. Stored in <code>admin/uploads/events/</code>.
                    Max dimensions: 1200 × 800 px.
                </div>
                <?php endif; ?>

                <!-- Optimiser info badge -->
                <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded" style="background:#f0f6ff;border:1px solid #c8dff5;">
                    <i class="bi bi-lightning-charge-fill text-primary fs-5"></i>
                    <div style="font-size:.85rem;">
                        <strong>Auto-Optimisation Active</strong><br>
                        <span class="text-muted">
                            JPEG/WEBP re-encoded at quality 82 · PNG converted to JPEG if no transparency ·
                            <?= $type === 'event' ? 'Max 1200×800px' : 'Max 1600×1200px' ?> ·
                            EXIF stripped · Typically 40–60% size reduction
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <?= $type === 'gallery' ? 'Images (max 20 · 8 MB each · JPG PNG WEBP GIF)' : 'Image (1 file · 8 MB max · JPG PNG WEBP)' ?>
                    </label>
                    <div class="drop-zone" id="dropZone">
                        <i class="bi bi-cloud-upload fs-1 d-block mb-2 text-primary"></i>
                        <strong>Drag &amp; drop here</strong>, or click to browse
                        <input type="file" name="images[]" id="imgInput"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            <?= $type === 'gallery' ? 'multiple' : '' ?>
                            style="display:none">
                    </div>
                    <div id="preview-grid"></div>
                    <div id="fileCount" class="text-muted mt-1" style="font-size:.82rem;"></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-upload me-2"></i>Upload &amp; Optimise
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="clearBtn">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Optimisation explainer -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white">
            <strong><i class="bi bi-info-circle me-2 text-primary"></i>How Optimisation Works</strong>
        </div>
        <div class="card-body" style="font-size:.88rem;">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 rounded" style="background:#fff3cd;">
                        <strong>JPEG</strong><br>
                        Re-encoded at quality 82. Visually identical to the original, typically 40–60% smaller. EXIF (GPS, camera data) stripped.
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 rounded" style="background:#d1fae5;">
                        <strong>PNG (opaque)</strong><br>
                        Converted to JPEG — photos and diagrams without transparency are up to 80% smaller with no visible difference.
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 rounded" style="background:#e0f2fe;">
                        <strong>PNG (transparent)</strong><br>
                        Kept as PNG with maximum compression (level 9). Transparency is always preserved exactly.
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="p-3 rounded" style="background:#f0f0f0;">
                        <strong>GIF / WEBP</strong><br>
                        GIFs saved as-is (animated GIF support preserved). WEBP re-encoded at quality 82.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dz      = document.getElementById('dropZone');
const inp     = document.getElementById('imgInput');
const grid    = document.getElementById('preview-grid');
const counter = document.getElementById('fileCount');
const clearBtn= document.getElementById('clearBtn');
const form    = document.getElementById('uploadForm');
const submitBtn = document.getElementById('submitBtn');

let selectedFiles = [];

dz.addEventListener('click', () => inp.click());
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', ()  => dz.classList.remove('drag-over'));
dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag-over');
    addFiles(Array.from(e.dataTransfer.files));
});
inp.addEventListener('change', () => addFiles(Array.from(inp.files)));

function addFiles(newFiles) {
    newFiles.forEach(f => {
        if (!f.type.startsWith('image/')) return;
        if (selectedFiles.length >= 20) return;
        selectedFiles.push(f);
    });
    renderPreviews();
}

function renderPreviews() {
    grid.innerHTML = '';
    selectedFiles.forEach((f, idx) => {
        const div = document.createElement('div');
        div.className = 'thumb';

        const img = document.createElement('img');
        const reader = new FileReader();
        reader.onload = e => img.src = e.target.result;
        reader.readAsDataURL(f);
        div.appendChild(img);

        const nm = document.createElement('div');
        nm.className = 'fname';
        nm.textContent = f.name;
        div.appendChild(nm);

        const rm = document.createElement('button');
        rm.className = 'rm';
        rm.type = 'button';
        rm.innerHTML = '✕';
        rm.title = 'Remove';
        rm.onclick = () => { selectedFiles.splice(idx, 1); renderPreviews(); };
        div.appendChild(rm);

        grid.appendChild(div);
    });

    const total = selectedFiles.reduce((s, f) => s + f.size, 0);
    counter.textContent = selectedFiles.length
        ? selectedFiles.length + ' file(s) selected — ' + formatBytes(total) + ' total (will be smaller after optimisation)'
        : '';
}

function formatBytes(b) {
    if (b < 1024)      return b + ' B';
    if (b < 1048576)   return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(2) + ' MB';
}

// Sync our JS file list back into the form via DataTransfer
form.addEventListener('submit', e => {
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert('Please select at least one image.');
        return;
    }
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    inp.files = dt.files;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading & Optimising…';
});

clearBtn.addEventListener('click', () => {
    selectedFiles = [];
    inp.value = '';
    renderPreviews();
});

// Sidebar toggle
const toggle  = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
document.addEventListener('click', e => {
    if (window.innerWidth < 768 && sidebar && toggle &&
        !sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});
</script>
<?php include 'session_warning.php'; ?>
</body>
</html>
