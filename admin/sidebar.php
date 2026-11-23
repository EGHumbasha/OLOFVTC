<?php if(session_status()==PHP_SESSION_NONE) session_start(); ?>
<div class="sidebar" id="sidebar">
    <div class="brand">
        <img src="../img/logo.png" alt="Logo">
        <div>
            <div style="font-weight:700;font-size:.9rem;">Fatima VTC</div>
            <div style="font-size:.75rem;opacity:.6;">Admin Panel</div>
        </div>
    </div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="upload.php?type=gallery"><i class="bi bi-images me-2"></i>Gallery Uploads</a>
    <a href="upload.php?type=event"><i class="bi bi-cloud-upload me-2"></i>Event Images</a>
    <a href="events.php"><i class="bi bi-calendar-event me-2"></i>Manage Events</a>
    <a href="messages.php"><i class="bi bi-envelope me-2"></i>Messages</a>
    <a href="subscribers.php"><i class="bi bi-people me-2"></i>Subscribers</a>
    <?php if(isset($_SESSION['role']) && $_SESSION['role']==='superadmin'): ?>
    <a href="users.php"><i class="bi bi-person-gear me-2"></i>Manage Users</a>
    <a href="site_settings.php" class="site-editor-link">
        <i class="bi bi-lightning-charge-fill me-2" style="color:#a78bfa;"></i>Site Editor
    </a>
    <?php endif; ?>
    <a href="../index.html"><i class="bi bi-house me-2"></i>View Website</a>
    <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
.sidebar .site-editor-link {
    background: linear-gradient(135deg, rgba(123,47,247,.18), rgba(0,95,153,.12));
}
.sidebar .site-editor-link:hover,
.sidebar .site-editor-link.active {
    background: linear-gradient(135deg, rgba(123,47,247,.32), rgba(0,95,153,.22));
    border-left-color: #7b2ff7 !important;
}
</style>
