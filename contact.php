<?php
require_once __DIR__.'/admin/config.php';
require_once __DIR__.'/admin/db.php';
require_once __DIR__.'/admin/send_mail.php';
$errors=[];$success=false;$fields=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['website']??'')){$success=true;}
    else {
        $fields=['name'=>trim($_POST['name']??''),'email'=>trim($_POST['email']??''),'subject'=>trim($_POST['subject']??''),'message'=>trim($_POST['message']??'')];
        if(empty($fields['name'])) $errors[]="Your name is required.";
        if(empty($fields['email'])) $errors[]="Your email is required.";
        elseif(!filter_var($fields['email'],FILTER_VALIDATE_EMAIL)) $errors[]="Please enter a valid email address.";
        if(empty($fields['subject'])) $errors[]="Subject is required.";
        if(empty($fields['message'])||strlen($fields['message'])<10) $errors[]="Please enter a message (at least 10 characters).";
        if(empty($errors)){
            try { $conn->prepare("INSERT INTO contact_messages (name,email,phone,subject,message,submitted_at) VALUES(?,?,?,?,?,NOW())")->execute([$fields['name'],$fields['email'],$fields['phone']??null,$fields['subject'],$fields['message']]); } catch(Exception $e){ error_log($e->getMessage()); }
            $n=htmlspecialchars($fields['name']); $s=htmlspecialchars($fields['subject']); $m=nl2br(htmlspecialchars($fields['message']));
            sendEmail(SCHOOL_EMAIL,"Website Contact: {$s}","<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'><h2 style='color:#005f99;'>New Contact Message</h2><table style='width:100%;border-collapse:collapse;'><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;width:25%;'>From</td><td style='padding:8px;border:1px solid #ddd;'>{$n}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Email</td><td style='padding:8px;border:1px solid #ddd;background:#f8f8f8;'>{$fields['email']}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;'>Subject</td><td style='padding:8px;border:1px solid #ddd;'>{$s}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Message</td><td style='padding:8px;border:1px solid #ddd;background:#f8f8f8;'>{$m}</td></tr></table></div>",$fields['email']);
            sendEmail($fields['email'],"We received your message — ".SCHOOL_NAME,"<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'><div style='background:#005f99;padding:20px 28px;border-radius:8px 8px 0 0;'><h2 style='color:#fff;margin:0;'>Message Received</h2></div><div style='padding:20px 28px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;'><p>Dear {$n},</p><p>Thank you for contacting <strong>".SCHOOL_NAME."</strong>. We will respond within 1–2 business days.</p><p><strong>Your message:</strong><br>{$m}</p><hr><p>📞 ".SCHOOL_PHONE."<br>📧 ".SCHOOL_EMAIL."<br>📍 ".SCHOOL_ADDRESS."</p></div></div>");
            $success=true;
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Contact Us — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
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
.btn-enroll:hover{background:#cc7000;transform:translateY(-2px);}
.info-card{background:var(--primary);color:#fff;border-radius:10px;padding:28px;}
.info-card a{color:#ffffffcc;}
.page-header{background:linear-gradient(rgba(24,29,56,.7),rgba(24,29,56,.7)),url(img/carousel-1.jpg);background-position:center;background-size:cover;}
.btn-sm-square{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;}
.hp-field{display:none!important;}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}}
</style>
</head><body>
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>
<?php include 'navbar.php'; ?>
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5"><div class="row justify-content-center"><div class="col-lg-10 text-center">
        <h1 class="display-3 text-white animated slideInDown">Contact Us</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a class="text-white" href="index.html">Home</a></li>
            <li class="breadcrumb-item text-white active">Contact</li>
        </ol></nav>
    </div></div></div>
</div>
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
            <div class="info-card h-100">
                <h4 class="text-white mb-4">Get In Touch</h4>
                <p><i class="fa fa-map-marker-alt me-3"></i><?=SCHOOL_ADDRESS?></p>
                <p><i class="fa fa-phone-alt me-3"></i><?=SCHOOL_PHONE?></p>
                <p><i class="fa fa-envelope me-3"></i><a href="mailto:<?=SCHOOL_EMAIL?>"><?=SCHOOL_EMAIL?></a></p>
                <hr style="border-color:rgba(255,255,255,.3)">
                <p class="mb-1"><strong>Office Hours</strong></p>
                <p class="mb-0">Mon–Fri: 8:00 AM – 5:00 PM<br>Saturday: 8:00 AM – 12:00 PM</p>
                <div class="d-flex pt-3 gap-2">
                    <a href="#" class="btn btn-sm-square" style="background:rgba(255,255,255,.2);color:#fff;"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com/people/Our-Lady-Of-Fatima-VTC/61583791887969/" target="_blank" class="btn btn-sm-square" style="background:rgba(255,255,255,.2);color:#fff;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-sm-square" style="background:rgba(255,255,255,.2);color:#fff;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.2s">
            <h4 class="mb-4">Send Us a Message</h4>
            <?php if($success): ?>
            <div class="alert alert-success p-4 text-center">
                <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success"></i>
                <h5>Message Sent!</h5>
                <p class="mb-0">Thank you for reaching out. We will get back to you within 1–2 business days. A copy has been sent to your email.</p>
                <a href="contact.php" class="btn btn-primary mt-3">Send Another</a>
            </div>
            <?php else: ?>
            <?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif;?>
            <form method="POST" action="contact.php" novalidate>
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-6"><div class="form-floating"><input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required value="<?=htmlspecialchars($fields['name']??'')?>"><label for="name">Your Name</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="email" name="email" class="form-control" id="email" placeholder="Your Email" required value="<?=htmlspecialchars($fields['email']??'')?>"><label for="email">Your Email</label></div></div>
                    <div class="col-12"><div class="form-floating"><input type="text" name="subject" class="form-control" id="subject" placeholder="Subject" required value="<?=htmlspecialchars($fields['subject']??'')?>"><label for="subject">Subject</label></div></div>
                    <div class="col-12"><div class="form-floating"><textarea class="form-control" name="message" id="message" placeholder="Message" style="height:150px" required><?=htmlspecialchars($fields['message']??'')?></textarea><label for="message">Message</label></div></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary py-3 px-5">Send Message</button></div>
                </div>
            </form>
            <?php endif;?>
        </div>
    </div>
    <div class="row mt-5 wow fadeInUp" data-wow-delay="0.3s">
        <div class="col-12">
            <iframe class="w-100 rounded" style="height:350px;border:0;"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3787.48!2d28.5730!3d-20.1964!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDExJzQ3LjAiUyAyOMKwMzQnMjIuOCJF!5e0!3m2!1sen!2szw!4v1"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"></iframe>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/wow/wow.min.js"></script>
<script src="js/main.js"></script>
<script>(function(){const nav=document.querySelector('.glass-nav');if(nav)window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});})();</script>
</body></html>
