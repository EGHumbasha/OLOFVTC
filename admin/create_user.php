<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $role = $_POST['role'];

    // Auto-generate secure password
    $temp_pass = bin2hex(random_bytes(4));  // 8 characters
    $hashed = password_hash($temp_pass, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO admin_users (email, password, role, must_change_password) VALUES (?, ?, ?, 1)");
    $stmt->execute([$email, $hashed, $role]);

    // Send email
    require "send_mail.php";
    sendEmail(
        $email,
        "Your Admin Login Credentials",
        "<p>Hello,</p>
         <p>You have been added as an admin user.</p>
         <p><strong>Email:</strong> $email<br>
         <strong>Temporary Password:</strong> $temp_pass</p>
         <p>Login here: <a href='https://fatima.co.zw/admin/login.php'>Admin Login</a></p>
         <p>You will be asked to create a new password on first login.</p>"
    );

    $message = "User created and login email sent!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">

    <h3>Create New Admin User</h3>
    <hr>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">

        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required>

        <label class="form-label mt-3">Role</label>
        <select name="role" class="form-control">
            <option value="uploader">Uploader</option>
            <option value="superadmin">Super Admin</option>
        </select>

        <button class="btn btn-primary mt-3">Create User</button>
    </form>

</div>
</body>
</html>
