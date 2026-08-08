<?php
require_once __DIR__ . '/includes/data.php';

if ($currentUser) {
    header('Location: ' . base_path() . role_home($currentUser['role']));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $result = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '', 'admin');
        if ($result === true) {
            header('Location: ' . base_path() . 'admin/dashboard.php');
            exit;
        }
        $error = $result;
    }
}

$pageTitle = 'Admin Login — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="container auth-container">
    <div class="auth-card">
      <span class="auth-role-badge role-pill role-pill-admin"><i class="ti ti-shield-lock"></i> Admin Login</span>
      <h1>Admin access</h1>
      <p class="auth-sub">Restricted to site administrators.</p>

      <?php if ($error): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="admin-login.php" class="auth-form">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="admin@eliteevent.local" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit" class="btn btn-primary btn-block">Log in as Admin</button>
      </form>

      <p class="auth-note">Admin accounts are created by another admin, not through sign up.</p>
      <p class="auth-switch"><a href="student-login.php">Student login</a> · <a href="teacher-login.php">Teacher login</a></p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
