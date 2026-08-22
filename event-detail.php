<?php
require_once __DIR__ . '/includes/data.php';

$id = (int)($_GET['id'] ?? 0);
$event = getEventById($events, $id);

if (!$event) {
    $pageTitle = 'Event not found — Elite Event';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><span class="empty-state-icon">📅</span><h3>Event not found</h3><p>The event link is incorrect or the event was removed.</p><a href="events.php" class="btn btn-primary">Browse events</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$registrationMessage = '';
$registrationError = '';
$isRegistered = $currentUser ? userRegisteredForEvent((int)$currentUser['id'], $id) : false;

$feedbackMessage = '';
$feedbackError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'feedback') {
    if (!$currentUser || $currentUser['role'] !== 'student') {
        $feedbackError = 'Only student accounts can leave feedback.';
    } elseif (!csrf_verify()) {
        $feedbackError = 'Your form session expired. Please refresh the page and try again.';
    } else {
        $result = submitFeedback(
            (int)$currentUser['id'],
            $id,
            (int)($_POST['rating'] ?? 0),
            $_POST['comment'] ?? '',
            $_POST['photo_url'] ?? ''
        );
        if ($result === true) {
            $feedbackMessage = 'Thanks for sharing your feedback!';
        } else {
            $feedbackError = $result;
        }
    }
}

$myFeedback = $currentUser ? userFeedbackForEvent((int)$currentUser['id'], $id) : null;
$eventFeedback = eventFeedback($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    if (!$currentUser) {
        $registrationError = 'Please log in with your account before registering.';
    } elseif ($currentUser['role'] !== 'student') {
        $registrationError = 'Only student accounts can register for events.';
    } elseif (!csrf_verify()) {
        $registrationError = 'Your form session expired. Please refresh the page and try again.';
    } elseif (!$event['google_form_url']) {
        $registrationError = 'Registration form is not available for this event yet.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO registrations (event_id, user_id) VALUES (?, ?)');
            $stmt->execute([$id, (int)$currentUser['id']]);
            $isRegistered = true;
            $registrationMessage = 'You are registered for this event. Complete the Google Form below to submit your event details.';
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000' || (string)$e->errorInfo[1] === '1062' || stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), 'unique') !== false) {
                $isRegistered = true;
                $registrationMessage = 'You have already registered for this event. You can complete or review the Google Form below.';
            } else {
                $registrationError = 'Registration could not be completed. Please try again.';
            }
        }
    }
}

$cat = getCategory($event['category'], $categories);
$related = array_values(array_filter($events, fn($e) => $e['category'] === $event['category'] && (int)$e['id'] !== $id));
$related = array_slice($related, 0, 3);
$pageTitle = htmlspecialchars($event['title']) . ' — Elite Event';
require_once __DIR__ . '/includes/header.php';

$formUrl = $event['google_form_url'];
if ($formUrl && !str_contains($formUrl, 'embedded=true')) $formUrl .= (str_contains($formUrl, '?') ? '&' : '?') . 'embedded=true';
?>

<section class="event-banner" style="--cover-color: <?= htmlspecialchars($cat['color']) ?>">
  <div class="container event-banner-inner"><span class="event-banner-icon"><?= $cat['icon'] ?></span></div>
</section>

<section class="section">
  <div class="container event-detail-layout">
    <div class="event-detail-main">
      <span class="event-tag" style="--tag-color: <?= htmlspecialchars($cat['color']) ?>"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></span>
      <h1><?= htmlspecialchars($event['title']) ?></h1>

      <div class="event-meta-list">
        <div class="event-meta-item"><span class="event-meta-icon">📅</span><div><strong><?= formatEventDate($event['date']) ?></strong><div class="event-meta-sub"><?= formatEventTime($event['time']) ?></div></div></div>
        <div class="event-meta-item"><span class="event-meta-icon">📍</span><div><strong><?= htmlspecialchars($event['venue']) ?></strong><div class="event-meta-sub"><?= htmlspecialchars($event['city']) ?></div></div></div>
        <div class="event-meta-item"><span class="event-meta-icon">🏷️</span><div><strong>Hosted by</strong><div class="event-meta-sub"><?= htmlspecialchars($event['organizer']) ?></div></div></div>
      </div>

      <h2 class="event-section-title">About this event</h2>
      <p class="event-description"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
    </div>

    <aside class="registration-panel">
      <div class="registration-card">
        <span class="registration-icon">📝</span>
        <h3>Event registration</h3>
        <p>One account can register <strong>once</strong> for this event.</p>

        <?php if ($registrationMessage): ?><div class="form-success registration-alert">✅ <?= htmlspecialchars($registrationMessage) ?></div><?php endif; ?>
        <?php if ($registrationError): ?><div class="form-error registration-alert">⚠️ <?= htmlspecialchars($registrationError) ?></div><?php endif; ?>

        <?php if (!$currentUser): ?>
          <a href="login.php" class="btn btn-primary btn-block">Log in to register</a>
          <p class="registration-note">You need a student account to register.</p>
        <?php elseif ($currentUser['role'] !== 'student'): ?>
          <div class="no-form-box">Please use a student account to register for this event.</div>
        <?php elseif ($isRegistered): ?>
          <div class="registered-badge">✓ Already registered</div>
          <?php if ($formUrl): ?><a href="#google-registration" class="btn btn-primary btn-block">Open registration form</a><?php endif; ?>
        <?php elseif (!$formUrl): ?>
          <div class="no-form-box">Registration is not available yet. The organizer has not connected a Google Form.</div>
        <?php else: ?>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="register">
            <button type="submit" class="btn btn-primary btn-block">Register with my account</button>
          </form>
          <p class="registration-note">After registration, the Google Form will appear below.</p>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</section>

