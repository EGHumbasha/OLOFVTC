<?php
/**
 * admin/session_warning.php
 * Include just before </body> on every admin page.
 * Shows countdown warning 2 min before idle timeout.
 * Keep-alive pings include CSRF token.
 * On page refresh — auth.php resets last_activity, so refresh extends session.
 */
if (!isset($sessionExpiresInMs)) $sessionExpiresInMs = 20 * 60 * 1000;
$csrfForJs = htmlspecialchars($csrfToken ?? $_SESSION['csrf_token'] ?? '', ENT_QUOTES);
?>
<div id="sw-banner" style="display:none;position:fixed;bottom:22px;right:22px;z-index:99999;
    background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:16px 20px;
    box-shadow:0 4px 18px rgba(0,0,0,.18);font-family:'Nunito',sans-serif;
    max-width:310px;font-size:.87rem;color:#856404;">
    <div style="display:flex;align-items:flex-start;gap:10px;">
        <span style="font-size:1.3rem;flex-shrink:0;">⏱</span>
        <div>
            <strong>Session Expiring Soon</strong><br>
            Auto-logout in <strong id="sw-cd"></strong>.<br>
            <div style="margin-top:10px;display:flex;gap:8px;">
                <button onclick="swKeepAlive()"
                    style="background:#005f99;color:#fff;border:none;border-radius:6px;
                           padding:7px 14px;font-size:.82rem;font-weight:700;cursor:pointer;">
                    Stay Logged In
                </button>
                <a href="logout.php"
                    style="background:#f8d7da;color:#721c24;border-radius:6px;
                           padding:7px 14px;font-size:.82rem;font-weight:700;text-decoration:none;">
                    Log Out
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var WARN_BEFORE_MS = 2 * 60 * 1000;        // show warning 2 min before expiry
    var csrfToken      = '<?= $csrfForJs ?>';
    var expiresInMs    = <?= (int)$sessionExpiresInMs ?>;

    var banner  = document.getElementById('sw-banner');
    var cdEl    = document.getElementById('sw-cd');
    var wTimer  = null;
    var cInt    = null;
    var shown   = false;

    function scheduleWarning() {
        clearTimeout(wTimer);
        var showIn = expiresInMs - WARN_BEFORE_MS;
        if (showIn <= 0) {
            showWarning(expiresInMs);
        } else {
            wTimer = setTimeout(function () { showWarning(WARN_BEFORE_MS); }, showIn);
        }
    }

    function showWarning(msLeft) {
        shown = true;
        banner.style.display = 'block';
        clearInterval(cInt);
        cInt = setInterval(function () {
            msLeft -= 1000;
            if (msLeft <= 0) {
                clearInterval(cInt);
                cdEl.textContent = '0:00';
                window.location.href = 'logout.php';
                return;
            }
            var m = Math.floor(msLeft / 60000);
            var s = Math.floor((msLeft % 60000) / 1000);
            cdEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        }, 1000);
    }

    window.swKeepAlive = function () {
        var fd = new FormData();
        fd.append('_csrf', csrfToken);
        fetch('keep_alive.php', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.ok) {
                    clearInterval(cInt);
                    clearTimeout(wTimer);
                    banner.style.display = 'none';
                    shown       = false;
                    expiresInMs = d.expiresInMs;
                    scheduleWarning();
                } else {
                    // Absolute timeout or auth failure — force logout
                    window.location.href = 'logout.php';
                }
            })
            .catch(function () { window.location.reload(); });
    };

    scheduleWarning();
})();
</script>
