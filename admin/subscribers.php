<?php
session_start(); require "db.php";
if(!isset($_SESSION['admin_id'])){header("Location: login.php");exit;}

if(isset($_GET['delete'])){
    $conn->prepare("DELETE FROM newsletter_subscribers WHERE id=?")->execute([intval($_GET['delete'])]);
    $_SESSION['flash']=['success','Subscriber removed.'];
    header("Location: subscribers.php"); exit;
}
if(isset($_GET['toggle'])){
    $id=intval($_GET['toggle']);
    $cur=$conn->prepare("SELECT is_active FROM newsletter_subscribers WHERE id=?");
    $cur->execute([$id]); $row=$cur->fetch();
    if($row){ $conn->prepare("UPDATE newsletter_subscribers SET is_active=? WHERE id=?")->execute([$row['is_active']?0:1,$id]); }
    header("Location: subscribers.php"); exit;
}

$subs=$conn->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll();
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Subscribers — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head><body>
<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<div class="sidebar" id="sidebar">
    <div class="brand"><img src="../img/logo.png" alt="Logo"><div><div style="font-weight:700;font-size:.9rem;">Fatima VTC</div><div style="font-size:.75rem;opacity:.6;">Admin Panel</div></div></div>
    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a href="upload.php?type=gallery"><i class="bi bi-images me-2"></i>Gallery Uploads</a>
    <a href="events.php"><i class="bi bi-calendar-event me-2"></i>Manage Events</a>
    <a href="messages.php"><i class="bi bi-envelope me-2"></i>Messages</a>
    <a href="subscribers.php" class="active"><i class="bi bi-people me-2"></i>Subscribers</a>
    <?php if(($_SESSION['role']??'')==='superadmin'): ?><a href="users.php"><i class="bi bi-person-gear me-2"></i>Manage Users</a><?php endif; ?>
    <a href="../index.html"><i class="bi bi-house me-2"></i>View Website</a>
    <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>
<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">Newsletter Subscribers <span class="badge bg-primary"><?=count($subs)?></span></h4>
        <a href="export_subscribers.php" class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Export CSV</a>
    </div>
    <?php if($flash): ?><div class="alert alert-<?=$flash[0]?> alert-dismissible fade show"><?=e($flash[1])?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(empty($subs)): ?>
    <div class="card border-0 shadow-sm text-center py-5"><i class="bi bi-people fs-1 text-muted d-block mb-2"></i><p class="text-muted">No subscribers yet.</p></div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>#</th><th>Email</th><th>Subscribed</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($subs as $i=>$s): ?>
                <tr>
                    <td><?=$i+1?></td>
                    <td><?=e($s['email'])?></td>
                    <td><?=date('d M Y',strtotime($s['subscribed_at']))?></td>
                    <td><?=$s['is_active']?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>'?></td>
                    <td>
                        <a href="?toggle=<?=$s['id']?>" class="btn btn-sm <?=$s['is_active']?'btn-outline-secondary':'btn-outline-success'?>"><?=$s['is_active']?'Deactivate':'Activate'?></a>
                        <a href="?delete=<?=$s['id']?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this subscriber?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const t=document.getElementById('sidebarToggle'),s=document.getElementById('sidebar');if(t)t.addEventListener('click',()=>s.classList.toggle('open'));</script>
</body></html>
