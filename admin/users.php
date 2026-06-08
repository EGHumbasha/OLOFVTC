<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

// Handle Add User
$msg = '';
if (isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("INSERT INTO admin_users (email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$email, $password, $role, $is_active]);
        $msg = "User added successfully!";
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM admin_users ORDER BY role")->fetchAll(PDO::FETCH_ASSOC);

// Function to escape output
function e($str){ return htmlspecialchars($str); }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 40px; }
        .header-buttons { display:flex; justify-content: space-between; align-items: center; margin-bottom:20px; }
        .header-buttons img { height:50px; }
        .header-buttons .btn-group button, .header-buttons .btn-group a { margin-left:5px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-buttons">
        <img src="../img/logo.png" alt="Logo">
        <div class="btn-group">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="../index.php" class="btn btn-info">Back to Website</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <h3>Manage Admin Users</h3>
    <hr>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= e($msg) ?></div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="card mb-4">
        <div class="card-header">Add New User</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">All Users</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= e($u['email']) ?></td>
                            <td><?= strtoupper(e($u['role'])) ?></td>
                            <td><?= isset($u['is_active']) && $u['is_active'] ? 'Active' : 'Suspended' ?></td>
                            <td><?= e($u['created_at']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php if(isset($u['is_active']) && $u['is_active']): ?>
                                    <a href="toggle_user.php?id=<?= $u['id'] ?>&action=suspend" class="btn btn-sm btn-secondary">Suspend</a>
                                <?php else: ?>
                                    <a href="toggle_user.php?id=<?= $u['id'] ?>&action=activate" class="btn btn-sm btn-success">Activate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
