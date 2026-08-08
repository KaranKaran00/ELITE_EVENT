<?php
/* signup.php — public sign up. Only ever creates student or
   teacher accounts (see register_user() in includes/auth.php);
   admin accounts can only be created by an existing admin. */
require_once __DIR__ . '/includes/data.php';

if ($currentUser) {
    header('Location: ' . base_path() . role_home($currentUser['role']));
    exit;
}

$error = '';
$role  = $_POST['role'] ?? 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';

        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $result = register_user($name, $email, $password, $role);
            if ($result === true) {
                header('Location: ' . base_path() . role_home($role));
                exit;
            }
            $error = $result;
        }
    }
}

$pageTitle = 'Sign up — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="container auth-container">
    <div class="auth-card">
      <h1>Create your account 🎉</h1>
      <p class="auth-sub">Sign up as a student or a teacher.</p>

      <?php if ($error): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" action="signup.php" class="auth-form">
        <?= csrf_field() ?>

        <label>I am a...</label>
        <div class="role-toggle">
          <label class="role-toggle-option">
            <input type="radio" name="role" value="student" <?= $role === 'student' ? 'checked' : '' ?>>
            <span><i class="ti ti-school"></i> Student</span>
          </label>
          <label class="role-toggle-option">
            <input type="radio" name="role" value="teacher" <?= $role === 'teacher' ? 'checked' : '' ?>>
            <span><i class="ti ti-chalkboard"></i> Teacher</span>
          </label>
        </div>

        <label for="name">Full name</label>
        <input type="text" id="name" name="name" placeholder="e.g. Arjun Shah"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 8 characters" minlength="8" required>

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" placeholder="••••••••" required>

        <button type="submit" class="btn btn-primary btn-block">Sign up</button>
      </form>

      <p class="auth-note">Need an admin account? Ask an existing admin to create one for you.</p>
      <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
