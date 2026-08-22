<?php
/* includes/feedback-card.php — renders one Event Feedback Card.
   Include this file with $fb set to a feedback row (from eventFeedback()
   or recentFeedback()), optionally with 'event_title' joined in. */
$fb = $fb ?? null;
if (!$fb) return;

$initials = '';
$parts = explode(' ', trim($fb['user_name']));
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
?>
<div class="feedback-card">
  <?php if (!empty($fb['photo_url'])): ?>
    <div class="feedback-card-photo">
      <img src="<?= htmlspecialchars($fb['photo_url']) ?>" alt="Photo from <?= htmlspecialchars($fb['user_name']) ?>" loading="lazy"
           onerror="this.closest('.feedback-card-photo').style.display='none'">
    </div>
  <?php endif; ?>

  <div class="feedback-card-stars"><?= starRating((int)$fb['rating']) ?></div>

  <p class="feedback-card-quote">&ldquo;<?= nl2br(htmlspecialchars($fb['comment'])) ?>&rdquo;</p>

  <div class="feedback-card-author">
    <span class="feedback-card-avatar"><?= htmlspecialchars($initials ?: 'U') ?></span>
    <div>
      <strong>&mdash; <?= htmlspecialchars($fb['user_name']) ?></strong>
      <?php if (!empty($fb['event_title'])): ?>
        <span class="feedback-card-event"><?= htmlspecialchars($fb['event_title']) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>
