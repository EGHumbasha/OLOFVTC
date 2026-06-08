<?php
session_start();
require "db.php";
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Stats
$totalEvents    = $conn->query("SELECT COUNT(*) FROM events")->fetchColumn();
$upcomingEvents = $conn->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()")->fetchColumn();
$totalMessages  = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadMessages = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$subscribers    = $conn->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active=1")->fetchColumn();
$totalUsers     = $conn->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();

// Count gallery images
$galleryCount = count(glob(__DIR__.'/uploads/gallery/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: []);

// Recent messages
$recentMsgs = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC LIMIT 5")->fetchAll();

// Recent events
$recentEvents = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent newsletter
$recentSubs = $conn->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT 5")->fetchAll();

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Admin Dashboard — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head><body>

<!-- Sidebar toggle (mobile) -->
<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="brand">
        <img src="../img/logo.png" alt="Logo">
        <div>
            <div style="font-weight:700;font-size:.9rem;">Fatima VTC</div>
            <div style="font-size:.75rem;opacity:.6;">Admin Panel</div>
        </div>
    </div>
    <a href="dashboard.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="upload.php?type=gallery"><i class="bi bi-images me-2"></i>Gallery Uploads</a>
    <a href="upload.php?type=event"><i class="bi bi-cloud-upload me-2"></i>Event Images</a>
    <a href="events.php"><i class="bi bi-calendar-event me-2"></i>Manage Events</a>
    <a href="messages.php"><i class="bi bi-envelope me-2"></i>Messages <?php if($unreadMessages>0): ?><span class="badge bg-danger ms-1"><?=$unreadMessages?></span><?php endif; ?></a>
    <a href="subscribers.php"><i class="bi bi-people me-2"></i>Subscribers</a>
    <?php if(($_SESSION['role']??'')==='superadmin'): ?>
    <a href="users.php"><i class="bi bi-person-gear me-2"></i>Manage Users</a>
    <?php endif; ?>
    <a href="../index.html"><i class="bi bi-house me-2"></i>View Website</a>
    <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <!-- Top bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-700">Dashboard</h4>
            <small class="text-muted">Welcome back, <?=e($_SESSION['email']??'Admin')?>  — <?=date('l, d F Y')?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="upload.php?type=gallery" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload Images</a>
            <a href="events.php" class="btn btn-success btn-sm"><i class="bi bi-plus-circle me-1"></i>Add Event</a>
        </div>
    </div>

    <?php if($flash): ?>
    <div class="alert alert-<?=$flash[0]?> flash-bar alert-dismissible fade show">
        <?=htmlspecialchars($flash[1])?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#005f99;"><i class="bi bi-calendar-event"></i></div>
                <div><div class="num"><?=$totalEvents?></div><div class="lbl">Total Events</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#28a745;"><i class="bi bi-calendar-check"></i></div>
                <div><div class="num"><?=$upcomingEvents?></div><div class="lbl">Upcoming Events</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#ff8c00;"><i class="bi bi-envelope"></i></div>
                <div><div class="num"><?=$totalMessages?></div><div class="lbl">Messages <span class="badge bg-danger"><?=$unreadMessages?> new</span></div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#6f42c1;"><i class="bi bi-people"></i></div>
                <div><div class="num"><?=$subscribers?></div><div class="lbl">Newsletter Subscribers</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#17a2b8;"><i class="bi bi-images"></i></div>
                <div><div class="num"><?=$galleryCount?></div><div class="lbl">Gallery Images</div></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="icon" style="background:#dc3545;"><i class="bi bi-person-badge"></i></div>
                <div><div class="num"><?=$totalUsers?></div><div class="lbl">Admin Users</div></div>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="row g-3 mb-4">
        <div class="col-12"><h6 class="text-muted text-uppercase fw-bold" style="font-size:.8rem;letter-spacing:1px;">Quick Actions</h6></div>
        <div class="col-6 col-md-3"><a href="upload.php?type=gallery" class="btn btn-outline-primary w-100 py-3"><i class="bi bi-images d-block fs-4 mb-1"></i>Gallery Upload</a></div>
        <div class="col-6 col-md-3"><a href="upload.php?type=event" class="btn btn-outline-success w-100 py-3"><i class="bi bi-calendar-plus d-block fs-4 mb-1"></i>Event Image</a></div>
        <div class="col-6 col-md-3"><a href="events.php" class="btn btn-outline-warning w-100 py-3"><i class="bi bi-calendar-event d-block fs-4 mb-1"></i>Manage Events</a></div>
        <div class="col-6 col-md-3"><a href="messages.php" class="btn btn-outline-danger w-100 py-3 position-relative"><i class="bi bi-envelope d-block fs-4 mb-1"></i>View Messages<?php if($unreadMessages>0): ?><span class="position-absolute top-0 end-0 m-1 badge bg-danger"><?=$unreadMessages?></span><?php endif;?></a></div>
    </div>

    <div class="row g-4">
        <!-- Recent messages -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-envelope me-2 text-primary"></i>Recent Messages</strong>
                    <a href="messages.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($recentMsgs)): ?>
                    <p class="text-muted text-center py-4">No messages yet.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($recentMsgs as $msg): ?>
                        <div class="list-group-item <?=$msg['is_read']?'':'bg-light fw-bold'?>">
                            <div class="d-flex justify-content-between">
                                <span><?=e($msg['name'])?> <small class="text-muted fw-normal">— <?=e($msg['subject'])?></small></span>
                                <small class="text-muted"><?=date('d M',strtotime($msg['submitted_at']))?></small>
                            </div>
                            <small class="text-muted"><?=e(substr($msg['message'],0,80))?>...</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent events -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-calendar-event me-2 text-success"></i>Recent Events</strong>
                    <a href="events.php" class="btn btn-sm btn-outline-success">Manage</a>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($recentEvents)): ?>
                    <p class="text-muted text-center py-4">No events yet.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($recentEvents as $ev): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?=e($ev['title'])?></div>
                                <small class="text-muted"><?=date('d M Y',strtotime($ev['event_date']))?></small>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="view_event.php?id=<?=$ev['id']?>" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                <a href="edit_event.php?id=<?=$ev['id']?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="delete_event.php?id=<?=$ev['id']?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this event?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent subscribers -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-people me-2 text-purple" style="color:#6f42c1;"></i>Recent Subscribers</strong>
                    <a href="subscribers.php" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($recentSubs)): ?>
                    <p class="text-muted text-center py-4">No subscribers yet.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($recentSubs as $sub): ?>
                        <div class="list-group-item d-flex justify-content-between">
                            <span><i class="bi bi-envelope-check me-2 text-success"></i><?=e($sub['email'])?></span>
                            <small class="text-muted"><?=date('d M Y',strtotime($sub['subscribed_at']))?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System info -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong><i class="bi bi-info-circle me-2 text-info"></i>System Info</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">PHP Version</td><td><?=phpversion()?></td></tr>
                        <tr><td class="text-muted">Server Time</td><td><?=date('d M Y H:i:s')?></td></tr>
                        <tr><td class="text-muted">Upload Dir Gallery</td><td><?=is_writable(__DIR__.'/uploads/gallery/')?'<span class="badge bg-success">Writable</span>':'<span class="badge bg-danger">Not Writable</span>'?></td></tr>
                        <tr><td class="text-muted">Upload Dir Events</td><td><?=is_writable(__DIR__.'/uploads/events/')?'<span class="badge bg-success">Writable</span>':'<span class="badge bg-danger">Not Writable</span>'?></td></tr>
                        <tr><td class="text-muted">Logged in as</td><td><?=e($_SESSION['email']??'')?>  <span class="badge bg-primary"><?=e($_SESSION['role']??'')?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggle=document.getElementById('sidebarToggle'),sidebar=document.getElementById('sidebar');
if(toggle) toggle.addEventListener('click',()=>sidebar.classList.toggle('open'));
document.addEventListener('click',e=>{if(window.innerWidth<768&&!sidebar.contains(e.target)&&!toggle.contains(e.target)) sidebar.classList.remove('open');});
</script>
</body></html>
