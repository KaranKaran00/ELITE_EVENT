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
        $result = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '', 'teacher');
        if ($result === true) {
            header('Location: ' . base_path() . 'teacher/dashboard.php');
            exit;
        }
        $error = $result;
    }
}

$pageTitle = 'Teacher Login — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="container auth-container">
    <div class="auth-card">
      <span class="auth-role-badge role-pill role-pill-teacher"><i class="ti ti-chalkboard"></i> Teacher Login</span>
      <h1>Welcome back, teacher 👋</h1>
      <p class="auth-sub">Log in to create and manage your events.</p>

      <?php if ($error): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="teacher-login.php" class="auth-form">
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@college.edu" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit" class="btn btn-primary btn-block">Log in as Teacher</button>
      </form>

      <p class="auth-note">Not a teacher? <a href="student-login.php">Student login</a> · <a href="admin-login.php">Admin login</a></p>
      <p class="auth-switch">New here? <a href="signup.php">Sign up</a></p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
