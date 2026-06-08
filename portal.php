<?php
/**
 * portal.php — Student Portal Login
 *
 * Logic:
 *   - Admin users (role = admin | superadmin) → admin/dashboard.php
 *   - Everyone else → coming-soon.php
 *
 * Until a real students table exists, any non-empty credentials
 * that don't match an admin account go to coming-soon.php.
 * Replace the bottom block with real student auth when ready.
 */

session_start();

// Already logged in as admin → skip portal entirely
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/admin/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (empty($email) || empty($pass)) {
        $error = "Please enter your email and password.";

    } else {
        // ── Check against admin_users table ──
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            // ── Valid admin credentials ──
            if (!$user['is_active']) {
                $error = "Your account has been suspended. Please contact the administrator.";
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['email']    = $user['email'];

                if ($user['must_change_password']) {
                    $_SESSION['force_change'] = true;
                    header("Location: admin/force_change_password.php");
                    exit;
                }

                header("Location: admin/dashboard.php");
                exit;
            }

        } else {
            // ── Non-admin or unrecognised credentials → Coming Soon ──
            // In future: check students table here before redirecting.
            header("Location: coming-soon.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Portal — Our Lady of Fatima VTC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* ── Animated gradient background ── */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(-45deg, #0f3460, #16213e, #005f99, #00b4d8);
            background-size: 400% 400%;
            animation: gradBG 18s ease infinite;
            z-index: -2;
        }

        /* ── Subtle logo watermark ── */
        body::after {
            content: "";
            position: fixed;
            top: 50%; left: 50%;
            width: 480px; height: 480px;
            background: url('img/logo.png') no-repeat center / contain;
            opacity: 0.04;
            transform: translate(-50%, -50%);
            z-index: -1;
            pointer-events: none;
        }

        @keyframes gradBG {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ── Floating particles ── */
        .particles { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatUp linear infinite;
        }

        @keyframes floatUp {
            0%   { transform: translateY(110vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.4; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        /* ── Card ── */
        .portal-card {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
            padding: 44px 40px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .55s ease, transform .55s ease;
        }
        .portal-card.visible { opacity: 1; transform: translateY(0); }
        .portal-card:hover { box-shadow: 0 16px 48px rgba(0,0,0,0.38); }

        /* ── Logo ── */
        .portal-logo img { width: 110px; margin-bottom: 12px; }

        .portal-card h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f3460;
            margin-bottom: 4px;
        }
        .portal-card .subtitle {
            font-size: .9rem;
            color: #6c757d;
            margin-bottom: 28px;
        }

        /* ── Form ── */
        .input-group-custom {
            position: relative;
            margin-bottom: 16px;
        }
        .input-group-custom .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: .95rem;
            pointer-events: none;
        }
        .input-group-custom input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: .95rem;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .input-group-custom input:focus {
            border-color: #005f99;
            box-shadow: 0 0 0 3px rgba(0,95,153,0.12);
        }

        /* Password toggle */
        .input-group-custom .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #adb5bd;
            font-size: .9rem;
            background: none;
            border: none;
            padding: 0;
        }
        .input-group-custom .toggle-pass:hover { color: #005f99; }

        /* ── Submit button ── */
        .btn-portal {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #005f99, #00b4d8);
            color: #fff;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity .2s, transform .2s, box-shadow .2s;
            margin-top: 4px;
        }
        .btn-portal:hover {
            opacity: .92;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,95,153,0.35);
        }
        .btn-portal:active { transform: translateY(0); }

        /* ── Error alert ── */
        .alert-portal {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .9rem;
            margin-bottom: 18px;
            text-align: left;
        }

        /* ── Footer links ── */
        .portal-footer {
            margin-top: 22px;
            font-size: .85rem;
            color: #6c757d;
        }
        .portal-footer a {
            color: #005f99;
            text-decoration: none;
            font-weight: 600;
        }
        .portal-footer a:hover { text-decoration: underline; }

        /* ── Back to site ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 18px;
            font-size: .85rem;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            transition: color .2s;
        }
        .back-link:hover { color: #fff; }

        @media (max-width: 480px) {
            .portal-card { padding: 32px 22px; margin: 16px; }
        }
    </style>
</head>
<body>

<!-- Floating particles -->
<div class="particles" id="particles"></div>

<div style="display:flex;flex-direction:column;align-items:center;">

    <div class="portal-card" id="portalCard">

        <div class="portal-logo">
            <img src="img/logo.png" alt="Our Lady of Fatima VTC">
        </div>

        <h2>Student Portal</h2>
        <p class="subtitle">Our Lady of Fatima Vocational Training Centre</p>

        <?php if ($error): ?>
            <div class="alert-portal">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="portal.php" novalidate>

            <div class="input-group-custom">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" placeholder="Email address" required
                       autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="input-group-custom">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="passwordField"
                       placeholder="Password" required autocomplete="current-password">
                <button type="button" class="toggle-pass" id="togglePass" title="Show/hide password">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-portal">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <div class="portal-footer">
            <p class="mb-1 mt-3">Forgot your password? <a href="contact.php">Contact Us</a></p>
            <p class="mb-0">Not yet enrolled? <a href="enroll.php">Apply Now</a></p>
        </div>
    </div>

    <a href="index.html" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Website
    </a>
</div>

<script>
// ── Fade-in card ──
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('portalCard').classList.add('visible');
});

// ── Password toggle ──
document.getElementById('togglePass').addEventListener('click', function () {
    const field   = document.getElementById('passwordField');
    const icon    = document.getElementById('eyeIcon');
    const showing = field.type === 'text';
    field.type    = showing ? 'password' : 'text';
    icon.className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
});

// ── Floating particles ──
(function () {
    const container = document.getElementById('particles');
    const count = 18;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 60 + 20;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            left:${Math.random() * 100}%;
            animation-duration:${Math.random() * 18 + 10}s;
            animation-delay:${Math.random() * 12}s;
        `;
        container.appendChild(p);
    }
})();
</script>

</body>
</html>
