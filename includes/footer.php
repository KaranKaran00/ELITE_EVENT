<footer class="footer">
  <p>© <?php echo date('Y'); ?> <strong style="color:var(--gold);">Elite Event</strong> — College Event Management System &nbsp;·&nbsp;
  Made with ❤️ for students &nbsp;·&nbsp;
  <a href="<?= $basePath ?>index.php">Home</a> &nbsp;·&nbsp;
  <a href="<?= $basePath ?>events.php">Browse Events</a> &nbsp;·&nbsp;
  <a href="<?= $basePath ?>create-event.php">Create Event</a>
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
