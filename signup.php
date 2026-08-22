<?php
/* signup.php — public self-registration is disabled. Accounts (student,
   teacher, or admin) can only be created by an existing admin from
   admin/users.php. This page just explains that and points people
   to log in or contact an admin. */
require_once __DIR__ . '/includes/data.php';

if ($currentUser) {
    header('Location: ' . base_path() . role_home($currentUser['role']));
    exit;
}

$pageTitle = 'Sign up — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="container auth-container">
    <div class="auth-card">
      <h1>Accounts are admin-only 🔒</h1>
      <p class="auth-sub">Public sign-up has been turned off. An administrator creates every student, teacher, and admin account for Elite Event.</p>

      <div class="no-form-box" style="margin-bottom:18px;">
        Already have login details? Head to the log in page below.<br>
        Need an account? Ask an existing admin to create one for you from the Manage Users panel.
      </div>

      <a href="login.php" class="btn btn-primary btn-block">Log in</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
