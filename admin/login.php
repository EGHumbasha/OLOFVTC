<?php
/**
 * admin/login.php — Hardened login
 *
 * Security features:
 *   - Brute-force lockout: 5 failures per IP in 15 min → 15-min ban
 *   - Timing-safe password check (password_verify is already constant-time)
 *   - Session fixation prevention (session_regenerate_id on login)
 *   - IP + User-agent binding set on login
 *   - Absolute session start timestamp set on login
 *   - No username enumeration (same error for bad email or bad password)
 *   - Cache-Control: no-store on login page
 *   - CSRF token on login form (belt-and-suspenders)
 */

session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'db.php';

// ── Auto-create lockout table ─────────────────────────────────────────────────
$conn->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip           VARCHAR(45)  NOT NULL,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip   (ip),
    INDEX idx_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Lockout settings ──────────────────────────────────────────────────────────
define('MAX_ATTEMPTS',  5);
define('LOCKOUT_MINS',  15);
define('WINDOW_MINS',   15);

$ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')[0]);

function countAttempts(PDO $conn, string $ip): int {
    $s = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip=? AND attempted_at >= NOW() - INTERVAL " . WINDOW_MINS . " MINUTE");
    $s->execute([$ip]);
    return (int)$s->fetchColumn();
}
function recordAttempt(PDO $conn, string $ip): void {
    $conn->prepare("INSERT INTO login_attempts (ip) VALUES (?)")->execute([$ip]);
}
function clearAttempts(PDO $conn, string $ip): void {
    $conn->prepare("DELETE FROM login_attempts WHERE ip=?")->execute([$ip]);
}
function getLockExpiry(PDO $conn, string $ip): ?string {
    $s = $conn->prepare("SELECT attempted_at FROM login_attempts WHERE ip=? AND attempted_at >= NOW() - INTERVAL " . WINDOW_MINS . " MINUTE ORDER BY attempted_at ASC LIMIT 1 OFFSET " . (MAX_ATTEMPTS - 1));
    $s->execute([$ip]);
    $row = $s->fetch();
    return $row ? date('Y-m-d H:i:s', strtotime($row['attempted_at']) + LOCKOUT_MINS * 60) : null;
}

