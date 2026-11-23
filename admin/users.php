<?php
session_start();
require 'db.php';
require 'auth.php';

// Superadmin only
if (($_SESSION['role'] ?? '') !== 'superadmin') {
    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = [];
$formData = [];

// ── Add User ──────────────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role']     ?? 'admin';
    $active   = isset($_POST['is_active']) ? 1 : 0;
    $force    = isset($_POST['must_change']) ? 1 : 0;
    $formData = compact('email', 'role', 'active', 'force');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 8)                       $errors[] = 'Password must be at least 8 characters.';
    if (!in_array($role, ['admin','superadmin'], true)) $errors[] = 'Invalid role.';

    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $conn->prepare("INSERT INTO admin_users (email,password,role,is_active,must_change_password,created_at) VALUES(?,?,?,?,?,NOW())")
                 ->execute([$email, $hash, $role, $active, $force]);
            $_SESSION['flash'] = ['success', 'User <strong>' . htmlspecialchars($email) . '</strong> created successfully.'];
            header('Location: users.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'That email address is already registered.';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ── Reset Password (admin sets it directly) ───────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $uid      = intval($_POST['user_id'] ?? 0);
    $newpass  = $_POST['new_password'] ?? '';
    $force    = isset($_POST['force_change']) ? 1 : 0;

    if ($uid <= 0)             $errors[] = 'Invalid user.';
    if (strlen($newpass) < 8)  $errors[] = 'New password must be at least 8 characters.';

    if (empty($errors)) {
        $hash = password_hash($newpass, PASSWORD_BCRYPT, ['cost' => 12]);
        $conn->prepare("UPDATE admin_users SET password=?, must_change_password=? WHERE id=?")
             ->execute([$hash, $force, $uid]);
        $_SESSION['flash'] = ['success', 'Password reset successfully.'];
        header('Location: users.php');
        exit;
    }
}

// ── Toggle Active ─────────────────────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = intval($_GET['toggle']);
    if ($uid === (int)$_SESSION['admin_id']) {
        $_SESSION['flash'] = ['danger', 'You cannot suspend your own account.'];
    } else {
        $cur = $conn->prepare("SELECT is_active FROM admin_users WHERE id=?");
        $cur->execute([$uid]);
        $row = $cur->fetch();
        if ($row) {
            $newState = $row['is_active'] ? 0 : 1;
            $conn->prepare("UPDATE admin_users SET is_active=? WHERE id=?")->execute([$newState, $uid]);
            $_SESSION['flash'] = ['success', 'User ' . ($newState ? 'activated' : 'suspended') . '.'];
        }
    }
    header('Location: users.php');
    exit;
}

// ── Delete User ───────────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid === (int)$_SESSION['admin_id']) {
        $_SESSION['flash'] = ['danger', 'You cannot delete your own account.'];
    } else {
        $conn->prepare("DELETE FROM admin_users WHERE id=?")->execute([$uid]);
        $_SESSION['flash'] = ['success', 'User deleted.'];
    }
    header('Location: users.php');
    exit;
}

