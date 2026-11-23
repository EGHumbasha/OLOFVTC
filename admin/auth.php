<?php
/**
 * admin/auth.php — Full session security guard
 *
 * Features:
 *   1. Session must exist with admin_id — redirect to login if not
 *   2. Force-change-password intercept
 *   3. Inactivity timeout (SESSION_TIMEOUT_MINS = 20 min)
 *   4. Absolute session lifetime (SESSION_ABSOLUTE_MINS = 480 min / 8 hours)
 *   5. IP binding — session invalidated if IP changes mid-session
 *   6. User-agent binding — extra hijack signal
 *   7. Session ID regeneration every 10 minutes (anti-fixation)
 *   8. Cache-Control headers — prevents browser back-button after logout
 *   9. CSRF token generation — available as $csrfToken for forms
 */

// ── Constants ────────────────────────────────────────────────────────────────
if (!defined('SESSION_TIMEOUT_MINS'))  define('SESSION_TIMEOUT_MINS',  20);   // idle logout
if (!defined('SESSION_ABSOLUTE_MINS')) define('SESSION_ABSOLUTE_MINS', 480);  // 8-hour hard limit
if (!defined('SESSION_REGEN_MINS'))    define('SESSION_REGEN_MINS',    10);   // ID regen interval

// ── Prevent caching of admin pages ───────────────────────────────────────────
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// ── Not logged in ─────────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['flash'] = ['warning', 'Please log in to access the admin panel.'];
    header('Location: login.php');
    exit;
}

// ── Force password change ─────────────────────────────────────────────────────
if (isset($_SESSION['force_change']) &&
    basename($_SERVER['PHP_SELF']) !== 'force_change_password.php') {
    header('Location: force_change_password.php');
    exit;
}

$now = time();

// ── Absolute session lifetime ─────────────────────────────────────────────────
if (isset($_SESSION['created_at']) &&
    ($now - $_SESSION['created_at']) > SESSION_ABSOLUTE_MINS * 60) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['warning',
        'Your session has expired after ' . SESSION_ABSOLUTE_MINS . ' minutes. Please log in again.'];
    header('Location: login.php');
    exit;
}

// ── Inactivity timeout ────────────────────────────────────────────────────────
if (isset($_SESSION['last_activity']) &&
    ($now - $_SESSION['last_activity']) > SESSION_TIMEOUT_MINS * 60) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['warning',
        'Your session expired after ' . SESSION_TIMEOUT_MINS . ' minutes of inactivity.'];
    header('Location: login.php');
    exit;
}

// ── IP binding ────────────────────────────────────────────────────────────────
$currentIp = $_SERVER['HTTP_X_FORWARDED_FOR']
    ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
    : ($_SERVER['REMOTE_ADDR'] ?? '');

if (isset($_SESSION['bound_ip']) && $_SESSION['bound_ip'] !== $currentIp) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['danger', 'Session terminated: IP address changed. Please log in again.'];
    header('Location: login.php');
    exit;
}

// ── User-agent binding ────────────────────────────────────────────────────────
$currentUa = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
if (isset($_SESSION['bound_ua']) && $_SESSION['bound_ua'] !== $currentUa) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['danger', 'Session terminated: browser fingerprint changed.'];
    header('Location: login.php');
    exit;
}

// ── Update timestamps ─────────────────────────────────────────────────────────
$_SESSION['last_activity'] = $now;

// ── Periodic session ID regeneration (anti-fixation) ─────────────────────────
if (!isset($_SESSION['regen_time'])) {
    $_SESSION['regen_time'] = $now;
} elseif ($now - $_SESSION['regen_time'] > SESSION_REGEN_MINS * 60) {
    session_regenerate_id(true);
    $_SESSION['regen_time'] = $now;
}

// ── CSRF token ────────────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ── Expose timing info for session_warning.php ────────────────────────────────
$sessionExpiresAt   = $_SESSION['last_activity'] + (SESSION_TIMEOUT_MINS * 60);
$sessionExpiresInMs = max(0, ($sessionExpiresAt - $now)) * 1000;

// ── Helper: verify CSRF token (call at top of POST handlers) ─────────────────
function verifyCsrf(): void {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token. Go back and try again.');
    }
}
