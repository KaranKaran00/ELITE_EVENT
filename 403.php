<?php
require_once __DIR__ . '/includes/data.php';
$pageTitle = 'Access denied — Elite Event';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="min-height: calc(100vh - 64px); display:flex; align-items:center;">
  <div class="container" style="text-align:center;">
    <div style="font-size:56px;">🚫</div>
    <h1 style="margin-top:12px;">Access denied</h1>
    <p style="color:var(--text-hint); margin-top:8px;">
      You're logged in<?= $currentUser ? ' as <strong>' . htmlspecialchars($currentUser['name']) . '</strong> (' . htmlspecialchars($currentUser['role']) . ')' : '' ?>,
      but this page isn't available for your role.
    </p>
    <a href="<?= base_path() . ($currentUser ? role_home($currentUser['role']) : 'login.php') ?>" class="btn btn-primary" style="margin-top:20px;">
      Go to your dashboard
    </a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
