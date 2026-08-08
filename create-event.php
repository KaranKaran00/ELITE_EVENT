<?php
require_once __DIR__ . '/includes/data.php';
$currentUser = require_role('teacher', 'admin');

$pageTitle = 'Create event — Elite Event';

$editEvent = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editEvent = $stmt->fetch() ?: null;
}

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$values = $submitted ? $_POST : ($editEvent ?? []);
$error = '';
$success = '';

if ($submitted) {
    if (!csrf_verify()) {
        $error = 'Your form session expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $googleFormUrl = trim($_POST['google_form_url'] ?? '');

        if ($title === '' || $category === '' || $date === '' || $time === '' || $venue === '' || $city === '') {
            $error = 'Please fill in all required fields.';
        } elseif ($googleFormUrl !== '') {
            $host = strtolower((string)parse_url($googleFormUrl, PHP_URL_HOST));
            if ($host !== 'docs.google.com' || strpos($googleFormUrl, '/forms/') === false) {
                $error = 'Please enter a valid Google Forms URL from docs.google.com/forms/. The field can also be left blank.';
            }
        }

        if ($error === '') {
            if ($editEvent) {
                $stmt = db()->prepare('UPDATE events SET title=?, category=?, date=?, time=?, venue=?, city=?, description=?, google_form_url=?, updated_at=datetime(\'now\') WHERE id=?');
                $stmt->execute([$title,$category,$date,$time,$venue,$city,$description,$googleFormUrl !== '' ? $googleFormUrl : null,(int)$editEvent['id']]);
                $success = 'Event updated successfully.';
                $editEvent = array_merge($editEvent, compact('title','category','date','time','venue','city','description'), ['google_form_url'=>$googleFormUrl]);
            } else {
                $stmt = db()->prepare('INSERT INTO events (title, category, date, time, venue, city, organizer, description, google_form_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title,$category,$date,$time,$venue,$city,$currentUser['name'],$description,$googleFormUrl !== '' ? $googleFormUrl : null,$currentUser['id']]);
                $success = 'Event created successfully.';
                $values = [];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1><?= $editEvent ? 'Edit event' : 'Create an event' ?></h1>
    <p>Add your event details and optionally connect a Google Form for registration.</p>
  </div>
</section>

<section class="section">
  <div class="container form-container">
    <?php if ($error): ?><div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="form-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="event-form">
      <form method="post" action="create-event.php<?= $editEvent ? '?edit='.(int)$editEvent['id'] : '' ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="form-field form-field-full">
            <label for="title">Event title *</label>
            <input type="text" id="title" name="title" placeholder="e.g. College Tech Workshop" value="<?= htmlspecialchars($values['title'] ?? '') ?>" required>
          </div>

          <div class="form-field">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
              <option value="">Choose a category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= (($values['category'] ?? '') === $cat['slug']) ? 'selected' : '' ?>><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-field">
            <label for="date">Date *</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($values['date'] ?? '') ?>" required>
          </div>

          <div class="form-field">
            <label for="time">Time *</label>
            <input type="time" id="time" name="time" value="<?= htmlspecialchars($values['time'] ?? '') ?>" required>
          </div>

          <div class="form-field">
            <label for="venue">Venue *</label>
            <input type="text" id="venue" name="venue" placeholder="e.g. Main Auditorium" value="<?= htmlspecialchars($values['venue'] ?? '') ?>" required>
          </div>

          <div class="form-field">
            <label for="city">City *</label>
            <input type="text" id="city" name="city" placeholder="e.g. Ahmedabad" value="<?= htmlspecialchars($values['city'] ?? '') ?>" required>
          </div>

          <div class="form-field form-field-full">
            <label for="google_form_url">Google Form registration URL</label>
            <input type="url" id="google_form_url" name="google_form_url" placeholder="https://docs.google.com/forms/d/e/.../viewform" value="<?= htmlspecialchars($values['google_form_url'] ?? '') ?>">
            <small class="form-help">Create a Google Form, click <strong>Send → Embed</strong> or copy its form URL, then paste it here. Leave blank if registration is not ready.</small>
          </div>

          <div class="form-field form-field-full">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" placeholder="Tell people what to expect at your event..."><?= htmlspecialchars($values['description'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> <?= $editEvent ? 'Save changes' : 'Create event' ?></button>
          <a href="events.php" class="btn btn-ghost">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
