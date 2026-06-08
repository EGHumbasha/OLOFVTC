<?php
ini_set('display_errors',0);
require "admin/db.php";
$today=date("Y-m-d");
$upcoming=$conn->query("SELECT * FROM events WHERE event_date >= '$today' ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);
$past=$conn->query("SELECT * FROM events WHERE event_date < '$today' ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function eventImg($e){ $f=basename($e['image']??''); if($f&&file_exists(__DIR__.'/admin/uploads/events/'.$f)) return 'admin/uploads/events/'.e($f); return 'img/placeholder.jpg'; }
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>News &amp; Events — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="icon" href="img/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="lib/animate/animate.min.css">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
:root{--primary:#005f99;--accent:#ff8c00;--dark:#222;}
body{background:#f8f9fa;font-family:'Heebo',sans-serif;}
.text-primary{color:var(--primary)!important;}
.btn-primary{background:var(--primary);border-color:var(--primary);}
.glass-nav{background:rgba(255,255,255,.92);backdrop-filter:blur(8px);transition:all .3s;}
.glass-nav.shrink{padding-top:.4rem!important;padding-bottom:.4rem!important;box-shadow:0 6px 18px rgba(0,0,0,.08);}
#logo{height:84px;transition:height .25s;}
.glass-nav.shrink #logo{height:68px;}
.navbar-nav .nav-link{color:var(--dark)!important;font-weight:700;position:relative;margin:0 8px;}
.navbar-nav .nav-link::after{content:'';position:absolute;left:0;bottom:-6px;height:3px;width:0;background:var(--accent);transition:width .25s;border-radius:2px;}
.navbar-nav .nav-link:hover::after,.navbar-nav .nav-link.active::after{width:100%;}
.navbar .nav-center{margin:0 auto;display:flex;gap:4px;align-items:center;}
.btn-enroll{background:var(--accent);color:#fff!important;border-radius:999px;padding:8px 20px;font-weight:700;}
.page-header{background:linear-gradient(rgba(24,29,56,.7),rgba(24,29,56,.7)),url(img/carousel-1.jpg);background-position:center;background-size:cover;}
.event-card{border-radius:10px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,.1);margin-bottom:28px;background:#fff;display:flex;flex-direction:column;transition:transform .3s;}
.event-card:hover{transform:translateY(-4px);}
.event-card img{width:100%;height:200px;object-fit:cover;}
.event-card .body{flex:1;display:flex;flex-direction:column;padding:18px;}
.event-title{font-size:1.2rem;font-weight:700;color:#333;}
.event-date{color:var(--primary);font-weight:700;margin-bottom:8px;}
.btn-sm-square{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}}
</style>
</head><body>
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>
<?php include 'navbar.php'; ?>
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5"><div class="row justify-content-center"><div class="col-lg-10 text-center">
        <h1 class="display-3 text-white animated slideInDown">News &amp; Events</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a class="text-white" href="index.html">Home</a></li>
            <li class="breadcrumb-item text-white active">Events</li>
        </ol></nav>
    </div></div></div>
</div>
<div class="container py-5">
    <div class="text-center mb-5">
        <h6 class="section-title text-primary text-uppercase">Stay Updated</h6>
        <h2>News &amp; Events</h2>
        <p class="text-muted">Browse upcoming and past events at Our Lady of Fatima VTC</p>
    </div>
    <div class="text-center mb-4">
        <button class="btn btn-primary me-2" onclick="showTab('upcoming',this)">Upcoming &amp; Current</button>
        <button class="btn btn-outline-secondary" onclick="showTab('past',this)">Past Events</button>
    </div>
    <div id="upcoming">
        <?php if(empty($upcoming)): ?><div class="alert alert-info text-center">No upcoming events at the moment. Check back soon!</div>
        <?php else: ?><div class="row"><?php foreach($upcoming as $ev): ?>
        <div class="col-md-4">
            <div class="event-card">
                <img src="<?=eventImg($ev)?>" alt="<?=e($ev['title'])?>">
                <div class="body">
                    <div class="event-title"><?=e($ev['title'])?></div>
                    <p class="event-date"><i class="bi bi-calendar me-2"></i><?=date("F d, Y",strtotime($ev['event_date']))?></p>
                    <p><?=nl2br(e(substr($ev['description'],0,140)))?>...</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
    <div id="past" style="display:none;">
        <?php if(empty($past)): ?><div class="alert alert-secondary text-center">No past events available.</div>
        <?php else: ?><div class="row"><?php foreach($past as $ev): ?>
        <div class="col-md-4">
            <div class="event-card" style="opacity:.85;">
                <img src="<?=eventImg($ev)?>" alt="<?=e($ev['title'])?>">
                <div class="body">
                    <div class="event-title"><?=e($ev['title'])?></div>
                    <p class="event-date"><i class="bi bi-calendar-check me-2"></i><?=date("F d, Y",strtotime($ev['event_date']))?></p>
                    <p><?=nl2br(e(substr($ev['description'],0,140)))?>...</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?></div><?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="js/main.js"></script>
<script>
function showTab(t,btn){
    document.getElementById('upcoming').style.display=t==='upcoming'?'block':'none';
    document.getElementById('past').style.display=t==='past'?'block':'none';
    document.querySelectorAll('.container .btn').forEach(b=>b.classList.replace('btn-primary','btn-outline-secondary'));
    btn.classList.replace('btn-outline-secondary','btn-primary');
}
(function(){const nav=document.querySelector('.glass-nav');if(nav)window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});})();
</script>
</body></html>
