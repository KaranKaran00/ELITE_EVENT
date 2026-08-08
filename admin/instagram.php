<?php
require_once __DIR__ . '/../includes/data.php';
$currentUser = require_role('admin');

$notice = '';
$error  = '';

function is_valid_instagram_url(string $url): bool {
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) return false;
    $host = strtolower($parts['host']);
    return $host === 'instagram.com' || str_ends_with($host, '.instagram.com');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_post') {
            $url     = trim($_POST['url'] ?? '');
            $caption = trim($_POST['caption'] ?? '');

            if ($url === '') {
                $error = 'Paste an Instagram post link.';
            } elseif (!is_valid_instagram_url($url)) {
                $error = 'That doesn\'t look like a valid instagram.com link.';
            } else {
                $stmt = db()->prepare('INSERT INTO instagram_posts (url, caption, added_by) VALUES (?, ?, ?)');
                $stmt->execute([$url, $caption, $currentUser['id']]);
                $notice = 'Instagram post added.';
            }

        } elseif ($action === 'delete_post') {
            $id = (int) ($_POST['post_id'] ?? 0);
            $stmt = db()->prepare('DELETE FROM instagram_posts WHERE id = ?');
            $stmt->execute([$id]);
            $notice = 'Instagram post removed.';
        }
    }
}

$posts = db()->query('SELECT ip.*, u.name AS added_by_name FROM instagram_posts ip LEFT JOIN users u ON u.id = ip.added_by ORDER BY ip.id DESC')->fetchAll();

$pageTitle = 'Instagram Posts — Elite Event';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1>Instagram posts</h1>
    <p>Add Instagram post links to feature them on the Elite Event homepage.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ($notice): ?><div class="form-success">✅ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="admin-panel" style="margin-bottom:28px;">
      <h2>Add a new post</h2>
      <form method="post" class="event-form" style="margin-top:12px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_post">
        <div class="form-grid">
          <div class="form-field form-field-full">
            <label>Instagram post URL</label>
            <input type="url" name="url" placeholder="https://www.instagram.com/p/XXXXXXXXXXX/" required>
          </div>
          <div class="form-field form-field-full">
            <label>Caption (optional)</label>
            <input type="text" name="caption" placeholder="Short note shown above the post">
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:8px;">Add post</button>
      </form>
    </div>

    <div class="section-head">
      <h2>Featured posts (<?= count($posts) ?>)</h2>
      <a href="<?= base_path() ?>index.php" class="see-all">View on homepage →</a>
    </div>

    <div class="instagram-admin-grid">
      <?php foreach ($posts as $p): ?>
        <div class="instagram-admin-card">
          <blockquote class="instagram-media" data-instgrm-permalink="<?= htmlspecialchars($p['url']) ?>" data-instgrm-version="14"></blockquote>
          <?php if ($p['caption']): ?><p class="instagram-caption"><?= htmlspecialchars($p['caption']) ?></p><?php endif; ?>
          <div class="instagram-meta">
            <span>Added by <?= htmlspecialchars($p['added_by_name'] ?? 'Unknown') ?> · <?= htmlspecialchars($p['created_at']) ?></span>
            <form method="post" onsubmit="return confirm('Remove this Instagram post?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_post">
              <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);">Remove</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$posts): ?>
        <p style="color:var(--text-hint);">No Instagram posts yet — add one above.</p>
      <?php endif; ?>
    </div>

  </div>
</section>

<script async src="//www.instagram.com/embed.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
