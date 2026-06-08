<?php
session_start();
require "db.php";

if (!isset($_SESSION['force_change'])) {
    header("Location: dashboard.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id = $_SESSION['admin_id'];

    $stmt = $conn->prepare("UPDATE admin_users SET password=?, must_change_password=0 WHERE id=?");
    $stmt->execute([$pass, $id]);

    unset($_SESSION['force_change']);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Password</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">

    <h3>Set Your New Password</h3>
    <hr>

    <form method="POST">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" required>

        <button class="btn btn-primary mt-3">Save Password</button>
    </form>

</div>

</body>
</html>
