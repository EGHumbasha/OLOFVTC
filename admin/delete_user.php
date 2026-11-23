<?php
session_start();
require 'db.php';
require 'auth.php';
if (($_SESSION['role']??'')!=='superadmin'){header('Location: dashboard.php');exit;}

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Prevent deleting yourself
    if($id == $_SESSION['admin_id']){
        $_SESSION['flash'] = ['danger','You cannot delete your own account.'];
        header("Location: users.php");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id=?");
    $stmt->execute([$id]);

    $_SESSION['flash'] = ['success','User deleted successfully.'];
    header("Location: users.php");
    exit;
}
?>
