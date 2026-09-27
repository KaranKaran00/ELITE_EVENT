<?php
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
    $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));

    $basePath = '/' . ltrim(
        str_replace($docRoot, '', $projectDir),
        '/'
    );

    $basePath = rtrim($basePath, '/') . '/';
?>

<footer class="footer">

    <div class="footer-container">

        <!-- Brand -->
        <div class="footer-brand">
            <div class="footer-logo">Elite Event</div>
            <p>Discover. Create. Connect.</p>
        </div>

        <!-- Navigation -->
        <div class="footer-links">
            <a href="<?= htmlspecialchars($basePath) ?>index.php">Home</a>
            <a href="<?= htmlspecialchars($basePath) ?>events.php">Browse Events</a>
            <a href="<?= htmlspecialchars($basePath) ?>create-event.php">Create Event</a>
            <a href="<?= htmlspecialchars($basePath) ?>login.php">Log in</a>
        </div>

        <!-- Social -->
        <div class="footer-social">

            <a href="https://instagram.com/Czmgbca"
               target="_blank"
               rel="noopener"
               aria-label="Instagram">
                <i class="ti ti-brand-instagram"></i>
            </a>

            <a href="#" aria-label="Facebook">
                <i class="ti ti-brand-facebook"></i>
            </a>

            <a href="#" aria-label="Twitter">
                <i class="ti ti-brand-twitter"></i>
            </a>

            <a href="#" aria-label="LinkedIn">
                <i class="ti ti-brand-linkedin"></i>
            </a>

        </div>

    </div>

    <div class="footer-bottom">
        <span>
            © <?= date('Y') ?> <strong>Elite Event</strong>
        </span>

        <span>
            Made with ❤️ for students
        </span>
    </div>

</footer>

<script src="<?= htmlspecialchars($basePath) ?>js/main.js"></script>

</body>
</html>