<?php
require_once __DIR__.'/admin/config.php';
require_once __DIR__.'/admin/db.php';
require_once __DIR__.'/admin/send_mail.php';
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'Method not allowed.']);exit;}
if(!empty($_POST['hp']??'')){echo json_encode(['success'=>true,'message'=>'Subscribed!']);exit;}
$email=trim($_POST['email']??'');
if(!$email){echo json_encode(['success'=>false,'message'=>'Please enter your email address.']);exit;}
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){echo json_encode(['success'=>false,'message'=>'Please enter a valid email address.']);exit;}
try {
    $stmt=$conn->prepare("SELECT id FROM newsletter_subscribers WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    if($stmt->fetch()){echo json_encode(['success'=>true,'message'=>'You are already subscribed. Thank you!']);exit;}
    $conn->prepare("INSERT INTO newsletter_subscribers (email,subscribed_at) VALUES(?,NOW())")->execute([$email]);
    $body="<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;'>
    <div style='background:#005f99;padding:20px 28px;border-radius:8px 8px 0 0;'><h2 style='color:#fff;margin:0;'>You're subscribed!</h2></div>
    <div style='padding:20px 28px;border:1px solid #e0e0e0;border-top:none;border-radius:0 0 8px 8px;'>
    <p>Thank you for subscribing to updates from <strong>".SCHOOL_NAME."</strong>.</p>
    <p>We'll keep you informed about new courses, events, and news.</p>
    <hr><p style='color:#888;font-size:12px;'>".SCHOOL_NAME." · ".SCHOOL_ADDRESS."</p>
    </div></div>";
    sendEmail($email,"You're subscribed — ".SCHOOL_NAME,$body);
    echo json_encode(['success'=>true,'message'=>'Thank you for subscribing!']);
} catch(PDOException $e){
    error_log("Newsletter error: ".$e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Something went wrong. Please try again.']);
}