<?php if ($currentUser && $isRegistered && $formUrl): ?>
<section class="section section-soft" id="google-registration">
  <div class="container google-registration-section">
    <div class="section-head registration-form-head">
      <div><h2>Complete event registration</h2><p>Your Elite Event account is already registered once. Submit the organizer's Google Form below.</p></div>
      <span class="google-form-badge">Google Form</span>
    </div>
    <div class="google-form-wrapper">
      <iframe src="<?= htmlspecialchars($formUrl) ?>" title="Google Form registration for <?= htmlspecialchars($event['title']) ?>" loading="lazy">Loading Google Form…</iframe>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="feedback">
  <div class="container">
    <div class="section-head feedback-section-head">
      <h2>Attendee feedback</h2>
      <?php if ($eventFeedback): ?><span class="see-all"><?= count($eventFeedback) ?> review<?= count($eventFeedback) === 1 ? '' : 's' ?></span><?php endif; ?>
    </div>

    <?php if ($eventFeedback): ?>
      <div class="feedback-grid">
        <?php foreach ($eventFeedback as $fb): include __DIR__ . '/includes/feedback-card.php'; endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:var(--text-hint);">No feedback yet — be the first to share how it went!</p>
    <?php endif; ?>

    <?php if ($feedbackMessage): ?><div class="form-success registration-alert" style="margin-top:20px;">✅ <?= htmlspecialchars($feedbackMessage) ?></div><?php endif; ?>
    <?php if ($feedbackError): ?><div class="form-error registration-alert" style="margin-top:20px;">⚠️ <?= htmlspecialchars($feedbackError) ?></div><?php endif; ?>

    <?php if ($currentUser && $currentUser['role'] === 'student' && $isRegistered): ?>
      <div class="feedback-form-card">
        <h3><?= $myFeedback ? 'Update your feedback' : 'Leave your feedback' ?></h3>
        <p class="form-help">Only attendees who registered for this event can leave feedback.</p>
        <form method="post" class="event-form" style="border:none; box-shadow:none; padding:0; margin-top:14px;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="feedback">

          <div class="form-field" style="margin-bottom:16px;">
            <label>Your rating</label>
            <div class="star-select">
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= ($myFeedback['rating'] ?? 0) == $i ? 'checked' : '' ?> required>
                <label for="star<?= $i ?>">★</label>
              <?php endfor; ?>
            </div>
          </div>

          <div class="form-field" style="margin-bottom:16px;">
            <label for="comment">Your comment</label>
            <textarea id="comment" name="comment" rows="3" placeholder="Amazing event! Had a great experience and met so many people." required><?= htmlspecialchars($myFeedback['comment'] ?? '') ?></textarea>
          </div>

          <div class="form-field" style="margin-bottom:16px;">
            <label for="photo_url">Photo link (optional)</label>
            <input type="url" id="photo_url" name="photo_url" placeholder="https://..." value="<?= htmlspecialchars($myFeedback['photo_url'] ?? '') ?>">
          </div>

          <button type="submit" class="btn btn-primary"><?= $myFeedback ? 'Update feedback' : 'Submit feedback' ?></button>
        </form>
      </div>
    <?php elseif ($currentUser && $currentUser['role'] === 'student' && !$isRegistered): ?>
      <div class="feedback-already" style="background:#fff7e8; color:#765313;">Register for this event to leave feedback once you've attended.</div>
    <?php elseif (!$currentUser): ?>
      <div class="feedback-already" style="background:#fff7e8; color:#765313;"><a href="login.php">Log in</a> as a student to leave feedback.</div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section section-soft">
  <div class="container">
    <div class="section-head"><h2>More <?= htmlspecialchars($cat['name']) ?> events</h2><a href="events.php?category=<?= urlencode($cat['slug']) ?>" class="see-all">See all →</a></div>
    <div class="event-grid">
      <?php foreach ($related as $re): $rcat = getCategory($re['category'], $categories); ?>
        <a href="event-detail.php?id=<?= (int)$re['id'] ?>" class="event-card">
          <div class="event-card-cover" style="--cover-color: <?= htmlspecialchars($rcat['color']) ?>"><span class="event-card-icon"><?= $rcat['icon'] ?></span></div>
          <div class="event-card-body"><span class="event-card-date"><?= formatEventDate($re['date']) ?></span><h3><?= htmlspecialchars($re['title']) ?></h3><p class="event-card-venue">📍 <?= htmlspecialchars($re['venue']) ?>, <?= htmlspecialchars($re['city']) ?></p></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
