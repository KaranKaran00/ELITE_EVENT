<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('admin');
$pageTitle = 'Event registrations — Elite Event';

$stmt = db()->query('SELECT r.id, r.registered_at, e.id AS event_id, e.title, e.date, e.venue, u.name, u.email FROM registrations r JOIN events e ON e.id=r.event_id JOIN users u ON u.id=r.user_id ORDER BY r.registered_at DESC');
$registrations = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Event registrations</h1><p>One student account can register only once per event.</p></div></section>
<section class="section"><div class="container">
  <div class="admin-summary"><div class="stat-card"><span class="stat-value"><?= count($registrations) ?></span><span class="stat-label">Total registrations</span></div></div>
  <?php if (!$registrations): ?>
    <div class="empty-state"><span class="empty-state-icon">📝</span><h3>No registrations yet</h3><p>Student registrations will appear here.</p></div>
  <?php else: ?>
    <div class="dashboard-table registrations-table">
      <div class="dashboard-row dashboard-row-head"><span>Student</span><span>Event</span><span>Date</span><span>Registered</span><span></span></div>
      <?php foreach ($registrations as $r): ?>
      <div class="dashboard-row">
        <span><strong><?= htmlspecialchars($r['name']) ?></strong><small><?= htmlspecialchars($r['email']) ?></small></span>
        <span><?= htmlspecialchars($r['title']) ?></span>
        <span><?= formatEventDate($r['date']) ?></span>
        <span><?= htmlspecialchars(date('d M Y, g:i A', strtotime($r['registered_at']))) ?></span>
        <span><a class="btn btn-ghost btn-sm" href="../event-detail.php?id=<?= (int)$r['event_id'] ?>">View</a></span>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
