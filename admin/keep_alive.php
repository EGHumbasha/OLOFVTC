<?php
/**
 * admin/keep_alive.php — AJAX session refresh endpoint
 * Only resets inactivity timer. Does NOT extend absolute lifetime.
 * Requires POST + valid CSRF token.
 */
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'reason' => 'method_not_allowed']);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['ok' => false, 'reason' => 'not_authenticated']);
    exit;
}

// Verify CSRF
$token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'reason' => 'csrf_invalid']);
    exit;
}

// Check absolute session limit first
$absoluteMins = defined('SESSION_ABSOLUTE_MINS') ? SESSION_ABSOLUTE_MINS : 480;
if (isset($_SESSION['created_at']) && (time() - $_SESSION['created_at']) > $absoluteMins * 60) {
    echo json_encode(['ok' => false, 'reason' => 'absolute_timeout']);
    exit;
}

// Reset idle timer
$_SESSION['last_activity'] = time();

$idleMins     = defined('SESSION_TIMEOUT_MINS') ? SESSION_TIMEOUT_MINS : 20;
$expiresInMs  = $idleMins * 60 * 1000;

echo json_encode(['ok' => true, 'expiresInMs' => $expiresInMs]);
