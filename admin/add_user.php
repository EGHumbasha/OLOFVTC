<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill all fields.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $conn->prepare("INSERT INTO admin_users (email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$email, $hashedPassword, $role, 1]);
                $success = "User added successfully!";
            } catch (PDOException $e) {
                $error = "Error adding user: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New User</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .header-buttons { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .header-buttons img { height:50px; }
        .header-buttons .btn-group button, .header-buttons .btn-group a { margin-left:5px; }
    </style>
</head>
<body>
<div class="form-container">
    <div class="header-buttons">
        <img src="../img/logo.png" alt="Logo">
        <div class="btn-group">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="../index.php" class="btn btn-info">Back to Website</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <h3 class="text-center mb-4">Add New User</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Add User</button>
    </form>
</div>
</body>
</html>
