<?php
require_once __DIR__ . '/includes/auth.php';
$dest = base_path() . 'index.php';
logout_user();
header('Location: ' . $dest);
exit;
