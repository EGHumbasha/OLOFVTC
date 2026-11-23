<?php
/**
 * setup/install.php — One-time database installer
 * Run once, then DELETE this entire setup/ folder.
 */

// Security: block if a lock file exists (already ran)
$lockFile = __DIR__ . '/.installed.lock';
if (file_exists($lockFile)) {
    die('<h2 style="font-family:sans-serif;color:#dc3545;padding:40px;">⛔ Installer has already been run. Delete this setup/ folder from your server.</h2>');
}

require_once __DIR__ . '/../admin/config.php';

$results = [];

// Attempt DB connection
try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $results[] = ['ok', 'Database', 'Connected successfully to <code>'.DB_NAME.'</code> on <code>'.DB_HOST.'</code>'];
} catch (PDOException $e) {
    ?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Install Error</title>
    <style>body{font-family:sans-serif;padding:40px;background:#fff3cd;}h2{color:#856404;}</style></head><body>
    <h2>⚠ Database Connection Failed</h2>
    <p><?= htmlspecialchars($e->getMessage()) ?></p>
    <p>Check credentials in <code>admin/config.php</code> and try again.</p>
    </body></html><?php
    exit;
}

// ── Table definitions ────────────────────────────────────────────────────────

$tables = [
    'admin_users' => "CREATE TABLE IF NOT EXISTS `admin_users` (
        `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `email`                VARCHAR(180)  NOT NULL UNIQUE,
        `password`             VARCHAR(255)  NOT NULL,
        `role`                 ENUM('superadmin','admin','editor') NOT NULL DEFAULT 'admin',
        `is_active`            TINYINT(1)    NOT NULL DEFAULT 1,
        `must_change_password` TINYINT(1)    NOT NULL DEFAULT 0,
        `created_at`           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'events' => "CREATE TABLE IF NOT EXISTS `events` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title`       VARCHAR(255)  NOT NULL,
        `description` TEXT,
        `image`       VARCHAR(300)  DEFAULT NULL,
        `event_date`  DATE          NOT NULL,
        `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'newsletter_subscribers' => "CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
        `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `email`         VARCHAR(180)  NOT NULL UNIQUE,
        `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
        `subscribed_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    'contact_messages' => "CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name`         VARCHAR(120)  NOT NULL,
        `email`        VARCHAR(180)  NOT NULL,
        `phone`        VARCHAR(30)   DEFAULT NULL,
        `subject`      VARCHAR(220)  NOT NULL,
        `message`      TEXT          NOT NULL,
        `is_read`      TINYINT(1)    NOT NULL DEFAULT 0,
        `submitted_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $results[] = ['ok', "Table: <code>{$name}</code>", 'Created (or already exists)'];
    } catch (PDOException $e) {
        $results[] = ['err', "Table: <code>{$name}</code>", htmlspecialchars($e->getMessage())];
    }
}

// ── Default superadmin ───────────────────────────────────────────────────────

$adminEmail = 'admin@ourladyoffatimavtc.co.zw';
$adminPass  = 'Admin@2025';
$adminHash  = password_hash($adminPass, PASSWORD_DEFAULT);

try {
    // Insert only if not already present
    $check = $pdo->prepare('SELECT id FROM admin_users WHERE email=?');
    $check->execute([$adminEmail]);
    if ($check->fetch()) {
        $results[] = ['warn', 'Default superadmin', "User <code>{$adminEmail}</code> already exists — skipped."];
    } else {
        $ins = $pdo->prepare("INSERT INTO admin_users (email,password,role,is_active,must_change_password) VALUES(?,?,?,1,0)");
        $ins->execute([$adminEmail, $adminHash, 'superadmin']);
        $results[] = ['ok', 'Default superadmin', "Created <code>{$adminEmail}</code> with password <code>{$adminPass}</code> — <strong>change this immediately after login!</strong>"];
    }
} catch (PDOException $e) {
    $results[] = ['err', 'Default superadmin', htmlspecialchars($e->getMessage())];
}

// ── Upload directories ───────────────────────────────────────────────────────

$dirs = [
    __DIR__.'/../admin/uploads/gallery',
    __DIR__.'/../admin/uploads/events',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (@mkdir($dir, 0755, true)) {
            $results[] = ['ok', 'Directory', 'Created <code>'.basename(dirname($dir)).'/'.basename($dir).'</code>'];
        } else {
            $results[] = ['warn', 'Directory', 'Could not create <code>'.basename($dir).'</code> — create it manually and set permissions to 755.'];
        }
    } else {
        $results[] = ['ok', 'Directory', 'Already exists: <code>'.basename(dirname($dir)).'/'.basename($dir).'</code>'];
    }
}

// Write lock file
@file_put_contents($lockFile, date('Y-m-d H:i:s'));

$allOk = !in_array('err', array_column($results, 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Install — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;}
.wrap{width:100%;max-width:700px;}
.header{background:linear-gradient(135deg,#005f99,#003d66);color:#fff;padding:36px 40px;border-radius:12px 12px 0 0;text-align:center;}
.header img{height:60px;margin-bottom:12px;}
.header h1{font-size:1.6rem;margin-bottom:4px;}
.header p{opacity:.8;font-size:.9rem;}
.body{background:#fff;padding:36px 40px;border-radius:0 0 12px 12px;box-shadow:0 8px 30px rgba(0,0,0,.12);}
table{width:100%;border-collapse:collapse;margin-bottom:28px;}
th{text-align:left;padding:10px 14px;background:#f0f4f8;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:#555;}
td{padding:12px 14px;border-bottom:1px solid #eee;font-size:.9rem;vertical-align:top;}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:700;}
.badge-ok{background:#d1fae5;color:#065f46;}
.badge-err{background:#fee2e2;color:#991b1b;}
.badge-warn{background:#fef3c7;color:#92400e;}
.alert{border-radius:8px;padding:18px 22px;margin-bottom:20px;font-size:.92rem;display:flex;gap:12px;align-items:flex-start;}
.alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
.alert-warning{background:#fef3c7;color:#92400e;border:1px solid #fcd34d;}
.alert-icon{font-size:1.4rem;flex-shrink:0;}
.cred-box{background:#fff3cd;border:1px solid #fcd34d;border-radius:8px;padding:16px 20px;margin-bottom:24px;font-size:.9rem;}
.cred-box strong{display:block;margin-bottom:6px;color:#856404;}
.login-btn{display:inline-block;background:#005f99;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:1rem;margin-top:8px;}
.login-btn:hover{background:#004a7a;}
code{background:#f0f4f8;padding:2px 6px;border-radius:4px;font-size:.85em;}
@media(max-width:600px){.header,.body{padding:24px 20px;}}
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>⚙ Database Installer</h1>
        <p>Our Lady of Fatima Vocational Training Centre — Admin Setup</p>
    </div>
    <div class="body">

        <?php if ($allOk): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✅</span>
            <div><strong>Installation complete!</strong><br>All tables were created and the default admin account is ready. <strong>Delete the <code>setup/</code> folder from your server immediately.</strong></div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <span class="alert-icon">⚠</span>
            <div><strong>Installation completed with warnings or errors.</strong><br>Review the table below and resolve any errors before proceeding.</div>
        </div>
        <?php endif; ?>

        <table>
            <thead><tr><th style="width:90px;">Status</th><th style="width:220px;">Item</th><th>Detail</th></tr></thead>
            <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
                <td>
                    <?php if($r[0]==='ok'): ?>
                    <span class="badge badge-ok">✓ OK</span>
                    <?php elseif($r[0]==='err'): ?>
                    <span class="badge badge-err">✗ Error</span>
                    <?php else: ?>
                    <span class="badge badge-warn">⚠ Warn</span>
                    <?php endif; ?>
                </td>
                <td><?= $r[1] ?></td>
                <td><?= $r[2] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cred-box">
            <strong>🔑 Default Login Credentials</strong>
            <div><strong>URL:</strong> <a href="../admin/login.php" target="_blank">/admin/login.php</a></div>
            <div><strong>Email:</strong> admin@ourladyoffatimavtc.co.zw</div>
            <div><strong>Password:</strong> Admin@2025</div>
            <div style="margin-top:8px;color:#dc3545;font-weight:600;">⚠ Change this password immediately after first login!</div>
        </div>

        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:16px 20px;margin-bottom:24px;font-size:.9rem;">
            <strong style="color:#991b1b;">🗑 IMPORTANT — Delete This Folder</strong><br>
            For security, delete the entire <code>setup/</code> directory from your server after setup is complete. Leaving it accessible is a critical security risk.
        </div>

        <?php if ($allOk): ?>
        <a href="../admin/login.php" class="login-btn">→ Go to Admin Login</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
