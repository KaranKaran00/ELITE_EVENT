<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('admin');

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_user') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role     = $_POST['role'] ?? 'student';

            if (!in_array($role, ['student', 'teacher', 'admin'], true)) {
                $error = 'Invalid role.';
            } elseif ($name === '' || $email === '' || strlen($password) < 8) {
                $error = 'Fill in a name, valid email, and a password of at least 8 characters.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email address.';
            } else {
                $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'An account with that email already exists.';
                } else {
                    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                    $notice = ucfirst($role) . ' account created for ' . $name . '.';
                }
            }

        } elseif ($action === 'update_role') {
            $targetId = (int) ($_POST['user_id'] ?? 0);
            $newRole  = $_POST['role'] ?? '';
            if (!in_array($newRole, ['student', 'teacher', 'admin'], true)) {
                $error = 'Invalid role.';
            } elseif ($targetId === (int) $currentUser['id']) {
                $error = "You can't change your own role.";
            } else {
                $stmt = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute([$newRole, $targetId]);
                $notice = 'Role updated.';
            }

        } elseif ($action === 'toggle_status') {
            $targetId = (int) ($_POST['user_id'] ?? 0);
            if ($targetId === (int) $currentUser['id']) {
                $error = "You can't suspend your own account.";
            } else {
                $stmt = db()->prepare('SELECT status FROM users WHERE id = ?');
                $stmt->execute([$targetId]);
                $row = $stmt->fetch();
                if ($row) {
                    $newStatus = $row['status'] === 'active' ? 'suspended' : 'active';
                    $stmt = db()->prepare('UPDATE users SET status = ? WHERE id = ?');
                    $stmt->execute([$newStatus, $targetId]);
                    $notice = 'Account ' . ($newStatus === 'active' ? 'reactivated' : 'suspended') . '.';
                }
            }

        } elseif ($action === 'delete_user') {
            $targetId = (int) ($_POST['user_id'] ?? 0);
            if ($targetId === (int) $currentUser['id']) {
                $error = "You can't delete your own account.";
            } else {
                $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$targetId]);
                $notice = 'Account deleted.';
            }
        }
    }
}

$roleFilter = $_GET['role'] ?? 'all';
if (!in_array($roleFilter, ['all', 'admin', 'teacher', 'student'], true)) $roleFilter = 'all';

if ($roleFilter === 'all') {
    $users = db()->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
} else {
    $stmt = db()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
    $stmt->execute([$roleFilter]);
    $users = $stmt->fetchAll();
}

$pageTitle = 'Manage Users — Elite Event';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1>Manage users</h1>
    <p>Create accounts, change roles and permissions, or suspend/delete accounts.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ($notice): ?><div class="form-success">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="admin-grid">

      <div class="admin-panel">
        <h2>Add a new account</h2>
        <p class="form-help" style="margin-top:6px;">Create a student, teacher, or administrator account.</p>
        <form method="post" class="event-form" style="margin-top:12px;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="create_user">
          <div class="form-grid">
            <div class="form-field">
              <label>Full name</label>
              <input type="text" name="name" required>
            </div>
            <div class="form-field">
              <label>Email</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-field">
              <label>Password</label>
              <input type="password" name="password" minlength="8" required>
            </div>
            <div class="form-field">
              <label>Role</label>
              <select name="role">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px;">Create account</button>
        </form>
      </div>

      <div class="admin-panel">
        <div class="section-head" style="margin-bottom:12px;">
          <h2>All accounts</h2>
          <div class="role-filter-tabs">
            <a href="?role=all"     class="<?= $roleFilter === 'all' ? 'active' : '' ?>">All</a>
            <a href="?role=student" class="<?= $roleFilter === 'student' ? 'active' : '' ?>">Students</a>
            <a href="?role=teacher" class="<?= $roleFilter === 'teacher' ? 'active' : '' ?>">Teachers</a>
            <a href="?role=admin"   class="<?= $roleFilter === 'admin' ? 'active' : '' ?>">Admins</a>
          </div>
        </div>

        <div class="dashboard-table">
          <div class="dashboard-row dashboard-row-head">
            <span>Name</span>
            <span>Email</span>
            <span>Role</span>
            <span>Status</span>
            <span>Actions</span>
          </div>
          <?php foreach ($users as $u): ?>
            <div class="dashboard-row">
              <span class="dashboard-event-name"><?= htmlspecialchars($u['name']) ?></span>
              <span><?= htmlspecialchars($u['email']) ?></span>
              <span>
                <?php if ((int) $u['id'] === (int) $currentUser['id']): ?>
                  <span class="role-pill role-pill-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?> (you)</span>
                <?php else: ?>
                  <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="role" onchange="this.form.submit()" class="role-select role-select-<?= htmlspecialchars($u['role']) ?>">
                      <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                      <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                      <option value="admin"   <?= $u['role'] === 'admin'   ? 'selected' : '' ?>>Admin</option>
                    </select>
                  </form>
                <?php endif; ?>
              </span>
              <span><span class="status-badge <?= $u['status'] === 'active' ? 'status-live' : 'status-draft' ?>"><?= htmlspecialchars(ucfirst($u['status'])) ?></span></span>
              <span class="dashboard-row-actions">
                <?php if ((int) $u['id'] !== (int) $currentUser['id']): ?>
                  <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm"><?= $u['status'] === 'active' ? 'Suspend' : 'Reactivate' ?></button>
                  </form>
                  <form method="post" class="inline-form" onsubmit="return confirm('Delete this account permanently?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);">Delete</button>
                  </form>
                <?php endif; ?>
              </span>
            </div>
          <?php endforeach; ?>
          <?php if (!$users): ?>
            <div class="dashboard-row"><span style="color:var(--text-hint);">No accounts in this filter.</span></div>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