$users = $conn->query("SELECT * FROM admin_users ORDER BY role ASC, created_at ASC")->fetchAll();

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Users — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
.role-badge-superadmin{background:#7b2ff7;color:#fff;padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:700;}
.role-badge-admin{background:#005f99;color:#fff;padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:700;}
.modal .form-control:focus{border-color:#005f99;box-shadow:0 0 0 3px rgba(0,95,153,.15);}
</style>
</head>
<body>

<button class="toggle-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Manage Users</h4>
            <small class="text-muted">Create, edit, suspend, reset passwords for admin accounts</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i>Add New User
        </button>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash[0]) ?> alert-dismissible fade show">
        <?= $flash[1] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Error:</strong>
        <ul class="mb-0 mt-1 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-people me-2"></i>Admin Accounts <span class="badge bg-secondary"><?= count($users) ?></span></strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Pwd Change</th>
                        <th>Created</th>
                        <th style="min-width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): $isSelf = ((int)$u['id'] === (int)$_SESSION['admin_id']); ?>
                <tr class="<?= !$u['is_active'] ? 'table-secondary' : '' ?>">
                    <td>
                        <div class="fw-semibold"><?= e($u['email']) ?></div>
                        <?php if ($isSelf): ?><small class="text-muted">(you)</small><?php endif; ?>
                    </td>
                    <td>
                        <span class="role-badge-<?= e($u['role']) ?>"><?= strtoupper(e($u['role'])) ?></span>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Suspended</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['must_change_password']): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Required</span>
                        <?php else: ?>
                        <span class="badge bg-light text-muted border">OK</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap text-muted" style="font-size:.84rem;">
                        <?= date('d M Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <!-- Edit -->
                            <button class="btn btn-sm btn-outline-warning"
                                onclick="openEditModal(<?= $u['id'] ?>, '<?= e(addslashes($u['email'])) ?>', '<?= e($u['role']) ?>')"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- Reset Password -->
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openResetModal(<?= $u['id'] ?>, '<?= e(addslashes($u['email'])) ?>')"
                                title="Reset Password">
                                <i class="bi bi-key"></i>
                            </button>

                            <!-- Suspend / Activate -->
                            <?php if (!$isSelf): ?>
                            <a href="users.php?toggle=<?= $u['id'] ?>"
                               class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                               title="<?= $u['is_active'] ? 'Suspend' : 'Activate' ?>"
                               onclick="return confirm('<?= $u['is_active'] ? 'Suspend' : 'Activate' ?> this user?')">
                                <i class="bi bi-<?= $u['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i>
                            </a>

                            <!-- Delete -->
                            <a href="users.php?delete=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger" title="Delete"
                               onclick="return confirm('Permanently delete <?= e(addslashes($u['email'])) ?>? This cannot be undone.')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Add User Modal ── -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? $_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required
                            value="<?= e($formData['email'] ?? '') ?>" placeholder="user@ourladyoffatimavtc.co.zw">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="newUserPw" class="form-control" required minlength="8" placeholder="Min. 8 characters">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('newUserPw','newUserPwIco')">
                                <i class="bi bi-eye" id="newUserPwIco"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin" <?= (($formData['role'] ?? 'admin') === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="superadmin" <?= (($formData['role'] ?? '') === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                        </select>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="newIsActive" value="1"
                                <?= !isset($formData['active']) || $formData['active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="newIsActive">Active</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="must_change" id="newMustChange" value="1"
                                <?= !empty($formData['force']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="newMustChange">Must change password on first login</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Edit User Modal ── -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="edit_user.php">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? $_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" id="editUserRole" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted">
                            New Password <small>(leave blank to keep current)</small>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="editUserPw" class="form-control" placeholder="Leave blank to keep current">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('editUserPw','editUserPwIco')">
                                <i class="bi bi-eye" id="editUserPwIco"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Reset Password Modal ── -->
<div class="modal fade" id="resetPwModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset User Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? $_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                <div class="modal-body">
                    <p class="text-muted mb-3">Resetting password for: <strong id="resetUserEmail"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="resetPwField" class="form-control"
                                required minlength="8" placeholder="Min. 8 characters">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('resetPwField','resetPwIco')">
                                <i class="bi bi-eye" id="resetPwIco"></i>
                            </button>
                        </div>
                        <div id="resetPwStrength" class="mt-1" style="height:4px;background:#e9ecef;border-radius:2px;"><div id="resetPwBar" style="height:100%;width:0;border-radius:2px;transition:width .3s,background .3s;"></div></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="resetPwConfirm" class="form-control" placeholder="Repeat new password">
                        <small id="resetPwMatch" class="form-text"></small>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="force_change" id="resetForce" value="1" checked>
                        <label class="form-check-label" for="resetForce">
                            Require user to change this password on next login
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="resetPwSubmit">
                        <i class="bi bi-key me-1"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

// Show/hide password
function togglePw(fieldId, icoId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(icoId);
    const show = f.type === 'password';
    f.type = show ? 'text' : 'password';
    i.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
}

// Open edit modal
function openEditModal(id, email, role) {
    document.getElementById('editUserId').value    = id;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserRole').value  = role;
    document.getElementById('editUserPw').value    = '';
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

// Open reset password modal
function openResetModal(id, email) {
    document.getElementById('resetUserId').value   = id;
    document.getElementById('resetUserEmail').textContent = email;
    document.getElementById('resetPwField').value   = '';
    document.getElementById('resetPwConfirm').value = '';
    document.getElementById('resetPwMatch').textContent  = '';
    document.getElementById('resetPwBar').style.width = '0';
    new bootstrap.Modal(document.getElementById('resetPwModal')).show();
}

// Password strength meter in reset modal
const pwField   = document.getElementById('resetPwField');
const pwConfirm = document.getElementById('resetPwConfirm');
const pwBar     = document.getElementById('resetPwBar');
const pwMatch   = document.getElementById('resetPwMatch');

if (pwField) {
    pwField.addEventListener('input', function () {
        const v = this.value;
        const score = [v.length >= 8, /[A-Z]/.test(v), /[0-9]/.test(v), /[^A-Za-z0-9]/.test(v)].filter(Boolean).length;
        const cfg = {0:['0%','#e9ecef'],1:['25%','#dc3545'],2:['50%','#fd7e14'],3:['75%','#ffc107'],4:['100%','#28a745']};
        pwBar.style.width = cfg[score][0];
        pwBar.style.background = cfg[score][1];
        checkMatch();
    });
    pwConfirm.addEventListener('input', checkMatch);
}

function checkMatch() {
    if (!pwConfirm.value) { pwMatch.textContent = ''; return; }
    if (pwField.value === pwConfirm.value) {
        pwMatch.textContent = '✓ Passwords match'; pwMatch.style.color = '#28a745';
    } else {
        pwMatch.textContent = '✗ Passwords do not match'; pwMatch.style.color = '#dc3545';
    }
}

// Validate confirm password before submit
document.getElementById('resetPwSubmit')?.closest('form').addEventListener('submit', function(e) {
    const pw  = document.getElementById('resetPwField').value;
    const pwc = document.getElementById('resetPwConfirm').value;
    if (pw !== pwc) { e.preventDefault(); alert('Passwords do not match.'); }
    if (pw.length < 8) { e.preventDefault(); alert('Password must be at least 8 characters.'); }
});

// Auto-open add user modal if there were validation errors
<?php if (!empty($errors) && isset($_POST['action']) && $_POST['action'] === 'add_user'): ?>
window.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('addUserModal')).show();
});
<?php endif; ?>
</script>
<?php include 'session_warning.php'; ?>
</body>
</html>
