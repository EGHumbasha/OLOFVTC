<?php
/**
 * event.php — Public full event detail page
 * Linked from events.php cards via ?id=N
 */
ini_set('display_errors', 0);
require 'admin/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: events.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$ev = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ev) {
    header('Location: events.php');
    exit;
}

// Previous & next event links
$prev = $conn->prepare('SELECT id, title FROM events WHERE id < ? ORDER BY id DESC LIMIT 1');
$prev->execute([$id]);
$prevEv = $prev->fetch();

$next = $conn->prepare('SELECT id, title FROM events WHERE id > ? ORDER BY id ASC LIMIT 1');
$next->execute([$id]);
$nextEv = $next->fetch();

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$imgPath = '';
if (!empty($ev['image'])) {
    $f = basename($ev['image']);
    if (file_exists(__DIR__ . '/admin/uploads/events/' . $f)) {
        $imgPath = 'admin/uploads/events/' . $f;
    }
}

$dateFormatted = date('F d, Y', strtotime($ev['event_date']));
$isPast = strtotime($ev['event_date']) < strtotime(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
(function(){function h(){var s=document.getElementById('spinner');if(s){s.style.display='none';s.classList.remove('show');}}
if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',h);}else{h();}
window.addEventListener('load',h);setTimeout(h,3000);})();
</script>
<meta charset="utf-8">
<title><?= e($ev['title']) ?> — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="<?= e(substr(strip_tags($ev['description']), 0, 160)) ?>">
<link rel="icon" href="img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="lib/animate/animate.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
:root{--primary:#005f99;--accent:#ff8c00;--dark:#222;}
body{background:#f8f9fa;font-family:'Heebo',sans-serif;color:var(--dark);}
.text-primary{color:var(--primary)!important;}
.btn-primary{background:var(--primary);border-color:var(--primary);}
.btn-primary:hover{background:#004a7a;border-color:#004a7a;}
.glass-nav{background:rgba(255,255,255,.92);backdrop-filter:blur(8px);transition:all .3s;}
.glass-nav.shrink{padding-top:.4rem!important;padding-bottom:.4rem!important;box-shadow:0 6px 18px rgba(0,0,0,.08);}
#logo{height:84px;transition:height .25s;}
.glass-nav.shrink #logo{height:68px;}
.navbar-nav .nav-link{color:var(--dark)!important;font-weight:700;position:relative;margin:0 8px;}
.navbar-nav .nav-link::after{content:'';position:absolute;left:0;bottom:-6px;height:3px;width:0;background:var(--accent);transition:width .25s;border-radius:2px;}
.navbar-nav .nav-link:hover::after,.navbar-nav .nav-link.active::after{width:100%;}
.btn-enroll{background:var(--accent);color:#fff!important;border-radius:999px;padding:8px 20px;font-weight:700;}

/* Page header */
.page-header{background:linear-gradient(rgba(24,29,56,.72),rgba(24,29,56,.72)),url(img/carousel-1.jpg);background-position:center;background-size:cover;}

/* Event article */
.event-article{background:#fff;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden;margin-bottom:32px;}
.event-hero{width:100%;max-height:480px;object-fit:cover;display:block;}
.event-body{padding:36px 40px;}
.event-meta{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;align-items:center;}
.meta-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:999px;font-size:.84rem;font-weight:600;}
.meta-badge.date{background:#e8f4fd;color:var(--primary);}
.meta-badge.past{background:#f1f1f1;color:#777;}
.meta-badge.upcoming{background:#d4edda;color:#155724;}
.event-title{font-family:'Nunito',sans-serif;font-weight:800;font-size:2rem;color:var(--dark);line-height:1.25;margin-bottom:20px;}
.event-description{font-size:1rem;line-height:1.85;color:#444;white-space:pre-line;}

/* Navigation between events */
.event-nav{display:flex;justify-content:space-between;gap:16px;margin-top:32px;padding-top:24px;border-top:1px solid #e9ecef;}
.event-nav a{display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--primary);font-weight:600;font-size:.88rem;padding:10px 16px;border:2px solid #e0eaf5;border-radius:8px;transition:all .2s;max-width:48%;}
.event-nav a:hover{background:var(--primary);color:#fff;border-color:var(--primary);}
.event-nav .nav-title{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;}
.btn-sm-square{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;}
@media(max-width:768px){
    .event-body{padding:22px 18px;}
    .event-title{font-size:1.5rem;}
    .event-nav{flex-direction:column;}
    .event-nav a{max-width:100%;}
    .nav-title{max-width:220px;}
}
</style>
</head>
<body>

<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center" style="z-index:9999;">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>

<?php include 'navbar.php'; ?>

<!-- Page Header -->
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-4 text-white animated slideInDown fw-bold">
                    <?= $isPast ? 'Event Report' : 'Upcoming Event' ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a class="text-white" href="index.html">Home</a></li>
                        <li class="breadcrumb-item"><a class="text-white" href="events.php">News &amp; Events</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Event Detail</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Event Content -->
<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="event-article">
                <?php if ($imgPath): ?>
                <img src="<?= e($imgPath) ?>" alt="<?= e($ev['title']) ?>" class="event-hero">
                <?php endif; ?>

                <div class="event-body">
                    <!-- Meta -->
                    <div class="event-meta">
                        <span class="meta-badge date">
                            <i class="bi bi-calendar3"></i>
                            <?= $dateFormatted ?>
                        </span>
                        <?php if ($isPast): ?>
                        <span class="meta-badge past">
                            <i class="bi bi-archive"></i> Past Event
                        </span>
                        <?php else: ?>
                        <span class="meta-badge upcoming">
                            <i class="bi bi-alarm"></i> Upcoming
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h1 class="event-title"><?= e($ev['title']) ?></h1>

                    <!-- Full description — every paragraph, every line -->
                    <div class="event-description"><?= e($ev['description']) ?></div>

                    <!-- Share / action row -->
                    <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                        <a href="events.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Events
                        </a>
                        <a href="enroll.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square me-1"></i>Enroll Now
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-chat-dots me-1"></i>Contact Us
                        </a>
                    </div>

                    <!-- Prev / next navigation -->
                    <?php if ($prevEv || $nextEv): ?>
                    <div class="event-nav">
                        <?php if ($prevEv): ?>
                        <a href="event.php?id=<?= $prevEv['id'] ?>">
                            <i class="bi bi-chevron-left flex-shrink-0"></i>
                            <div>
                                <div style="font-size:.75rem;opacity:.65;">Previous</div>
                                <div class="nav-title"><?= e($prevEv['title']) ?></div>
                            </div>
                        </a>
                        <?php else: ?>
                        <div></div>
                        <?php endif; ?>

                        <?php if ($nextEv): ?>
                        <a href="event.php?id=<?= $nextEv['id'] ?>" style="margin-left:auto;text-align:right;">
                            <div>
                                <div style="font-size:.75rem;opacity:.65;">Next</div>
                                <div class="nav-title"><?= e($nextEv['title']) ?></div>
                            </div>
                            <i class="bi bi-chevron-right flex-shrink:0"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i></a>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="js/main.js"></script>
<script>
(function(){
    const nav=document.querySelector('.glass-nav');
    if(nav) window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});
})();
</script>
</body>
</html>
