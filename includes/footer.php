<footer class="footer">
  <p class="footer-links">
    <a href="<?= $basePath ?>index.php">Home</a> ·
    <a href="<?= $basePath ?>events.php">Browse Events</a> ·
    <a href="<?= $basePath ?>create-event.php">Create Event</a> ·
    <a href="<?= $basePath ?>login.php">Log in</a>
  </p>

  <p class="footer-contact">
    <!-- TODO: swap in the institute's real contact details -->
    <a href="mailto:contact@eliteevent.local"><i class="ti ti-mail"></i> contact@eliteevent.local</a> ·
    <a href="tel:+910000000000"><i class="ti ti-phone"></i> +91 00000 00000</a>
  </p>

  <p class="footer-social">
    <!-- TODO: add/remove handles as needed -->
    <a href="https://instagram.com/Czmgbca" target="_blank" rel="noopener" title="Instagram" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
    <a href="#" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
    <a href="#" target="_blank" rel="noopener" title="Twitter / X" aria-label="Twitter / X"><i class="ti ti-brand-twitter"></i></a>
    <a href="#" target="_blank" rel="noopener" title="LinkedIn" aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>
  </p>

  <p class="footer-bottom">
    © <?php echo date('Y'); ?> <strong style="color:var(--gold);">Elite Event</strong> — All rights reserved. Made with ❤️ for students.
    <!-- Deliberately unlabeled and low-visibility: direct admin access, not for regular users. -->
    <a href="<?= $basePath ?>admin-login.php" class="footer-admin-link" title="Admin" aria-label="Admin access"><i class="ti ti-lock"></i></a>
  </p>
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
