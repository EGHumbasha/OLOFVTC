<?php
/**
 * admin/forgot_password.php — Password reset via email token
 */
session_start(); require "db.php"; require "send_mail.php";
$msg=''; $msgType='info'; $sent=false;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=trim($_POST['email']??'');
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $msg="Please enter a valid email."; $msgType='danger'; }
    else {
        $stmt=$conn->prepare("SELECT id FROM admin_users WHERE email=? AND is_active=1 LIMIT 1");
        $stmt->execute([$email]); $user=$stmt->fetch();
        if($user){
            $token=bin2hex(random_bytes(32));
            $expiry=date('Y-m-d H:i:s',strtotime('+1 hour'));
            // Store token (add password_resets table via migrate.sql)
            try {
                $conn->prepare("DELETE FROM password_resets WHERE email=?")->execute([$email]);
                $conn->prepare("INSERT INTO password_resets (email,token,expires_at) VALUES(?,?,?)")->execute([$email,$token,$expiry]);
                $link=SITE_URL."/admin/reset_password.php?token=".$token;
                sendEmail($email,"Password Reset — ".SCHOOL_NAME,
                "<p>Hello,</p><p>Click the link below to reset your password. This link expires in 1 hour.</p>
                <p><a href='$link'>$link</a></p>
                <p>If you did not request this, ignore this email.</p>");
            } catch(Exception $e){ error_log("Reset error: ".$e->getMessage()); }
        }
        // Always show same message (don't reveal if email exists)
        $msg="If that email is registered, a reset link has been sent."; $msgType='success'; $sent=true;
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Forgot Password</title>
<link href="../css/bootstrap.min.css" rel="stylesheet">
<style>
body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(-45deg,#6a11cb,#2575fc,#1fd1f9,#ffb347);background-size:400% 400%;animation:g 15s ease infinite;}
@keyframes g{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.card{max-width:400px;width:100%;border-radius:14px;padding:36px 32px;box-shadow:0 8px 28px rgba(0,0,0,.22);}
</style></head><body>
<div class="card bg-white text-center">
<img src="../img/logo.png" style="width:100px;margin-bottom:16px" alt="Logo">
<h4 class="mb-3">Reset Password</h4>
<?php if($msg): ?><div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if(!$sent): ?>
<form method="POST">
<div class="mb-3 text-start"><label class="form-label">Email address</label>
<input type="email" name="email" class="form-control" required placeholder="admin@example.com"></div>
<button class="btn btn-primary w-100">Send Reset Link</button>
</form>
<?php endif; ?>
<a href="login.php" class="d-block mt-3 small text-muted">← Back to Login</a>
</div></body></html>
