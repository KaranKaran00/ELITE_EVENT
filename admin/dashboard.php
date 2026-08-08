<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('admin');
$pageTitle = 'Admin Panel — Elite Event';
$userCount = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$registrationCount = (int)db()->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Admin Panel</h1><p>Manage events, users and registrations.</p></div></section>
<section class="section"><div class="container">
  <div class="stats-grid">
    <div class="stat-card"><span class="stat-value"><?= count($events) ?></span><span class="stat-label">Events</span></div>
    <div class="stat-card"><span class="stat-value"><?= $userCount ?></span><span class="stat-label">Users</span></div>
    <div class="stat-card"><span class="stat-value"><?= $registrationCount ?></span><span class="stat-label">Registrations</span></div>
  </div>
  <div class="admin-actions-grid">
    <a href="../create-event.php" class="admin-action-card"><span>➕</span><strong>Create Event</strong><small>Add an event and Google Form URL.</small></a>
    <a href="registrations.php" class="admin-action-card"><span>📝</span><strong>Registrations</strong><small>See who registered for each event.</small></a>
    <a href="users.php" class="admin-action-card"><span>👥</span><strong>Manage Users</strong><small>View and manage user accounts.</small></a>
  </div>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
