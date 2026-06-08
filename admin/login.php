<?php
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit; }
require "db.php";
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (empty($email) || empty($pass)) {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password'])) {
            if (!$user['is_active']) {
                $error = "Your account has been suspended. Contact your administrator.";
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['email']    = $user['email'];
                if ($user['must_change_password']) { $_SESSION['force_change']=true; header("Location: force_change_password.php"); exit; }
                header("Location: dashboard.php"); exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><title>Admin Login — Our Lady of Fatima VTC</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="../css/bootstrap.min.css" rel="stylesheet">
<style>
body{min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;overflow:hidden;position:relative;}
body::before{content:"";position:fixed;inset:0;background:linear-gradient(-45deg,#6a11cb,#2575fc,#1fd1f9,#ffb347);background-size:400% 400%;animation:gradBG 15s ease infinite;z-index:-2;}
body::after{content:"";position:fixed;top:50%;left:50%;width:400px;height:400px;background:url('../img/logo.png') no-repeat center/contain;opacity:.05;transform:translate(-50%,-50%);z-index:-1;}
@keyframes gradBG{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.login-card{background:rgba(255,255,255,.96);padding:40px 32px;border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,.22);width:100%;max-width:400px;text-align:center;opacity:0;transform:translateY(20px);transition:opacity .5s,transform .5s;}
.login-card.show{opacity:1;transform:translateY(0);}
.login-card .logo img{width:120px;margin-bottom:16px;}
.login-card h3{font-weight:700;color:#333;margin-bottom:20px;}
.form-control{border-radius:8px;padding:10px 14px;}
.btn-submit{border-radius:8px;padding:10px;background:#2575fc;border:none;color:#fff;font-weight:700;width:100%;transition:background .3s,transform .2s;}
.btn-submit:hover{background:#1b5fd9;transform:scale(1.02);}
.forgot-pass{display:block;margin-top:12px;font-size:.9rem;color:#555;text-decoration:none;}
.forgot-pass:hover{color:#2575fc;text-decoration:underline;}
</style></head><body>
<div class="login-card" id="loginCard">
    <div class="logo"><img src="../img/logo.png" alt="Logo"></div>
    <h3>Admin Login</h3>
    <?php if($error): ?><div class="alert alert-danger py-2"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST" novalidate>
        <div class="mb-3 text-start"><input type="email" name="email" class="form-control" placeholder="Email address" required value="<?=htmlspecialchars($_POST['email']??'')?>"></div>
        <div class="mb-3 text-start"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <button type="submit" class="btn-submit">Login</button>
        <a href="forgot_password.php" class="forgot-pass">Forgot your password?</a>
    </form>
</div>
<script>window.addEventListener('DOMContentLoaded',()=>document.getElementById('loginCard').classList.add('show'));</script>
</body></html>
