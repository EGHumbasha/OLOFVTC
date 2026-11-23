<?php
session_start();
require 'db.php';
require 'auth.php';

if (($_SESSION['role'] ?? '') !== 'superadmin') {
    header('Location: dashboard.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php'); exit;
}

$id    = intval($_POST['id'] ?? 0);
$email = strtolower(trim($_POST['email'] ?? ''));
$role  = $_POST['role'] ?? 'admin';
$pass  = $_POST['password'] ?? '';

if ($id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = ['danger', 'Invalid data submitted.'];
    header('Location: users.php'); exit;
}

if (!in_array($role, ['admin', 'superadmin'], true)) {
    $_SESSION['flash'] = ['danger', 'Invalid role.'];
    header('Location: users.php'); exit;
}

// Check email uniqueness
$check = $conn->prepare("SELECT id FROM admin_users WHERE email=? AND id<>? LIMIT 1");
$check->execute([$email, $id]);
if ($check->fetch()) {
    $_SESSION['flash'] = ['danger', 'Email address is already in use by another account.'];
    header('Location: users.php'); exit;
}

if (!empty($pass)) {
    if (strlen($pass) < 8) {
        $_SESSION['flash'] = ['danger', 'New password must be at least 8 characters.'];
        header('Location: users.php'); exit;
    }
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
    $conn->prepare("UPDATE admin_users SET email=?, role=?, password=? WHERE id=?")
         ->execute([$email, $role, $hash, $id]);
} else {
    $conn->prepare("UPDATE admin_users SET email=?, role=? WHERE id=?")
         ->execute([$email, $role, $id]);
}

$_SESSION['flash'] = ['success', 'User <strong>' . htmlspecialchars($email) . '</strong> updated successfully.'];
header('Location: users.php');
exit;
