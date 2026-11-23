<?php
session_start();
require 'db.php';
require 'auth.php';

// Mark as read
if(isset($_GET['read'])){
    $conn->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([intval($_GET['read'])]);
    header("Location: messages.php"); exit;
}
// Delete
if(isset($_GET['delete'])){
    $conn->prepare("DELETE FROM contact_messages WHERE id=?")->execute([intval($_GET['delete'])]);
    $_SESSION['flash']=['success','Message deleted.'];
    header("Location: messages.php"); exit;
}
// Mark all read
if(isset($_GET['readall'])){
    $conn->query("UPDATE contact_messages SET is_read=1");
    header("Location: messages.php"); exit;
}

$msgs = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC")->fetchAll();
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Messages — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head><body>
<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<?php include 'sidebar.php'; ?>
<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">Contact Messages</h4>
        <a href="?readall=1" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Mark all messages as read?')"><i class="bi bi-check-all me-1"></i>Mark All Read</a>
    </div>
    <?php if($flash): ?><div class="alert alert-<?=$flash[0]?> alert-dismissible fade show"><?=e($flash[1])?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(empty($msgs)): ?>
    <div class="card border-0 shadow-sm text-center py-5"><i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i><p class="text-muted">No messages yet.</p></div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($msgs as $m): ?>
                <tr class="<?=$m['is_read']?'':'table-warning'?>">
                    <td class="fw-semibold"><?=e($m['name'])?></td>
                    <td><a href="mailto:<?=e($m['email'])?>"><?=e($m['email'])?></a></td>
                    <td><?=e($m['subject'])?></td>
                    <td><?=date('d M Y',strtotime($m['submitted_at']))?></td>
                    <td><?=$m['is_read']?'<span class="badge bg-secondary">Read</span>':'<span class="badge bg-warning text-dark">Unread</span>'?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#msgModal<?=$m['id']?>"><i class="bi bi-eye"></i></button>
                        <?php if(!$m['is_read']): ?><a href="?read=<?=$m['id']?>" class="btn btn-sm btn-outline-success" title="Mark read"><i class="bi bi-check"></i></a><?php endif; ?>
                        <a href="?delete=<?=$m['id']?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <!-- Modal -->
                <div class="modal fade" id="msgModal<?=$m['id']?>" tabindex="-1">
                    <div class="modal-dialog modal-lg"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title"><?=e($m['subject'])?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <p><strong>From:</strong> <?=e($m['name'])?> &lt;<a href="mailto:<?=e($m['email'])?>"><?=e($m['email'])?></a>&gt;</p>
                            <p><strong>Received:</strong> <?=date('d F Y H:i',strtotime($m['submitted_at']))?></p>
                            <hr>
                            <p style="white-space:pre-wrap;"><?=e($m['message'])?></p>
                        </div>
                        <div class="modal-footer">
                            <a href="mailto:<?=e($m['email'])?>" class="btn btn-primary"><i class="bi bi-reply me-1"></i>Reply via Email</a>
                            <?php if(!$m['is_read']): ?><a href="?read=<?=$m['id']?>" class="btn btn-success"><i class="bi bi-check me-1"></i>Mark Read</a><?php endif; ?>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div></div>
                </div>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const t=document.getElementById('sidebarToggle'),s=document.getElementById('sidebar');if(t)t.addEventListener('click',()=>s.classList.toggle('open'));</script>
<?php include 'session_warning.php'; ?>
</body></html>
