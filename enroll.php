<?php
require_once __DIR__.'/admin/config.php';
require_once __DIR__.'/admin/send_mail.php';
$errors=[];$success=false;$fields=[];
$courses=['Bakery Studies','Catering','Cutting and Designing','Interior Home Decor','Beauty Therapy','Computers','Welding and Sheet Metalwork','Woodwork','Events Decor','Hairdressing'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['website']??'')){$success=true;}
    else {
        $fields=['firstName'=>trim($_POST['firstName']??''),'lastName'=>trim($_POST['lastName']??''),'email'=>trim($_POST['email']??''),'phone'=>trim($_POST['phone']??''),'course'=>trim($_POST['course']??''),'address'=>trim($_POST['address']??''),'dob'=>trim($_POST['dob']??''),'gender'=>trim($_POST['gender']??'')];
        if(empty($fields['firstName'])) $errors[]="First name is required.";
        if(empty($fields['lastName']))  $errors[]="Last name is required.";
        if(empty($fields['email'])||!filter_var($fields['email'],FILTER_VALIDATE_EMAIL)) $errors[]="A valid email address is required.";
        if(empty($fields['phone'])||!preg_match('/^[+\d\s\-()]{7,20}$/',$fields['phone'])) $errors[]="A valid phone number is required.";
        if(empty($fields['course'])||!in_array($fields['course'],$courses,true)) $errors[]="Please select a valid course.";
        if(empty($fields['address'])) $errors[]="Address is required.";
        if(empty($errors)){
            $full=htmlspecialchars($fields['firstName'].' '.$fields['lastName']);
            $email=$fields['email']; $course=htmlspecialchars($fields['course']);
            $phone=htmlspecialchars($fields['phone']); $addr=nl2br(htmlspecialchars($fields['address']));
            $dob=htmlspecialchars($fields['dob']); $gender=htmlspecialchars($fields['gender']);
            $adminBody="<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'><h2 style='color:#005f99;'>New Enrollment Application</h2><table style='width:100%;border-collapse:collapse;'><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;width:35%;'>Full Name</td><td style='padding:8px;border:1px solid #ddd;'>{$full}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Email</td><td style='padding:8px;border:1px solid #ddd;background:#f8f8f8;'>{$email}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;'>Phone</td><td style='padding:8px;border:1px solid #ddd;'>{$phone}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Date of Birth</td><td style='padding:8px;border:1px solid #ddd;background:#f8f8f8;'>{$dob}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;'>Gender</td><td style='padding:8px;border:1px solid #ddd;'>{$gender}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Course Applied</td><td style='padding:8px;border:1px solid #ddd;background:#f8f8f8;'>{$course}</td></tr><tr><td style='padding:8px;border:1px solid #ddd;font-weight:bold;'>Address</td><td style='padding:8px;border:1px solid #ddd;'>{$addr}</td></tr></table><p style='margin-top:16px;color:#666;font-size:13px;'>Submitted: ".date('d F Y, H:i')."</p></div>";
            $sent=sendEmail(REGISTRATION_EMAIL,"New Enrollment — {$full} ({$course})",$adminBody,$email);
            $confirmBody="<div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'><div style='background:#005f99;padding:24px 32px;border-radius:8px 8px 0 0;'><h2 style='color:#fff;margin:0;'>Thank You, {$full}!</h2></div><div style='padding:24px 32px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;'><p>We have received your enrollment application for <strong>{$course}</strong> at <strong>".SCHOOL_NAME."</strong>.</p><p>Our admissions team will review your application and contact you within <strong>2–3 business days</strong>.</p><h4>Application Summary</h4><table style='width:100%;border-collapse:collapse;'><tr><td style='padding:6px;border:1px solid #ddd;font-weight:bold;width:35%;'>Name</td><td style='padding:6px;border:1px solid #ddd;'>{$full}</td></tr><tr><td style='padding:6px;border:1px solid #ddd;font-weight:bold;background:#f8f8f8;'>Course</td><td style='padding:6px;border:1px solid #ddd;background:#f8f8f8;'>{$course}</td></tr><tr><td style='padding:6px;border:1px solid #ddd;font-weight:bold;'>Phone</td><td style='padding:6px;border:1px solid #ddd;'>{$phone}</td></tr></table><p style='margin-top:20px;'>📞 ".SCHOOL_PHONE."<br>📧 <a href='mailto:".SCHOOL_EMAIL."'>".SCHOOL_EMAIL."</a><br>📍 ".SCHOOL_ADDRESS."</p></div></div>";
            sendEmail($email,"Enrollment Application Received — ".SCHOOL_NAME,$confirmBody);
            if($sent){$success=true;}else{$errors[]="Could not send application. Please call us at ".SCHOOL_PHONE.".";}
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Enroll With Us — Our Lady of Fatima VTC</title>
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
.page-header{background:linear-gradient(rgba(24,29,56,.7),rgba(24,29,56,.7)),url(img/carousel-1.jpg);background-position:center;background-size:cover;}
.hp-field{display:none!important;}
.enroll-card{background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);padding:36px;}
@media(max-width:991px){.navbar-nav{background:rgba(255,255,255,.98);padding:8px 12px;border-radius:10px;}#logo{height:72px;}}
</style>
</head><body>
<div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border" style="width:3rem;height:3rem;color:var(--primary);" role="status"><span class="visually-hidden">Loading...</span></div>
</div>
<?php include 'navbar.php'; ?>
<div class="container-fluid bg-primary py-5 mb-5 page-header">
    <div class="container py-5"><div class="row justify-content-center"><div class="col-lg-10 text-center">
        <h1 class="display-3 text-white animated slideInDown">Enroll With Us</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><a class="text-white" href="index.html">Home</a></li>
            <li class="breadcrumb-item text-white active">Enroll</li>
        </ol></nav>
    </div></div></div>
</div>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="enroll-card wow fadeInUp" data-wow-delay="0.1s">
                <div class="text-center mb-4">
                    <h6 class="section-title text-primary text-uppercase">Join Us</h6>
                    <h2 class="mb-2">Start Your Enrollment</h2>
                    <p class="text-muted">Fill in the form and our admissions team will contact you within 2–3 business days.</p>
                </div>
                <?php if($success): ?>
                <div class="alert alert-success text-center p-4">
                    <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success"></i>
                    <h5>Application Submitted!</h5>
                    <p class="mb-0">A confirmation has been sent to your email. Our team will be in touch shortly.</p>
                    <a href="index.html" class="btn btn-primary mt-3">Back to Home</a>
                </div>
                <?php else: ?>
                <?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif;?>
                <form method="POST" action="enroll.php" novalidate>
                    <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="firstName" value="<?=htmlspecialchars($fields['firstName']??'')?>" required placeholder="First Name"></div>
                        <div class="col-md-6"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="lastName" value="<?=htmlspecialchars($fields['lastName']??'')?>" required placeholder="Last Name"></div>
                        <div class="col-md-6"><label class="form-label">Email Address <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?=htmlspecialchars($fields['email']??'')?>" required placeholder="your@email.com"></div>
                        <div class="col-md-6"><label class="form-label">Phone Number <span class="text-danger">*</span></label><input type="tel" class="form-control" name="phone" value="<?=htmlspecialchars($fields['phone']??'')?>" required placeholder="+263 7XX XXX XXX"></div>
                        <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="dob" value="<?=htmlspecialchars($fields['dob']??'')?>"></div>
                        <div class="col-md-6"><label class="form-label">Gender</label><select class="form-select" name="gender"><option value="">Select...</option><option value="Male" <?=(($fields['gender']??'')==='Male')?'selected':''?>>Male</option><option value="Female" <?=(($fields['gender']??'')==='Female')?'selected':''?>>Female</option><option value="Other" <?=(($fields['gender']??'')==='Other')?'selected':''?>>Other / Prefer not to say</option></select></div>
                        <div class="col-12"><label class="form-label">Course <span class="text-danger">*</span></label><select class="form-select" name="course" required><option value="" disabled <?=empty($fields['course'])?'selected':''?>>Select a Course</option><?php foreach($courses as $c): ?><option value="<?=htmlspecialchars($c)?>" <?=(($fields['course']??'')===$c)?'selected':''?>><?=htmlspecialchars($c)?></option><?php endforeach;?></select></div>
                        <div class="col-12"><label class="form-label">Home Address <span class="text-danger">*</span></label><textarea class="form-control" name="address" rows="3" required placeholder="Your home address"><?=htmlspecialchars($fields['address']??'')?></textarea></div>
                        <div class="col-12 text-center mt-2"><button type="submit" class="btn btn-primary py-3 px-5 fs-5">Submit Application <i class="fa fa-arrow-right ms-2"></i></button></div>
                    </div>
                </form>
                <?php endif;?>
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
<script>(function(){const nav=document.querySelector('.glass-nav');if(nav)window.addEventListener('scroll',()=>nav.classList.toggle('shrink',window.scrollY>80),{passive:true});})();</script>
</body></html>