// Initialise CSRF token for the login form
if (empty($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

$attempts    = countAttempts($conn, $ip);
$isLocked    = false;
$lockMsg     = '';
$error       = '';
$lockMinutes = 0;

// Check if locked
if ($attempts >= MAX_ATTEMPTS) {
    $expiry = getLockExpiry($conn, $ip);
    if ($expiry && strtotime($expiry) > time()) {
        $isLocked    = true;
        $lockMinutes = (int)ceil((strtotime($expiry) - time()) / 60);
        $lockMsg     = "Too many failed login attempts. Try again in {$lockMinutes} minute" . ($lockMinutes === 1 ? '' : 's') . ".";
    } else {
        clearAttempts($conn, $ip);
        $attempts = 0;
    }
}

// ── Handle POST ───────────────────────────────────────────────────────────────
if (!$isLocked && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify login CSRF
    $postToken = $_POST['_lcsrf'] ?? '';
    if (!hash_equals($_SESSION['login_csrf'] ?? '', $postToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email']    ?? ''));
        $pass  =              $_POST['password'] ?? '';

        if ($email === '' || $pass === '') {
            $error = 'Please enter your email and password.';
        } else {
            $stmt = $conn->prepare('SELECT * FROM admin_users WHERE email=? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && $user['is_active'] && password_verify($pass, $user['password'])) {
                // ── Successful login ──
                clearAttempts($conn, $ip);

                // Destroy old session completely, start fresh (anti-fixation)
                session_regenerate_id(true);
                session_unset();

                $_SESSION['admin_id']    = $user['id'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['email']       = $user['email'];
                $_SESSION['created_at']  = time();
                $_SESSION['last_activity']= time();
                $_SESSION['regen_time']  = time();
                $_SESSION['bound_ip']    = $ip;
                $_SESSION['bound_ua']    = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
                $_SESSION['csrf_token']  = bin2hex(random_bytes(32));

                if ($user['must_change_password']) {
                    $_SESSION['force_change'] = true;
                    header('Location: force_change_password.php');
                    exit;
                }

                header('Location: dashboard.php');
                exit;

            } else {
                // ── Failed login ──
                // Always record attempt — even for suspended/unknown users
                // (don't reveal whether email exists)
                if (!$user || !$user['is_active']) {
                    // Fake timing to prevent user enumeration
                    password_verify('dummy', '$2y$12$invalidhashpadding00000000000000000000000000000000000000');
                }

                recordAttempt($conn, $ip);
                $attempts = countAttempts($conn, $ip);
                $remaining = MAX_ATTEMPTS - $attempts;

                if ($attempts >= MAX_ATTEMPTS) {
                    $isLocked    = true;
                    $lockMinutes = LOCKOUT_MINS;
                    $lockMsg     = 'Too many failed attempts. Your IP is locked out for ' . LOCKOUT_MINS . ' minutes.';
                } else {
                    $error = 'Invalid email or password.'
                        . ($remaining <= 2
                            ? " <strong>{$remaining} attempt" . ($remaining === 1 ? '' : 's') . " remaining</strong> before lockout."
                            : '');
                }
            }
        }
    }

    // Rotate login CSRF after each POST attempt
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

// Periodic cleanup of old attempts (1-in-50 chance per request)
if (mt_rand(1, 50) === 1) {
    $conn->exec("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 24 HOUR");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<link href="../css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;}
body{min-height:100vh;margin:0;display:flex;align-items:center;justify-content:center;overflow:hidden;font-family:"Nunito",sans-serif;}
body::before{content:"";position:fixed;inset:0;background:linear-gradient(-45deg,#6a11cb,#2575fc,#1fd1f9,#005f99);background-size:400% 400%;animation:gb 15s ease infinite;z-index:-2;}
body::after{content:"";position:fixed;top:50%;left:50%;width:380px;height:380px;background:url("../img/logo.png") no-repeat center/contain;opacity:.05;transform:translate(-50%,-50%);z-index:-1;}
@keyframes gb{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.card{background:rgba(255,255,255,.97);padding:40px 36px;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.25);width:100%;max-width:410px;text-align:center;opacity:0;transform:translateY(20px);transition:opacity .5s,transform .5s;}
.card.show{opacity:1;transform:translateY(0);}
.card img{width:110px;margin-bottom:14px;}
.card h3{font-weight:700;color:#1a1a2e;margin-bottom:22px;font-size:1.35rem;}
.form-control{border-radius:9px;padding:11px 44px 11px 14px;border:1.5px solid #dee2e6;font-family:"Nunito",sans-serif;font-size:.95rem;width:100%;}
.form-control:focus{outline:none;border-color:#2575fc;box-shadow:0 0 0 3px rgba(37,117,252,.14);}
.pw-wrap{position:relative;margin-bottom:14px;}
.pw-wrap .tgl{position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;color:#adb5bd;cursor:pointer;padding:0;font-size:.9rem;}
.pw-wrap .tgl:hover{color:#2575fc;}
.btn-login{border-radius:9px;padding:12px;background:linear-gradient(135deg,#2575fc,#6a11cb);border:none;color:#fff;font-weight:700;width:100%;font-size:1rem;font-family:"Nunito",sans-serif;transition:opacity .2s,transform .2s;margin-top:4px;cursor:pointer;}
.btn-login:hover{opacity:.9;transform:translateY(-2px);}
.btn-login:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.attempt-bar{height:4px;background:#e9ecef;border-radius:2px;margin-bottom:14px;overflow:hidden;}
.attempt-fill{height:100%;background:#dc3545;transition:width .4s;border-radius:2px;}
.lock-box{background:#fff3cd;border:1px solid #ffc107;border-radius:9px;padding:14px 16px;text-align:left;font-size:.87rem;color:#856404;margin-bottom:16px;}
.forgot{display:block;margin-top:14px;font-size:.85rem;color:#6c757d;text-decoration:none;}
.forgot:hover{color:#2575fc;}
.alert{border-radius:9px;font-size:.88rem;text-align:left;}
</style>
</head>
<body>
<div class="card" id="loginCard">
    <img src="../img/logo.png" alt="Logo">
    <h3>Admin Login</h3>

    <?php if ($isLocked): ?>
    <div class="lock-box">
        <i class="bi bi-shield-lock-fill me-2"></i>
        <strong>IP Locked</strong><br>
        <?= htmlspecialchars($lockMsg) ?>
    </div>

    <?php else: ?>

    <?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($attempts > 0): ?>
    <div class="attempt-bar">
        <div class="attempt-fill" style="width:<?= min(100, ($attempts/MAX_ATTEMPTS)*100) ?>%"></div>
    </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" novalidate autocomplete="off">
        <input type="hidden" name="_lcsrf" value="<?= htmlspecialchars($_SESSION['login_csrf']) ?>">

        <div class="mb-3 text-start">
            <input type="email" name="email" class="form-control" placeholder="Email address"
                required autocomplete="email"
                value="<?= htmlspecialchars(strtolower($_POST['email'] ?? '')) ?>">
        </div>

        <div class="pw-wrap">
            <input type="password" name="password" id="pwField" class="form-control"
                placeholder="Password" required autocomplete="current-password">
            <button type="button" class="tgl" id="togglePw" aria-label="Show/hide password">
                <i class="bi bi-eye" id="eyeIco"></i>
            </button>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
    </form>

    <a href="forgot_password.php" class="forgot">Forgot your password?</a>

    <?php endif; ?>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => document.getElementById('loginCard').classList.add('show'));

const pw  = document.getElementById('pwField');
const ico = document.getElementById('eyeIco');
document.getElementById('togglePw')?.addEventListener('click', () => {
    const s = pw.type === 'password';
    pw.type = s ? 'text' : 'password';
    ico.className = s ? 'bi bi-eye-slash' : 'bi bi-eye';
});

document.getElementById('loginForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in…';
});
</script>
</body>
</html>
