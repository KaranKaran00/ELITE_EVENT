<?php
/* includes/header.php — shared header for all pages.
   $currentUser is already set by includes/data.php (real DB user,
   or null if logged out) — every page requires data.php first. */
$currentUser = $currentUser ?? null;
$initials = '';
if ($currentUser) {
    $parts = explode(' ', trim($currentUser['name']));
    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
$roleHome = $currentUser ? base_path() . role_home($currentUser['role']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($pageTitle ?? 'Elite Event'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css" />
  <?php
    // Detect the web root path of this project reliably
    // Works whether folder is at localhost/elite_event/ or any depth
    $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
    $docRoot    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
    $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $basePath   = '/' . ltrim(str_replace($docRoot, '', $projectDir), '/');
    $basePath   = rtrim($basePath, '/') . '/';
  ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>css/style.css" />
</head>
<body>

<nav class="nav">
  <!-- Logo matching the circular design in the image -->
  <a class="nav-logo" href="<?= $basePath ?>index.php">
    <div class="nav-logo-badge">
      <span class="logo-elite">Elite</span>
      <span class="logo-event">EVENT</span>
    </div>
  </a>

  <div class="nav-links">
    <a class="nav-link" href="<?= $basePath ?>index.php"><i class="ti ti-home"></i> Home</a>
    <a class="nav-link" href="<?= $basePath ?>events.php"><i class="ti ti-calendar-event"></i> Find Events</a>

    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
      <a class="nav-link" href="<?= $basePath ?>admin/dashboard.php"><i class="ti ti-shield-lock"></i> Admin Panel</a>
      <a class="nav-link" href="<?= $basePath ?>admin/users.php"><i class="ti ti-users"></i> Manage Users</a>
      <a class="nav-link" href="<?= $basePath ?>admin/registrations.php"><i class="ti ti-clipboard-check"></i> Registrations</a>
      <a class="nav-link" href="<?= $basePath ?>create-event.php"><i class="ti ti-circle-plus"></i> Create Event</a>
      <a class="nav-link" href="<?= $basePath ?>admin/instagram.php"><i class="ti ti-brand-instagram"></i> Instagram</a>

    <?php elseif ($currentUser && $currentUser['role'] === 'teacher'): ?>
      <a class="nav-link" href="<?= $basePath ?>teacher/dashboard.php"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
      <a class="nav-link" href="<?= $basePath ?>create-event.php"><i class="ti ti-circle-plus"></i> Create Event</a>

    <?php elseif ($currentUser && $currentUser['role'] === 'student'): ?>
      <a class="nav-link" href="<?= $basePath ?>dashboard.php"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
    <?php endif; ?>
  </div>

  <div class="nav-right">
    <?php if ($currentUser): ?>
      <div class="nav-user">
        <div class="nav-avatar"><?php echo $initials ?: 'U'; ?></div>
        <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
        <span class="role-pill role-pill-<?= htmlspecialchars($currentUser['role']) ?>"><?= htmlspecialchars(ucfirst($currentUser['role'])) ?></span>
      </div>
      <a href="<?= $roleHome ?>" class="btn btn-ghost btn-sm">Dashboard</a>
      <a href="<?= $basePath ?>logout.php" class="btn btn-ghost btn-sm">Log out</a>
    <?php else: ?>
      <a href="<?= $basePath ?>login.php"  class="btn btn-ghost btn-sm">Log in</a>
      <a href="<?= $basePath ?>signup.php" class="btn btn-primary btn-sm">Sign up</a>
    <?php endif; ?>
  </div>
</nav>
