<?php
/* ============================================================
   Elite Event — includes/auth.php
   Authentication + role-based access control helpers.
   ============================================================ */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- CSRF ---------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/* ---------- Current user ---------- */

/**
 * Returns the logged-in user's row (id, name, email, role, status),
 * or null if nobody is logged in. Re-checks the DB each call so a
 * suspended/deleted account is booted out immediately.
 */
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;

    static $cached = null;
    if ($cached !== null) return $cached;

    $stmt = db()->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        // Account was removed/suspended since login — force logout.
        session_unset();
        session_destroy();
        return null;
    }

    $cached = $user;
    return $user;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function has_role(string ...$roles): bool {
    $user = current_user();
    return $user && in_array($user['role'], $roles, true);
}

/* ---------- Guards (call at top of a page, before any output) ---------- */

function require_login(string $redirectTo = 'login.php'): array {
    $user = current_user();
    if (!$user) {
        header('Location: ' . base_path() . $redirectTo);
        exit;
    }
    return $user;
}

/**
 * Require the current user to hold one of the given roles.
 * Logged-out users go to login; logged-in users with the wrong
 * role are sent to a 403 page rather than silently redirected,
 * so it's obvious access was denied rather than "just not found".
 */
function require_role(string ...$roles): array {
    $user = current_user();
    if (!$user) {
        header('Location: ' . base_path() . 'login.php');
        exit;
    }
    if (!in_array($user['role'], $roles, true)) {
        header('Location: ' . base_path() . '403.php');
        exit;
    }
    return $user;
}

/* ---------- Login / logout ---------- */

/**
 * Attempts to log in a user, restricted to a specific expected role
 * (so the Student form can't be used to log a teacher/admin in, etc).
 * Returns true on success, or a string error message on failure.
 */
function attempt_login(string $email, string $password, string $expectedRole) {
    $email = trim($email);
    if ($email === '' || $password === '') {
        return 'Enter your email and password.';
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Same generic message whether the email doesn't exist, the
    // password is wrong, or the role doesn't match — this avoids
    // leaking which emails are registered or what role they hold.
    $genericError = 'Invalid email or password for this login type.';

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return $genericError;
    }
    if ($user['role'] !== $expectedRole) {
        return $genericError;
    }
    if ($user['status'] !== 'active') {
        return 'This account has been suspended. Contact an administrator.';
    }

    // Regenerate session id on privilege change to prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];

    return true;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Creates a new account. $role must be 'student' or 'teacher' —
 * public signup is never allowed to create admin accounts.
 */
function register_user(string $name, string $email, string $password, string $role) {
    $name  = trim($name);
    $email = trim($email);

    if ($name === '' || $email === '' || $password === '') {
        return 'Please fill in all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!in_array($role, ['student', 'teacher'], true)) {
        return 'Invalid account type.';
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return 'An account with that email already exists.';
    }

    $stmt = db()->prepare(
        'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = db()->lastInsertId();

    return true;
}

/**
 * Where a logged-in user's dashboard/home lives, by role.
 */
function role_home(string $role): string {
    return match ($role) {
        'admin'   => 'admin/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        default   => 'dashboard.php',
    };
}

/* ---------- Path helper ---------- */

/**
 * Absolute base path of the project (mirrors the logic already
 * used in header.php/footer.php), safe to call from subfolders
 * like /admin and /teacher too.
 */
function base_path(): string {
    $docRoot    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
    $projectDir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $basePath   = '/' . ltrim(str_replace($docRoot, '', $projectDir), '/');
    return rtrim($basePath, '/') . '/';
}
