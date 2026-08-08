<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('teacher');
$pageTitle = 'Teacher Dashboard — Elite Event';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h1>Teacher Dashboard</h1><p>Create and manage event listings.</p></div></section>
<section class="section"><div class="container">
  <div class="stats-grid"><div class="stat-card"><span class="stat-value"><?= count($events) ?></span><span class="stat-label">Published events</span></div></div>
  <div class="section-head"><h2>Your event tools</h2><a href="../create-event.php" class="btn btn-primary">Create Event</a></div>
  <div class="event-grid">
    <?php foreach ($events as $event): $cat=getCategory($event['category'],$categories); ?>
      <a class="event-card" href="../create-event.php?edit=<?= (int)$event['id'] ?>"><div class="event-card-cover" style="--cover-color:<?= htmlspecialchars($cat['color']) ?>"><span class="event-card-icon"><?= $cat['icon'] ?></span></div><div class="event-card-body"><span class="event-card-date"><?= formatEventDate($event['date']) ?></span><h3><?= htmlspecialchars($event['title']) ?></h3><p class="event-card-venue">✏️ Edit event</p></div></a>
    <?php endforeach; ?>
  </div>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
