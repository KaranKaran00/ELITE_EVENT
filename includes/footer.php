<footer class="footer">
  <div class="footer-top">
    <div class="footer-brand">
      <div class="footer-brand-name">Elite Event</div>
      <p class="footer-tagline">Discover. Create. Connect.</p>
    </div>

    <nav class="footer-links">
      <a href="<?= $basePath ?>index.php">Home</a>
      <a href="<?= $basePath ?>events.php">Browse Events</a>
      <a href="<?= $basePath ?>create-event.php">Create Event</a>
      <a href="<?= $basePath ?>login.php">Log in</a>
    </nav>

    <div class="footer-social">
      <!-- TODO: add/remove handles as needed -->
      <a href="https://instagram.com/Czmgbca" target="_blank" rel="noopener" title="Instagram" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
      <a href="#" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
      <a href="#" target="_blank" rel="noopener" title="Twitter / X" aria-label="Twitter / X"><i class="ti ti-brand-twitter"></i></a>
      <a href="#" target="_blank" rel="noopener" title="LinkedIn" aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© <?php echo date('Y'); ?> <strong style="color:var(--gold);">Elite Event</strong>
      <!-- Deliberately unlabeled and low-visibility: direct admin access, not for regular users. -->
      <a href="<?= $basePath ?>admin-login.php" class="footer-admin-link" title="Admin" aria-label="Admin access"><i class="ti ti-lock"></i></a>
    </p>
    <p>Made with ❤️ for students</p>
  </div>
</footer>

<?php
  $docRoot    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
  $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
  $basePath   = '/' . ltrim(str_replace($docRoot, '', $projectDir), '/');
  $basePath   = rtrim($basePath, '/') . '/';
?>
<script src="<?= htmlspecialchars($basePath) ?>js/main.js"></script>
</body>
</html>
