<?php
session_start(); require "db.php";
$token=trim($_GET['token']??'');
$msg=''; $msgType='danger'; $valid=false;

if($token){
    $stmt=$conn->prepare("SELECT * FROM password_resets WHERE token=? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]); $reset=$stmt->fetch();
    if($reset){ $valid=true; }
    else { $msg="This reset link is invalid or has expired."; }
}

if($_SERVER['REQUEST_METHOD']==='POST'&&$valid){
    $pass=$_POST['password']??''; $confirm=$_POST['confirm']??'';
    if(strlen($pass)<8){ $msg="Password must be at least 8 characters."; $msgType='danger'; }
    elseif($pass!==$confirm){ $msg="Passwords do not match."; $msgType='danger'; }
    else {
        $hash=password_hash($pass,PASSWORD_DEFAULT);
        $conn->prepare("UPDATE admin_users SET password=?,must_change_password=0 WHERE email=?")->execute([$hash,$reset['email']]);
        $conn->prepare("DELETE FROM password_resets WHERE token=?")->execute([$token]);
        $msg="Password updated successfully! You can now login."; $msgType='success'; $valid=false;
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Reset Password</title>
<link href="../css/bootstrap.min.css" rel="stylesheet">
<style>body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(-45deg,#6a11cb,#2575fc,#1fd1f9,#ffb347);background-size:400% 400%;animation:g 15s ease infinite;}
@keyframes g{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.card{max-width:420px;width:100%;border-radius:14px;padding:36px 32px;box-shadow:0 8px 28px rgba(0,0,0,.22);}</style></head><body>
<div class="card bg-white text-center">
<img src="../img/logo.png" style="width:100px;margin-bottom:16px" alt="Logo">
<h4 class="mb-3">New Password</h4>
<?php if($msg): ?><div class="alert alert-<?=$msgType?>"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if($valid): ?>
<form method="POST">
<div class="mb-3 text-start"><label class="form-label">New Password <small>(min 8 chars)</small></label>
<input type="password" name="password" class="form-control" required minlength="8"></div>
<div class="mb-3 text-start"><label class="form-label">Confirm Password</label>
<input type="password" name="confirm" class="form-control" required></div>
<button class="btn btn-primary w-100">Save Password</button>
</form>
<?php endif; ?>
<a href="login.php" class="d-block mt-3 small text-muted">← Back to Login</a>
</div></body></html>
