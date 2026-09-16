<?php
$pageTitle = 'Elite Event — Find things to do';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/header.php';

$trending = array_slice($events, 0, 6);

// Instagram posts are managed by admins from admin/instagram.php and
// stored in the database, so this list updates the moment an admin
// adds or removes a post — no code changes needed.
$instagramPosts = db()->query('SELECT * FROM instagram_posts ORDER BY id DESC LIMIT 6')->fetchAll();
?>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-inner">
    <h1>Find your next<br><strong>unforgettable<br>thing to do</strong></h1>
    <p class="hero-sub">Concerts, workshops, markets and meetups —<br>happening near you this week.</p>

    <form action="events.php" method="get" class="hero-search">
      <div class="hero-search-field">
        <span class="field-icon">🔍</span>
        <input type="text" name="q" placeholder="Search events, organizers, venues...">
      </div>
      <div class="hero-search-field hero-search-select">
        <span class="field-icon">📂</span>
        <select name="category">
          <option value="all">All categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary hero-search-btn">Search</button>
    </form>

    <div class="hero-trending">
      <span>Trending:</span>
      <a href="events.php?q=music">Music</a>
      <a href="events.php?category=workshop">Workshops</a>
      <a href="events.php?category=tech">Tech events</a>
      <a href="events.php?category=food">Food &amp; drink</a>
    </div>
  </div>
</section>

<!-- ── BROWSE BY CATEGORY ── -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <h2>Browse by category</h2>
      <a href="events.php" class="see-all">See all →</a>
    </div>
    <div class="category-tiles">
      <?php foreach ($categories as $cat): ?>
        <a href="events.php?category=<?= htmlspecialchars($cat['slug']) ?>"
           class="category-tile"
           style="--tile-color: <?= htmlspecialchars($cat['color']) ?>">
          <span class="category-tile-icon"><?= $cat['icon'] ?></span>
          <span class="category-tile-name"><?= htmlspecialchars($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── MAKING MOMENTS MEMORABLE (matching image section) ── -->
<section class="section" style="background: var(--warm-white);">
  <div class="container">
    <div class="about-split">
      <div>
        <h2>Making<br>Moments<br><em>Memorable</em></h2>
        <p>Elite Event is your go-to place to discover amazing events, connect with like-minded people, and create lasting memories.</p>
        <?php if (!$currentUser): ?>
          <a href="signup.php" class="btn btn-primary">Create an account</a>
        <?php else: ?>
          <a href="create-event.php" class="btn btn-primary">Create an event</a>
        <?php endif; ?>
      </div>
      <div class="about-image">🎉</div>
    </div>

    <!-- Stats bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-icon">📅</span>
        <div>
          <div class="stat-number">10,000+</div>
          <div class="stat-label">Events every month</div>
        </div>
      </div>
      <div class="stat-item">
        <span class="stat-icon">👥</span>
        <div>
          <div class="stat-number">50,000+</div>
          <div class="stat-label">Happy attendees</div>
        </div>
      </div>
      <div class="stat-item">
        <span class="stat-icon">📍</span>
        <div>
          <div class="stat-number">200+</div>
          <div class="stat-label">Cities worldwide</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── TRENDING EVENTS ── -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head">
      <h2>Trending this week</h2>
      <a href="events.php" class="see-all">See all events →</a>
    </div>
    <div class="event-grid">
      <?php foreach ($trending as $event):
        $cat = getCategory($event['category'], $categories);
      ?>
        <a href="event-detail.php?id=<?= $event['id'] ?>" class="event-card">
          <div class="event-card-cover" style="--cover-color: <?= htmlspecialchars($cat['color']) ?>">
            <span class="event-card-icon"><?= $cat['icon'] ?></span>
          </div>
          <div class="event-card-body">
            <span class="event-card-date"><?= formatEventDate($event['date']) ?></span>
            <h3><?= htmlspecialchars($event['title']) ?></h3>
            <p class="event-card-venue">📍 <?= htmlspecialchars($event['venue']) ?>, <?= htmlspecialchars($event['city']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA BANNER ── -->
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <div>
      <h2>Have an event of your own?</h2>
      <p>List it on Elite Event in a few minutes — free for community events.</p>
    </div>
    <a href="create-event.php" class="btn btn-light">Create an event</a>
  </div>
</section>

<!-- ── INSTAGRAM SECTION ── -->
<section class="section instagram-section">
  <div class="container">
    <div class="section-head">
      <h2>Follow Us On Instagram</h2>
      <a href="https://instagram.com/Czmgbca" target="_blank" class="see-all">View Profile →</a>
    </div>
    <?php if ($instagramPosts): ?>
      <div class="instagram-grid">
        <?php foreach ($instagramPosts as $p): ?>
          <div class="instagram-grid-item">
            <blockquote class="instagram-media"
              data-instgrm-permalink="<?= htmlspecialchars($p['url']) ?>"
              data-instgrm-version="14"></blockquote>
            <?php if ($p['caption']): ?>
              <p class="instagram-caption"><?= htmlspecialchars($p['caption']) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:var(--text-hint);">No Instagram posts yet — check back soon!</p>
    <?php endif; ?>
  </div>
</section>
<script async src="//www.instagram.com/embed.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
