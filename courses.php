<?php
/**
 * courses.php — safe sc() fallback so page works even without site_settings table
 */
if (!function_exists('sc')) {
    function sc(string $key, string $default = ''): string {
        static $settings = null;
        if ($settings === null) {
            $settings = [];
            try {
                if (file_exists(__DIR__ . '/admin/config.php')) {
                    require_once __DIR__ . '/admin/config.php';
                }
                if (!isset($GLOBALS['conn']) && defined('DB_HOST')) {
                    $GLOBALS['conn'] = new PDO(
                        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                        DB_USER, DB_PASS,
                        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
                    );
                }
                if (isset($GLOBALS['conn'])) {
                    $rows = $GLOBALS['conn']->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
                    $settings = $rows ?: [];
                }
            } catch (Exception $e) {
                $settings = []; // table missing or DB error — use defaults
            }
        }
        return $settings[$key] ?? $default;
    }
}
$courses = [
    ['slug'=>'hairdressing','id'=>'hairdressing','icon'=>'fa-cut','color'=>'#e91e8c',
     'title'=>sc('course_hairdressing_title','Hairdressing'),
     'desc' =>sc('course_hairdressing_desc', 'Learn modern professional hair styling, cutting, colouring and treatment techniques used in top salons.'),
     'img'  =>sc('course_hairdressing_img',  'img/course-1.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Professional salon styling','Hair colouring & treatments','Client consultation','Business & hygiene practices']],
    ['slug'=>'bakerystudies','id'=>'bakerystudies','icon'=>'fa-bread-slice','color'=>'#ff8c00',
     'title'=>sc('course_bakerystudies_title','Bakery Studies'),
     'desc' =>sc('course_bakerystudies_desc', 'Master artisan and commercial baking — bread, pastries, cakes, and confectionery.'),
     'img'  =>sc('course_bakerystudies_img',  'img/course-2.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Intermediate',
     'outcomes'=>['Bread & pastry production','Cake decoration','Food safety & hygiene','Costing & small business skills']],
    ['slug'=>'catering','id'=>'catering','icon'=>'fa-utensils','color'=>'#e53935',
     'title'=>sc('course_catering_title','Catering'),
     'desc' =>sc('course_catering_desc', 'Professional catering skills for events, hotels, restaurants, and private functions.'),
     'img'  =>sc('course_catering_img',  'img/course-3.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Menu planning & costing','Food preparation & presentation','Event catering logistics','Customer service']],
    ['slug'=>'cuttingdesigning','id'=>'cuttingdesigning','icon'=>'fa-scissors','color'=>'#8e24aa',
     'title'=>sc('course_cuttingdesigning_title','Cutting & Designing'),
     'desc' =>sc('course_cuttingdesigning_desc', 'Learn pattern making, fabric cutting, sewing, and fashion design to build a career in clothing.'),
     'img'  =>sc('course_cuttingdesigning_img',  'img/course-4.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Pattern drafting & cutting','Garment construction','Fashion design principles','Alterations & repairs']],
    ['slug'=>'interiorhome_decor','id'=>'interiorhomedecor','icon'=>'fa-couch','color'=>'#00897b',
     'title'=>sc('course_interiorhome_decor_title','Interior Home Decor'),
     'desc' =>sc('course_interiorhome_decor_desc', 'Transform living and working spaces with professional interior styling and space planning.'),
     'img'  =>sc('course_interiorhome_decor_img',  'img/course-5.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Intermediate',
     'outcomes'=>['Space planning & layout','Colour & texture selection','Window treatments','Client presentations']],
    ['slug'=>'beautytherapy','id'=>'beautytherapy','icon'=>'fa-spa','color'=>'#d81b60',
     'title'=>sc('course_beautytherapy_title','Beauty Therapy'),
     'desc' =>sc('course_beautytherapy_desc', 'Comprehensive beauty training: skincare, facials, nails, make-up artistry, and massage.'),
     'img'  =>sc('course_beautytherapy_img',  'img/course-6.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Facial & skin treatments','Nail care & extensions','Professional make-up','Aromatherapy & massage']],
    ['slug'=>'computers','id'=>'computers','icon'=>'fa-laptop-code','color'=>'#1565c0',
     'title'=>sc('course_computers_title','Computers & ICT'),
     'desc' =>sc('course_computers_desc', 'Build digital skills — MS Office, internet, data entry, and intro to programming.'),
     'img'  =>sc('course_computers_img',  'img/course-7.jpg'),
     'duration'=>'6 months','level'=>'Beginner – Intermediate',
     'outcomes'=>['Microsoft Office Suite','Internet & email','Data entry & spreadsheets','Intro to programming']],
    ['slug'=>'weldingsheet_metalwork','id'=>'welding','icon'=>'fa-fire','color'=>'#bf360c',
     'title'=>sc('course_weldingsheet_metalwork_title','Welding & Sheet Metalwork'),
     'desc' =>sc('course_weldingsheet_metalwork_desc', 'Hands-on industrial skills in arc welding, gas welding, and metal fabrication.'),
     'img'  =>sc('course_weldingsheet_metalwork_img',  'img/course-8.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Arc & MIG welding','Sheet metal cutting & bending','Fabrication projects','Safety practices']],
    ['slug'=>'woodwork','id'=>'woodwork','icon'=>'fa-hammer','color'=>'#6d4c41',
     'title'=>sc('course_woodwork_title','Woodwork & Carpentry'),
     'desc' =>sc('course_woodwork_desc', 'Expert carpentry skills — furniture making, joinery, cabinet construction, and finishing.'),
     'img'  =>sc('course_woodwork_img',  'img/course-9.jpg'),
     'duration'=>'12 months','level'=>'Beginner – Advanced',
     'outcomes'=>['Furniture design & construction','Joinery & cabinet making','Wood finishing & polishing','Estimation & costing']],
    ['slug'=>'eventsdecor','id'=>'eventsdecor','icon'=>'fa-star','color'=>'#f9a825',
     'title'=>sc('course_eventsdecor_title','Events Decor'),
     'desc' =>sc('course_eventsdecor_desc', 'Create stunning environments for weddings, corporate functions, and parties.'),
     'img'  =>sc('course_eventsdecor_img',  'img/course-10.jpg'),
     'duration'=>'6 months','level'=>'Beginner – Intermediate',
     'outcomes'=>['Event concept & design','Floral & centrepiece arrangements','Venue styling & setup','Client management']],
];
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
// Kill spinner immediately — no jQuery dependency
(function(){
    function hideSpinner(){
        var s=document.getElementById('spinner');
        if(s){s.style.display='none';s.classList.remove('show');}
    }
    // Fire instantly if DOM is ready
    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',hideSpinner);
    } else {
        hideSpinner();
    }
    // Hard fallback — kills it after 3 seconds no matter what
    setTimeout(hideSpinner, 3000);
    // Also on full load
    window.addEventListener('load', hideSpinner);
})();
</script>
<meta charset="utf-8">
<title>Our Courses — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="Vocational courses in hairdressing, bakery, catering, beauty therapy, computers, welding, woodwork, and events decor.">
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
:root{--primary:#005f99;--accent:#ff8c00;--dark:#1a1a2e;--text:#444;--light-bg:#f8fafd;}
body{font-family:"Heebo",sans-serif;color:var(--text);}
.glass-nav{background:rgba(255,255,255,.93);backdrop-filter:blur(10px);transition:all .3s;}
.glass-nav.shrink{padding-top:.35rem!important;padding-bottom:.35rem!important;box-shadow:0 6px 22px rgba(0,0,0,.09);}
#logo{height:84px;transition:height .25s;}
.glass-nav.shrink #logo{height:66px;}
.navbar-nav .nav-link{color:var(--dark)!important;font-weight:700;position:relative;margin:0 8px;font-size:.95rem;}
.navbar-nav .nav-link::after{content:"";position:absolute;left:0;bottom:-5px;height:3px;width:0;background:var(--accent);transition:width .25s;border-radius:2px;}
.navbar-nav .nav-link:hover::after,.navbar-nav .nav-link.active::after{width:100%;}
.btn-enroll{background:var(--accent);color:#fff!important;border-radius:999px;padding:8px 22px;font-weight:700;transition:all .2s;}
.btn-enroll:hover{background:#cc7000;transform:translateY(-2px);}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}.btn-enroll{display:inline-block!important;margin:10px 12px;}}
.page-header{background:linear-gradient(135deg,rgba(0,20,50,.82) 0%,rgba(0,95,153,.75) 100%),url(img/carousel-1.jpg) center/cover no-repeat;padding:100px 0 80px;}
.stats-bar{background:var(--primary);padding:24px 0;}
.stat-item{text-align:center;color:#fff;}
.stat-item .num{font-size:2rem;font-weight:800;font-family:"Nunito",sans-serif;line-height:1;}
.stat-item .lbl{font-size:.82rem;opacity:.8;text-transform:uppercase;letter-spacing:.5px;margin-top:2px;}
.filter-wrap{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:40px;}
.f-btn{border-radius:999px;padding:8px 20px;font-size:.88rem;font-weight:600;border:2px solid #dee2e6;color:#555;background:#fff;cursor:pointer;transition:all .2s;white-space:nowrap;}
.f-btn:hover{border-color:var(--primary);color:var(--primary);}
.f-btn.active{background:var(--primary);border-color:var(--primary);color:#fff;}
.course-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.07);transition:transform .3s,box-shadow .3s;height:100%;display:flex;flex-direction:column;}
.course-card:hover{transform:translateY(-8px);box-shadow:0 12px 36px rgba(0,0,0,.13);}
.course-card .img-wrap{position:relative;overflow:hidden;height:210px;}
.course-card .img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .45s;}
.course-card:hover .img-wrap img{transform:scale(1.08);}
.color-bar{position:absolute;top:0;left:0;right:0;height:4px;}
.icon-badge{position:absolute;bottom:-22px;left:20px;width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;box-shadow:0 4px 12px rgba(0,0,0,.2);border:3px solid #fff;}
.img-overlay{position:absolute;inset:0;background:rgba(0,0,0,0);transition:background .35s;display:flex;align-items:center;justify-content:center;}
.course-card:hover .img-overlay{background:rgba(0,0,0,.38);}
.overlay-btn{opacity:0;transform:translateY(10px);transition:all .3s;background:#fff;color:var(--primary);border-radius:999px;padding:9px 22px;font-weight:700;font-size:.88rem;text-decoration:none;white-space:nowrap;}
.course-card:hover .overlay-btn{opacity:1;transform:translateY(0);}
.card-body-inner{padding:32px 22px 22px;flex:1;display:flex;flex-direction:column;}
.course-title{font-size:1.1rem;font-weight:800;color:var(--dark);margin-bottom:8px;font-family:"Nunito",sans-serif;}
.course-desc{font-size:.88rem;color:#6b7280;line-height:1.65;flex:1;margin-bottom:14px;}
.course-meta{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;}
.meta-badge{background:#f0f4f8;color:#555;border-radius:999px;padding:4px 12px;font-size:.75rem;font-weight:600;display:flex;align-items:center;gap:5px;}
.outcomes{list-style:none;padding:0;margin:0 0 18px;}
.outcomes li{font-size:.82rem;color:#555;padding:3px 0;display:flex;align-items:flex-start;gap:7px;}
.outcomes li::before{content:"\2713";color:var(--primary);font-weight:800;flex-shrink:0;margin-top:1px;}
.btn-row{display:flex;gap:8px;margin-top:auto;}
.btn-enroll-card{flex:1;background:var(--primary);color:#fff;border:none;border-radius:8px;padding:10px;font-weight:700;font-size:.88rem;text-align:center;text-decoration:none;transition:background .2s;}
.btn-enroll-card:hover{background:#004a7a;color:#fff;}
.btn-gallery-card{background:#f0f4f8;color:var(--primary);border:none;border-radius:8px;padding:10px 14px;font-weight:700;font-size:.88rem;text-decoration:none;transition:background .2s;white-space:nowrap;}
.btn-gallery-card:hover{background:#e0eaf5;}
.why-card{background:#fff;border-radius:14px;padding:28px 24px;box-shadow:0 3px 16px rgba(0,0,0,.06);text-align:center;transition:transform .25s;height:100%;}
.why-card:hover{transform:translateY(-5px);}
.why-icon{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:#fff;}
.why-card h6{font-family:"Nunito",sans-serif;font-weight:800;color:var(--dark);margin-bottom:8px;}
.why-card p{font-size:.88rem;color:#6b7280;margin:0;}
.cta-banner{background:linear-gradient(135deg,#005f99 0%,#003d66 100%);border-radius:20px;padding:56px 40px;text-align:center;color:#fff;position:relative;overflow:hidden;}
.cta-banner::before{content:"";position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:rgba(255,255,255,.06);border-radius:50%;}
.cta-banner::after{content:"";position:absolute;bottom:-60px;left:-60px;width:220px;height:220px;background:rgba(255,255,255,.04);border-radius:50%;}
.cta-banner h2{font-family:"Nunito",sans-serif;font-weight:800;font-size:2rem;margin-bottom:12px;}
.cta-banner p{opacity:.85;margin-bottom:28px;max-width:520px;margin-left:auto;margin-right:auto;}
.btn-cta-white{background:#fff;color:var(--primary);border-radius:999px;padding:14px 36px;font-weight:800;text-decoration:none;font-size:1rem;transition:all .2s;display:inline-block;}
.btn-cta-white:hover{background:#f0f4f8;transform:translateY(-2px);}
.btn-cta-outline{border:2px solid rgba(255,255,255,.5);color:#fff;border-radius:999px;padding:13px 36px;font-weight:700;text-decoration:none;font-size:1rem;transition:all .2s;display:inline-block;}
.btn-cta-outline:hover{background:rgba(255,255,255,.1);color:#fff;}
.section-header{text-align:center;margin-bottom:48px;}
.section-header .sub{text-transform:uppercase;letter-spacing:2px;font-size:.8rem;font-weight:700;color:var(--accent);margin-bottom:10px;}
.section-header h2{font-family:"Nunito",sans-serif;font-weight:800;font-size:2rem;color:var(--dark);margin-bottom:12px;}
.section-header .lead{color:#6b7280;max-width:600px;margin:0 auto;font-size:.95rem;}
.course-count-badge{background:var(--accent);color:#fff;border-radius:999px;padding:3px 12px;font-size:.8rem;font-weight:700;margin-left:10px;}
.course-col{transition:opacity .35s,transform .35s;}
.course-col.hidden{opacity:0;transform:scale(.96) translateY(10px);pointer-events:none;position:absolute;visibility:hidden;}
.back-to-top{position:fixed;bottom:24px;right:24px;z-index:999;}
.btn-sm-square{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;}
@media(max-width:576px){.cta-banner{padding:36px 20px;}.cta-banner h2{font-size:1.5rem;}}
</style>
</head>
<body>

<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center" style="z-index:9999;">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>

<?php include "navbar.php"; ?>

<div class="page-header">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <p class="text-white text-uppercase fw-bold mb-2" style="letter-spacing:3px;font-size:.82rem;opacity:.8;">What We Offer</p>
                <h1 class="display-4 fw-bold text-white mb-3 animated slideInDown">Vocational Courses</h1>
                <p class="text-white mb-4" style="opacity:.85;font-size:1.05rem;max-width:580px;margin:0 auto 24px;">Practical, industry-recognised training that turns your passion into a profession.</p>
                <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                    <li class="breadcrumb-item text-white active">Courses</li>
                </ol></nav>
            </div>
        </div>
    </div>
</div>

<div class="stats-bar">
    <div class="container"><div class="row g-3 justify-content-center">
        <div class="col-6 col-md-3"><div class="stat-item"><div class="num">10+</div><div class="lbl">Vocational Courses</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-item"><div class="num">500+</div><div class="lbl">Graduates</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-item"><div class="num">25+</div><div class="lbl">Years of Training</div></div></div>
        <div class="col-6 col-md-3"><div class="stat-item"><div class="num">100%</div><div class="lbl">Practical Focus</div></div></div>
    </div></div>
</div>

<section class="py-5" style="background:var(--light-bg);">
<div class="container py-3">
    <div class="section-header">
        <div class="sub">Our Programmes</div>
        <h2>Choose Your Course <span class="course-count-badge"><?php echo count($courses); ?> Available</span></h2>
        <p class="lead">All courses include hands-on practical training, experienced instructors, and a nationally recognised certificate.</p>
    </div>

    <div class="filter-wrap" id="filterWrap">
        <button class="f-btn active" data-filter="all">All Courses</button>
        <?php foreach($courses as $c): ?>
        <button class="f-btn" data-filter="<?php echo e($c["slug"]); ?>"><?php echo e($c["title"]); ?></button>
        <?php endforeach; ?>
    </div>

    <div class="row g-4" id="courseGrid">
    <?php foreach($courses as $c):
        $img = $c["img"];
        if(strpos($img,"/")===false) $img = "img/".$img;
    ?>
    <div class="col-lg-4 col-md-6 course-col" data-cat="<?php echo e($c["slug"]); ?>" id="<?php echo e($c["id"]); ?>">
        <div class="course-card">
            <div class="img-wrap">
                <div class="color-bar" style="background:<?php echo e($c["color"]); ?>;"></div>
                <img src="<?php echo e($img); ?>" alt="<?php echo e($c["title"]); ?>" loading="lazy" onerror="this.src='img/placeholder.jpg'">
                <div class="img-overlay">
                    <a href="gallery.php?cat=<?php echo e($c["slug"]); ?>" class="overlay-btn">
                        <i class="bi bi-images me-2"></i>View Gallery
                    </a>
                </div>
                <div class="icon-badge" style="background:<?php echo e($c["color"]); ?>;">
                    <i class="fas <?php echo e($c["icon"]); ?>"></i>
                </div>
            </div>
            <div class="card-body-inner">
                <h5 class="course-title"><?php echo e($c["title"]); ?></h5>
                <p class="course-desc"><?php echo e($c["desc"]); ?></p>
                <div class="course-meta">
                    <span class="meta-badge"><i class="bi bi-clock"></i> <?php echo e($c["duration"]); ?></span>
                    <span class="meta-badge"><i class="bi bi-bar-chart"></i> <?php echo e($c["level"]); ?></span>
                    <span class="meta-badge"><i class="bi bi-patch-check"></i> Certificate</span>
                </div>
                <ul class="outcomes">
                    <?php foreach($c["outcomes"] as $o): ?>
                    <li><?php echo e($o); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="btn-row">
                    <a href="enroll.php" class="btn-enroll-card"><i class="bi bi-pencil-square me-1"></i>Enroll Now</a>
                    <a href="contact.php" class="btn-gallery-card"><i class="bi bi-chat-dots me-1"></i>Enquire</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <div id="noResults" class="text-center py-5" style="display:none;">
        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">No matching courses found.</h5>
        <button class="f-btn active mt-2" onclick="setFilter('all')">Show All</button>
    </div>
</div>
</section>

<section class="py-5 bg-white">
<div class="container py-3">
    <div class="section-header">
        <div class="sub">Why Train With Us</div>
        <h2>The Fatima Advantage</h2>
        <p class="lead">We go beyond classroom theory to prepare you for real-world success.</p>
    </div>
    <div class="row g-4">
        <div class="col-sm-6 col-lg-3"><div class="why-card">
            <div class="why-icon" style="background:#005f99;"><i class="fas fa-chalkboard-teacher"></i></div>
            <h6>Expert Instructors</h6><p>Industry-experienced tutors who bring real-world knowledge into every lesson.</p>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="why-card">
            <div class="why-icon" style="background:#ff8c00;"><i class="fas fa-tools"></i></div>
            <h6>Hands-On Training</h6><p>Learn by doing — our workshops and studios are fully equipped for practical work.</p>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="why-card">
            <div class="why-icon" style="background:#28a745;"><i class="fas fa-certificate"></i></div>
            <h6>Recognised Certificate</h6><p>Graduate with a nationally recognised qualification that opens doors to employment.</p>
        </div></div>
        <div class="col-sm-6 col-lg-3"><div class="why-card">
            <div class="why-icon" style="background:#8e24aa;"><i class="fas fa-briefcase"></i></div>
            <h6>Career Support</h6><p>Job placement assistance, entrepreneurship guidance, and alumni networking.</p>
        </div></div>
    </div>
</div>
</section>

<section class="py-5" style="background:var(--light-bg);">
<div class="container">
    <div class="cta-banner">
        <h2>Ready to Start Your Journey?</h2>
        <p>Applications are open. Take the first step toward a skilled, fulfilling career — apply today and our admissions team will be in touch within 2–3 business days.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="enroll.php" class="btn-cta-white"><i class="bi bi-pencil-square me-2"></i>Apply Now</a>
            <a href="contact.php" class="btn-cta-outline"><i class="bi bi-telephone me-2"></i>Contact Us</a>
        </div>
    </div>
</div>
</section>

<?php include "footer.php"; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="js/main.js"></script>
<script>
function setFilter(filter){
    document.querySelectorAll(".f-btn").forEach(b=>b.classList.toggle("active",b.dataset.filter===filter));
    let shown=0;
    document.querySelectorAll(".course-col").forEach(col=>{
        const match=filter==="all"||col.dataset.cat===filter;
        col.classList.toggle("hidden",!match);
        if(match) shown++;
    });
    document.getElementById("noResults").style.display=shown===0?"block":"none";
}
document.querySelectorAll(".f-btn").forEach(btn=>btn.addEventListener("click",()=>setFilter(btn.dataset.filter)));

function scrollToCourse(hash){
    if(!hash) return;
    const id=hash.replace("#","");
    const el=document.getElementById(id);
    if(!el) return;
    const col=el.closest(".course-col");
    if(col) setFilter(col.dataset.cat);
    setTimeout(()=>{const top=el.getBoundingClientRect().top+window.scrollY-100;window.scrollTo({top,behavior:"smooth"});},80);
}
window.addEventListener("DOMContentLoaded",()=>{if(window.location.hash) scrollToCourse(window.location.hash);});
window.addEventListener("hashchange",()=>scrollToCourse(window.location.hash));
</script>
</body>
</html>
