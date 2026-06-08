<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Coming Soon — Student Portal</title>
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
            font-family: 'Nunito', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            position: relative;
            color: #fff;
        }

        /* Animated background */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(-45deg, #0f3460, #16213e, #005f99, #533483);
            background-size: 400% 400%;
            animation: gradBG 20s ease infinite;
            z-index: -2;
        }

        @keyframes gradBG {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Stars */
        .stars { position: fixed; inset: 0; z-index: -1; pointer-events: none; }
        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: twinkle ease-in-out infinite;
        }
        @keyframes twinkle {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50%       { opacity: 0.9; transform: scale(1.4); }
        }

        /* ── Content ── */
        .content-wrap {
            max-width: 640px;
            padding: 32px 24px;
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .content-wrap.visible { opacity: 1; transform: translateY(0); }

        .logo-wrap img { width: 120px; margin-bottom: 20px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4)); }

        h1 {
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 12px;
        }
        h1 span {
            background: linear-gradient(90deg, #00d2ff, #7b2ff7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tagline {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.75);
            margin-bottom: 36px;
            line-height: 1.6;
        }

        /* ── Countdown ── */
        .countdown-wrap {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        .countdown-block {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
            border-radius: 14px;
            padding: 18px 22px;
            min-width: 90px;
            transition: transform .2s;
        }
        .countdown-block:hover { transform: translateY(-4px); }
        .countdown-block .num {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1;
            color: #fff;
        }
        .countdown-block .lbl {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.6);
            margin-top: 4px;
        }

        /* ── Notify form ── */
        .notify-form {
            display: flex;
            max-width: 420px;
            margin: 0 auto 32px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .notify-form input {
            flex: 1;
            min-width: 200px;
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            font-family: 'Nunito', sans-serif;
            font-size: .95rem;
            outline: none;
        }
        .notify-form button {
            padding: 12px 22px;
            background: linear-gradient(135deg, #00d2ff, #7b2ff7);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .2s;
            white-space: nowrap;
        }
        .notify-form button:hover { opacity: .88; transform: translateY(-2px); }
        #notify-msg { font-size: .9rem; margin-bottom: 16px; min-height: 22px; }

        /* ── Features list ── */
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 36px;
        }
        .feature-chip {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 999px;
            padding: 8px 18px;
            font-size: .88rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .feature-chip i { color: #00d2ff; }

        /* ── Back link ── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: .9rem;
            transition: color .2s;
        }
        .back-link:hover { color: #fff; }

        @media (max-width: 480px) {
            .countdown-block { padding: 14px 16px; min-width: 72px; }
            .countdown-block .num { font-size: 2rem; }
        }
    </style>
</head>
<body>

<div class="stars" id="stars"></div>

<div class="content-wrap" id="contentWrap">

    <div class="logo-wrap">
        <img src="img/logo.png" alt="Our Lady of Fatima VTC">
    </div>

    <h1>Student Portal<br><span>Coming Soon</span></h1>

    <p class="tagline">
        We're building a dedicated online space for our students.<br>
        Track your progress, access resources, and stay connected.
    </p>

    <!-- Countdown to a launch date — adjust TARGET_DATE below -->
    <div class="countdown-wrap">
        <div class="countdown-block"><div class="num" id="cd-days">--</div><div class="lbl">Days</div></div>
        <div class="countdown-block"><div class="num" id="cd-hours">--</div><div class="lbl">Hours</div></div>
        <div class="countdown-block"><div class="num" id="cd-mins">--</div><div class="lbl">Minutes</div></div>
        <div class="countdown-block"><div class="num" id="cd-secs">--</div><div class="lbl">Seconds</div></div>
    </div>

    <!-- Upcoming features -->
    <div class="features">
        <div class="feature-chip"><i class="fas fa-graduation-cap"></i> Course Materials</div>
        <div class="feature-chip"><i class="fas fa-calendar-check"></i> Attendance Tracker</div>
        <div class="feature-chip"><i class="fas fa-file-alt"></i> Results & Transcripts</div>
        <div class="feature-chip"><i class="fas fa-bell"></i> Announcements</div>
        <div class="feature-chip"><i class="fas fa-comments"></i> Messaging</div>
    </div>

    <!-- Notify me form -->
    <p style="color:rgba(255,255,255,0.7);font-size:.95rem;margin-bottom:12px;">
        Get notified when the portal launches:
    </p>
    <div class="notify-form">
        <input type="email" id="notifyEmail" placeholder="Your email address">
        <button id="notifyBtn"><i class="fas fa-paper-plane me-1"></i>Notify Me</button>
    </div>
    <div id="notify-msg"></div>

    <a href="index.html" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
</div>

<script>
// ── Fade in ──
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('contentWrap').classList.add('visible');
    generateStars();
    startCountdown();
});

// ── Star field ──
function generateStars() {
    const container = document.getElementById('stars');
    for (let i = 0; i < 120; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        const size = Math.random() * 3 + 1;
        s.style.cssText = `
            width:${size}px; height:${size}px;
            top:${Math.random()*100}%; left:${Math.random()*100}%;
            animation-duration:${Math.random()*4+2}s;
            animation-delay:${Math.random()*5}s;
        `;
        container.appendChild(s);
    }
}

// ── Countdown — set your target launch date here ──
function startCountdown() {
    // Change this date to your intended launch date (YYYY-MM-DD)
    const TARGET_DATE = new Date('2025-12-01T00:00:00');

    function update() {
        const now  = new Date();
        const diff = TARGET_DATE - now;

        if (diff <= 0) {
            ['cd-days','cd-hours','cd-mins','cd-secs'].forEach(id => {
                document.getElementById(id).textContent = '00';
            });
            return;
        }

        const days  = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        const mins  = Math.floor((diff % 3600000)  / 60000);
        const secs  = Math.floor((diff % 60000)    / 1000);

        document.getElementById('cd-days').textContent  = String(days).padStart(2,'0');
        document.getElementById('cd-hours').textContent = String(hours).padStart(2,'0');
        document.getElementById('cd-mins').textContent  = String(mins).padStart(2,'0');
        document.getElementById('cd-secs').textContent  = String(secs).padStart(2,'0');
    }

    update();
    setInterval(update, 1000);
}

// ── Notify me (wires into newsletter.php) ──
document.getElementById('notifyBtn').addEventListener('click', async function () {
    const email  = document.getElementById('notifyEmail').value.trim();
    const msgEl  = document.getElementById('notify-msg');

    if (!email) {
        msgEl.style.color = '#ffc107';
        msgEl.textContent = 'Please enter your email address.';
        return;
    }

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

    try {
        const body = new URLSearchParams({ email });
        const resp = await fetch('newsletter.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const data = await resp.json();
        msgEl.style.color    = data.success ? '#00d2ff' : '#ff6b6b';
        msgEl.textContent    = data.success
            ? "✓ You're on the list! We'll notify you when the portal launches."
            : data.message;
        if (data.success) document.getElementById('notifyEmail').value = '';
    } catch {
        msgEl.style.color = '#ff6b6b';
        msgEl.textContent = 'Something went wrong. Please try again.';
    } finally {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Notify Me';
    }
});
</script>

</body>
</html>
