<?php
/**
 * admin/upload.php — Unified gallery + event image uploader
 * - Server-side MIME validation (no browser spoofing)
 * - Randomised safe filenames
 * - 5 MB cap per file, max 20 files
 * - Writes to admin/uploads/gallery/ (gallery) or admin/uploads/events/ (events)
 */
session_start();
require "db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$type = $_GET['type'] ?? 'gallery'; // gallery | event
if (!in_array($type, ['gallery','event'])) $type = 'gallery';

$categories = ['hairdressing','bakerystudies','catering','cuttingdesigning',
               'interiorhome_decor','beautytherapy','computers',
               'weldingsheet_metalwork','woodwork','eventsdecor','graduation'];
$catLabels  = [
    'hairdressing'=>'Hairdressing','bakerystudies'=>'Bakery Studies',
    'catering'=>'Catering','cuttingdesigning'=>'Cutting & Designing',
    'interiorhome_decor'=>'Interior Home Decor','beautytherapy'=>'Beauty Therapy',
    'computers'=>'Computers','weldingsheet_metalwork'=>'Welding & Sheet Metalwork',
    'woodwork'=>'Woodwork','eventsdecor'=>'Events Decor','graduation'=>'Graduation'];

$allowedMimes = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
$baseDir      = $type === 'event'
    ? __DIR__ . '/uploads/events/'
    : __DIR__ . '/uploads/gallery/';

if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

$msg = ''; $msgType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = $_POST['category'] ?? '';

    if ($type === 'gallery' && !in_array($cat, $categories, true)) {
        $msg = "Invalid category."; $msgType = 'danger';
    } elseif (empty($_FILES['images']['name'][0])) {
        $msg = "Please select at least one image."; $msgType = 'warning';
    } else {
        $files = $_FILES['images'];
        $count = count($files['name']);
        $uploaded = 0; $errors = [];

        if ($count > 20) { $msg = "Maximum 20 images per upload."; $msgType = 'warning'; }
        else {
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = "File #".($i+1).": upload error (".$files['error'][$i].")"; continue; }
                if ($files['size'][$i] > 5*1024*1024) {
                    $errors[] = htmlspecialchars($files['name'][$i]).": exceeds 5 MB."; continue; }
                $finfo    = new finfo(FILEINFO_MIME_TYPE);
                $mime     = $finfo->file($files['tmp_name'][$i]);
                if (!isset($allowedMimes[$mime])) {
                    $errors[] = htmlspecialchars($files['name'][$i]).": not a valid image type."; continue; }
                $ext      = $allowedMimes[$mime];
                $prefix   = $type === 'event' ? 'event' : $cat;
                $safeName = $prefix.'_'.bin2hex(random_bytes(8)).'.'.$ext;
                if (move_uploaded_file($files['tmp_name'][$i], $baseDir.$safeName)) { $uploaded++; }
                else { $errors[] = "Could not save ".htmlspecialchars($files['name'][$i])."."; }
            }
            if ($uploaded > 0) { $msg = "$uploaded image(s) uploaded successfully!"; $msgType='success'; }
            if (!empty($errors)) { $msg .= ($msg?"<br>":"").implode("<br>",$errors); $msgType=$uploaded>0?'warning':'danger'; }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Upload — <?=ucfirst($type)?></title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<style>
body{background:#f8f9fa;}
.wrap{max-width:740px;margin:36px auto;background:#fff;padding:32px 36px;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,.1);}
.hbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;}
.hbar img{height:52px;}
.drop-zone{border:2px dashed #adb5bd;border-radius:8px;padding:26px;text-align:center;color:#6c757d;cursor:pointer;transition:border-color .2s,background .2s;}
.drop-zone.hover{border-color:#0d6efd;background:#f0f6ff;}
#preview{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
#preview img{width:90px;height:68px;object-fit:cover;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.12);}
</style></head><body>
<div class="wrap">
    <div class="hbar">
        <img src="../img/logo.png" alt="Logo">
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
            <a href="upload.php?type=gallery" class="btn btn-outline-primary btn-sm">Gallery Upload</a>
            <a href="upload.php?type=event" class="btn btn-outline-success btn-sm">Event Upload</a>
            <a href="../index.html" class="btn btn-info btn-sm">Website</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>

    <h4 class="mb-4 text-center">Upload to <?=ucfirst($type)?></h4>

    <?php if(!empty($msg)): ?><div class="alert alert-<?=$msgType?>"><?=$msg?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php if($type==='gallery'): ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Category</label>
            <select name="category" class="form-select" required>
                <?php foreach($categories as $c): ?>
                <option value="<?=$c?>" <?=(($_POST['category']??'')===$c)?'selected':''?>><?=$catLabels[$c]?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="category" value="event">
        <p class="text-muted">Uploading image for an event. Image will be stored in <code>admin/uploads/events/</code>.</p>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Images <small class="text-muted">(max 20 files · 5 MB each · JPG PNG GIF WEBP)</small></label>
            <div class="drop-zone" id="dropZone">
                <i class="bi bi-cloud-upload fs-2 d-block mb-1"></i>
                Drag &amp; drop here, or <strong>click to browse</strong>
                <input type="file" name="images[]" id="imgInput" accept="image/jpeg,image/png,image/gif,image/webp" multiple required style="display:none;">
            </div>
            <div id="preview"></div>
        </div>
        <button class="btn btn-primary w-100 mt-2">Upload</button>
    </form>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
<script>
const dz=document.getElementById('dropZone'),inp=document.getElementById('imgInput'),prev=document.getElementById('preview');
dz.addEventListener('click',()=>inp.click());
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('hover');});
dz.addEventListener('dragleave',()=>dz.classList.remove('hover'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('hover');showPrev(e.dataTransfer.files);});
inp.addEventListener('change',()=>showPrev(inp.files));
function showPrev(files){
    prev.innerHTML='';
    Array.from(files).forEach(f=>{const r=new FileReader();r.onload=e=>{const i=document.createElement('img');i.src=e.target.result;i.title=f.name;prev.appendChild(i);};r.readAsDataURL(f);});
}
</script>
</body></html>
