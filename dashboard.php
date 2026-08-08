<?php
/* dashboard.php — the STUDENT dashboard. Teachers and admins have
   their own dashboards (teacher/dashboard.php, admin/dashboard.php)
   with permissions matched to their role. */
require_once __DIR__ . '/includes/data.php';
$currentUser = require_role('student'); // teachers/admins get bounced to 403.php

$pageTitle = 'Dashboard — Elite Event';
require_once __DIR__ . '/includes/header.php';

$registered = userRegistrations((int)$currentUser['id']);
$upcoming = array_slice($events, 0, 3);
$stats = [
    ['label' => 'Events registered', 'value' => count($registered)],
    ['label' => 'Available events',  'value' => count($events)],
];
?>

<section class="page-head">
  <div class="container dashboard-head">
    <div>
      <h1>Welcome back, <?= htmlspecialchars($currentUser['name']) ?> 👋</h1>
      <p>Here's what's happening for you.</p>
    </div>
    <a href="events.php" class="btn btn-primary"><i class="ti ti-search"></i> Browse events</a>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="stats-grid">
      <?php foreach ($stats as $s): ?>
        <div class="stat-card">
          <span class="stat-value"><?= htmlspecialchars($s['value']) ?></span>
          <span class="stat-label"><?= htmlspecialchars($s['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section-head">
      <h2>Recommended for you</h2>
      <a href="events.php" class="see-all">See all events →</a>
    </div>

    <div class="dashboard-table">
      <div class="dashboard-row dashboard-row-head">
        <span>Event</span>
        <span>Date</span>
        <span>Status</span>
        <span></span>
      </div>
      <?php foreach ($upcoming as $event):
        $cat = getCategory($event['category'], $categories);
      ?>
        <div class="dashboard-row">
          <span class="dashboard-event-name">
            <span class="dashboard-event-icon" style="--cover-color: <?= htmlspecialchars($cat['color']) ?>"><?= $cat['icon'] ?></span>
            <?= htmlspecialchars($event['title']) ?>
          </span>
          <span><?= formatEventDate($event['date']) ?></span>
          <span><span class="status-badge status-live">Open</span></span>
          <span class="dashboard-row-actions">
            <a href="event-detail.php?id=<?= $event['id'] ?>" class="btn btn-ghost btn-sm">View</a>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section-head dashboard-registered-head">
      <h2>Your registrations</h2>
      <a href="events.php" class="see-all">Find more events →</a>
    </div>
    <?php if (empty($registered)): ?>
      <div class="empty-state compact-empty"><span class="empty-state-icon">📝</span><h3>No registrations yet</h3><p>Open an event and register with your account.</p></div>
    <?php else: ?>
      <div class="registered-list">
        <?php foreach ($registered as $registration): $rcat = getCategory($registration['category'], $categories); ?>
          <div class="registered-item">
            <span class="dashboard-event-icon" style="--cover-color: <?= htmlspecialchars($rcat['color']) ?>"><?= $rcat['icon'] ?></span>
            <div class="registered-item-main"><strong><?= htmlspecialchars($registration['title']) ?></strong><span><?= formatEventDate($registration['date']) ?> · <?= htmlspecialchars($registration['venue']) ?></span></div>
            <a href="event-detail.php?id=<?= (int)$registration['event_id'] ?>" class="btn btn-ghost btn-sm">View</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
