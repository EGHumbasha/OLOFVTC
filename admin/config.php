<?php
/**
 * config.php — Centralised credentials & settings
 * Keep this file outside web root if possible, or deny via .htaccess.
 * NEVER commit to version control.
 */

// ── Database ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'ourladyo_db');
define('DB_USER', 'ourladyo_db');
define('DB_PASS', 'TqDApmSJBFCedCj5QgV8');

// ── SMTP ──────────────────────────────────────────────────
define('SMTP_HOST',   'smtp.gmail.com');
define('SMTP_PORT',   465);
define('SMTP_USER',   'iamgenton@gmail.com');
define('SMTP_PASS',   'jseg vkmb tfuf glgo');
define('SMTP_FROM',   'iamgenton@gmail.com');
define('SMTP_NAME',   'Our Lady of Fatima VTC');

// ── School details ────────────────────────────────────────
define('SCHOOL_NAME',        'Our Lady of Fatima Vocational Training Centre');
define('SCHOOL_EMAIL',       'info@ourladyoffatima.co.zw');
define('REGISTRATION_EMAIL', 'registration@ourladyoffatimavtc.co.zw');
define('SCHOOL_PHONE',       '+263 773 833 645');
define('SCHOOL_ADDRESS',     'Stand No. 24574 Pumula South, Bulawayo');
define('SITE_URL',           'https://ourladyoffatimavtc.co.zw');
