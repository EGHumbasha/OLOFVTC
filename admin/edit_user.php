<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = trim($_POST['password']);

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email=? AND id<>? LIMIT 1");
    $stmt->execute([$email,$id]);
    if($stmt->fetch()) {
        $_SESSION['flash'] = ['danger','Email already exists.'];
        header("Location: users.php");
        exit;
    }

    if(!empty($password)){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_users SET email=?, role=?, password=? WHERE id=?");
        $stmt->execute([$email,$role,$hash,$id]);
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET email=?, role=? WHERE id=?");
        $stmt->execute([$email,$role,$id]);
    }

    $_SESSION['flash'] = ['success','User updated successfully.'];
    header("Location: users.php");
    exit;
}
?>
