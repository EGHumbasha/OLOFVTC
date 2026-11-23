<?php
/**
 * admin/logout.php — Secure logout
 * Destroys session completely, clears cookie, redirects to login.
 */
session_start();

// Unset all session variables
session_unset();

// Destroy the session data on server
session_destroy();

// Overwrite the session cookie with an expired one
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $p['path'], $p['domain'],
        $p['secure'], $p['httponly']
    );
}

// Prevent caching — browser back button must not show admin page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Clear-Site-Data: "cache", "cookies", "storage"'); // modern browsers

header('Location: login.php');
exit;
