<?php
session_start();
require 'db.php';
require 'auth.php';
if (($_SESSION['role']??'')!=='superadmin'){header('Location: dashboard.php');exit;}

if(isset($_GET['id'], $_GET['action'])){
    $id = $_GET['id'];
    $action = $_GET['action'];

    // Prevent self-suspend
    if($id == $_SESSION['admin_id']){
        $_SESSION['flash'] = ['danger','You cannot suspend/reactivate your own account.'];
        header("Location: users.php");
        exit;
    }

    if($action === 'suspend'){
        $stmt = $conn->prepare("UPDATE admin_users SET is_active=0 WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['success','User suspended.'];
    } elseif($action === 'activate'){
        $stmt = $conn->prepare("UPDATE admin_users SET is_active=1 WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['success','User reactivated.'];
    }

    header("Location: users.php");
    exit;
}
?>
