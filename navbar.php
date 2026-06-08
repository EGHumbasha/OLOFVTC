<?php // navbar.php ?>
<nav id="mainNavbar" class="navbar navbar-expand-lg glass-nav sticky-top py-3">
    <div class="container-fluid px-4 px-lg-5 d-flex align-items-center">
        <a href="index.html" class="navbar-brand d-flex align-items-center me-3">
            <img src="img/logo.png" alt="Our Lady of Fatima Logo" id="logo">
        </a>
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-label="Toggle navigation">
            <i class="fas fa-bars" style="font-size:24px;color:#222;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav nav-center fw-bold mx-auto align-items-lg-center">
                <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="about.html" class="nav-link">About Us</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">What We Offer</a>
                    <ul class="dropdown-menu rounded-3 border-0 shadow-sm">
                        <li><a href="courses.html" class="dropdown-item">All Courses</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="courses.html#hairdressing" class="dropdown-item">Hairdressing</a></li>
                        <li><a href="courses.html#bakerystudies" class="dropdown-item">Bakery Studies</a></li>
                        <li><a href="courses.html#catering" class="dropdown-item">Catering</a></li>
                        <li><a href="courses.html#cuttingdesigning" class="dropdown-item">Cutting &amp; Designing</a></li>
                        <li><a href="courses.html#interiorhomedecor" class="dropdown-item">Interior Home Decor</a></li>
                        <li><a href="courses.html#beautytherapy" class="dropdown-item">Beauty Therapy</a></li>
                        <li><a href="courses.html#computers" class="dropdown-item">Computers</a></li>
                        <li><a href="courses.html#welding" class="dropdown-item">Welding &amp; Sheet Metalwork</a></li>
                        <li><a href="courses.html#woodwork" class="dropdown-item">Woodwork</a></li>
                        <li><a href="courses.html#eventsdecor" class="dropdown-item">Events Decor</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="events.php" class="nav-link">News &amp; Updates</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact Us</a></li>
                <li class="nav-item"><a href="portal.php" class="nav-link">Student Portal</a></li>
            </ul>
            <div class="d-flex align-items-center ms-3">
                <a href="enroll.php" class="btn btn-enroll d-none d-lg-inline">Enroll Now</a>
            </div>
        </div>
    </div>
</nav>
<style>
.glass-nav{background:rgba(255,255,255,.92);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);transition:all .3s;}
.glass-nav.shrink{padding-top:.4rem!important;padding-bottom:.4rem!important;box-shadow:0 6px 18px rgba(0,0,0,.08);}
#logo{height:84px;transition:height .25s;}
.glass-nav.shrink #logo{height:68px;}
.navbar-nav .nav-link{color:#222!important;font-weight:700;position:relative;margin:0 8px;}
.navbar-nav .nav-link::after{content:'';position:absolute;left:0;bottom:-6px;height:3px;width:0;background:#ff8c00;transition:width .25s;border-radius:2px;}
.navbar-nav .nav-link:hover::after,.navbar-nav .nav-link.active::after{width:100%;}
.navbar .nav-center{margin:0 auto;display:flex;gap:4px;align-items:center;}
.btn-enroll{background:#ff8c00;color:#fff!important;border-radius:999px;padding:8px 20px;font-weight:700;transition:background .25s,transform .2s;}
.btn-enroll:hover{background:#cc7000;transform:translateY(-2px);}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}.btn-enroll{display:inline-block!important;margin:10px 12px 6px;}}
</style>
<script>
(function(){
    const nav=document.querySelector('.glass-nav');
    if(nav) window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});
    const cur=window.location.pathname.split('/').pop()||'index.html';
    document.querySelectorAll('#mainNavbar .nav-link:not(.dropdown-toggle)').forEach(l=>{
        const h=l.getAttribute('href')||'';
        if(h&&(h===cur||h.replace(/\.php|\.html/,'')===cur.replace(/\.php|\.html/,''))) l.classList.add('active');
    });
})();
</script>
