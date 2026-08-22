<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('admin');

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete_feedback') {
            $id = (int) ($_POST['feedback_id'] ?? 0);
            $stmt = db()->prepare('DELETE FROM feedback WHERE id = ?');
            $stmt->execute([$id]);
            $notice = 'Feedback removed.';
        }
    }
}

$allFeedback = db()->query(
    'SELECT f.*, u.name AS user_name, u.email AS user_email, e.title AS event_title, e.id AS event_id
     FROM feedback f JOIN users u ON u.id = f.user_id JOIN events e ON e.id = f.event_id
     ORDER BY f.created_at DESC'
)->fetchAll();

$avgRating = 0;
if ($allFeedback) {
    $avgRating = round(array_sum(array_column($allFeedback, 'rating')) / count($allFeedback), 1);
}

$pageTitle = 'Event Feedback — Elite Event';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1>Event feedback</h1>
    <p>Review and moderate feedback left by students who attended events.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ($notice): ?><div class="form-success">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="admin-summary" style="margin-bottom:28px;">
      <div class="stat-card"><span class="stat-value"><?= count($allFeedback) ?></span><span class="stat-label">Total reviews</span></div>
      <div class="stat-card"><span class="stat-value"><?= $allFeedback ? $avgRating . ' ⭐' : '—' ?></span><span class="stat-label">Average rating</span></div>
    </div>

    <?php if (!$allFeedback): ?>
      <div class="empty-state"><span class="empty-state-icon">💬</span><h3>No feedback yet</h3><p>Feedback submitted by attendees will appear here.</p></div>
    <?php else: ?>
      <div class="dashboard-table">
        <div class="dashboard-row dashboard-row-head">
          <span>Student</span>
          <span>Event</span>
          <span>Rating</span>
          <span>Comment</span>
          <span></span>
        </div>
        <?php foreach ($allFeedback as $fb): ?>
          <div class="dashboard-row">
            <span><strong><?= htmlspecialchars($fb['user_name']) ?></strong><small><?= htmlspecialchars($fb['user_email']) ?></small></span>
            <span><a href="../event-detail.php?id=<?= (int)$fb['event_id'] ?>"><?= htmlspecialchars($fb['event_title']) ?></a></span>
            <span><?= starRating((int)$fb['rating']) ?></span>
            <span style="overflow-wrap:anywhere;"><?= htmlspecialchars(mb_strimwidth($fb['comment'], 0, 120, '…')) ?></span>
            <span class="dashboard-row-actions">
              <form method="post" class="inline-form" onsubmit="return confirm('Remove this feedback?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_feedback">
                <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);">Remove</button>
              </form>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
