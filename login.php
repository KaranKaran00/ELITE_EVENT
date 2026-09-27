<?php
require_once __DIR__ . '/includes/data.php';

if ($currentUser) {
    header('Location: ' . base_path() . role_home($currentUser['role']));
    exit;
}

$pageTitle = 'Log in — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="container auth-container">
    <div class="auth-card" style="max-width: 560px;">
      <h1>Welcome back 👋</h1>
      <p class="auth-sub">Choose how you'd like to log in.</p>

      <div class="role-picker">
        <a href="student-login.php" class="role-picker-option role-picker-student">
          <i class="ti ti-school"></i>
          <span class="role-picker-title">Student</span>
          <span class="role-picker-desc">Browse events &amp; register for events</span>
        </a>
        <a href="teacher-login.php" class="role-picker-option role-picker-teacher">
          <i class="ti ti-chalkboard"></i>
          <span class="role-picker-title">Teacher</span>
          <span class="role-picker-desc">Create &amp; manage your own events</span>
        </a>
      </div>
      <!-- Admin login is intentionally not listed here. It stays reachable only
           via the direct URL admin-login.php (see the small link in the site
           footer), so it is never advertised to regular users. -->

      <p class="auth-switch">New here? <a href="signup.php">Sign up</a></p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
