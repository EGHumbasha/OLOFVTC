<?php
$categories=['hairdressing','bakerystudies','catering','cuttingdesigning','interiorhome_decor','beautytherapy','computers','weldingsheet_metalwork','woodwork','eventsdecor','graduation'];
$catLabels=['hairdressing'=>'Hairdressing','bakerystudies'=>'Bakery Studies','catering'=>'Catering','cuttingdesigning'=>'Cutting & Designing','interiorhome_decor'=>'Interior Home Decor','beautytherapy'=>'Beauty Therapy','computers'=>'Computers','weldingsheet_metalwork'=>'Welding & Sheet Metalwork','woodwork'=>'Woodwork','eventsdecor'=>'Events Decor','graduation'=>'Graduation'];
$uploadDir='admin/uploads/gallery/';
$categoryImages=array_fill_keys($categories,[]);
$categoryNewest=array_fill_keys($categories,0);
$allFiles=glob($uploadDir.'*.{jpg,jpeg,png,gif,webp}',GLOB_BRACE)?:[];
function normCat($s){return strtolower(str_replace('_','',$s));}
foreach($allFiles as $file){
    $prefix=explode('_',basename($file),2)[0];
    foreach($categories as $cat){
        if(normCat($prefix)===normCat($cat)){
            $categoryImages[$cat][]=$file;
            $t=filemtime($file); if($t>$categoryNewest[$cat]) $categoryNewest[$cat]=$t;
        }
    }
}
$categories=array_filter($categories,fn($c)=>!empty($categoryImages[$c]));
if(!empty($categoryNewest)){arsort($categoryNewest);$categories=array_intersect_key(array_flip(array_keys($categoryNewest)),array_flip($categories));$categories=array_keys($categories);}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Gallery — Our Lady of Fatima VTC</title>
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
body{font-family:'Heebo',sans-serif;color:var(--dark);}
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
.gallery-grid{columns:3 250px;gap:12px;}
.gallery-item{break-inside:avoid;position:relative;overflow:hidden;cursor:pointer;border-radius:8px;margin-bottom:12px;}
.gallery-item img{width:100%;display:block;transition:transform .3s;}
.gallery-item:hover img{transform:scale(1.05);}
.gallery-overlay{position:absolute;bottom:0;left:0;width:100%;background:rgba(0,0,0,.55);color:#fff;padding:10px;opacity:0;transition:opacity .3s;}
.gallery-item:hover .gallery-overlay{opacity:1;}
.filter-btns{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:28px;}
.filter-btns .btn{border-radius:999px;padding:6px 18px;font-size:.88rem;}
.btn-sm-square{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}.gallery-grid{columns:2 200px;}}
@media(max-width:576px){.gallery-grid{columns:1;}}
</style>
</head><body>
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>
<?php include 'navbar.php'; ?>
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5"><div class="row justify-content-center"><div class="col-lg-10 text-center">
        <h1 class="display-3 text-white animated slideInDown">Photo Gallery</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a class="text-white" href="index.html">Home</a></li>
            <li class="breadcrumb-item text-white active">Gallery</li>
        </ol></nav>
    </div></div></div>
</div>
<div class="container py-5">
    <div class="text-center mb-5">
        <h6 class="section-title text-primary text-uppercase">Our Work</h6>
        <h2>Photo Gallery</h2>
    </div>
    <div class="filter-btns">
        <button class="btn btn-primary active" data-filter="all">All</button>
        <?php foreach($categories as $cat): ?>
        <button class="btn btn-outline-primary" data-filter="<?=htmlspecialchars($cat)?>"><?=htmlspecialchars($catLabels[$cat]??$cat)?></button>
        <?php endforeach; ?>
    </div>
    <?php if(empty($categories)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-images fs-1 d-block mb-3"></i><p>No gallery images uploaded yet. Images will appear here once uploaded via the admin panel.</p></div>
    <?php else: ?>
    <div class="gallery-grid" id="galleryGrid">
        <?php foreach($categories as $cat): foreach($categoryImages[$cat] as $file): $label=$catLabels[$cat]??$cat; ?>
        <div class="gallery-item" data-cat="<?=htmlspecialchars($cat)?>">
            <img src="<?=htmlspecialchars($file)?>" alt="<?=htmlspecialchars($label)?>" loading="lazy" onclick="openModal('<?=htmlspecialchars($file,ENT_QUOTES)?>','<?=htmlspecialchars($label,ENT_QUOTES)?>')">
            <div class="gallery-overlay"><h6 class="mb-0"><?=htmlspecialchars($label)?></h6></div>
        </div>
        <?php endforeach; endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<!-- Lightbox Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-body p-0 position-relative text-center">
                <button class="btn btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index:10;"></button>
                <img id="galleryModalImg" class="img-fluid" src="" alt="" style="max-height:90vh;width:auto;margin:auto;">
                <p id="galleryModalLabel" class="text-white text-center py-2 mb-0"></p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="js/main.js"></script>
<script>
document.querySelectorAll('.filter-btns .btn').forEach(btn=>{
    btn.addEventListener('click',function(){
        document.querySelectorAll('.filter-btns .btn').forEach(b=>{b.classList.remove('btn-primary');b.classList.add('btn-outline-primary');});
        this.classList.remove('btn-outline-primary');this.classList.add('btn-primary');
        const f=this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item=>{item.style.display=(f==='all'||item.dataset.cat===f)?'block':'none';});
    });
});
function openModal(src,label){
    document.getElementById('galleryModalImg').src=src;
    document.getElementById('galleryModalLabel').textContent=label;
    new bootstrap.Modal(document.getElementById('galleryModal')).show();
}
(function(){const nav=document.querySelector('.glass-nav');if(nav)window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});})();
</script>
</body></html>
