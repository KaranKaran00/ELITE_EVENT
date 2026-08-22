<?php
/*
 * Elite Event — MySQL database connection + first-run installer.
 * XAMPP defaults are used below: host=localhost, user=root, blank password.
 * Change these values in config/database.php for another MySQL setup.
 */

function db_config(): array {
    static $config = null;
    if ($config !== null) return $config;

    $configFile = __DIR__ . '/../config/database.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
    } else {
        $config = [
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'elite_event',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ];
    }

    return $config;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $c = db_config();
    $charset = $c['charset'] ?? 'utf8mb4';

    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'],
            (int)($c['port'] ?? 3306),
            $c['database'],
            $charset
        );

        $pdo = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        // If the database has not been created yet, create it automatically.
        if ((int)$e->getCode() === 1049) {
            $serverDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $c['host'], (int)($c['port'] ?? 3306), $charset);
            $server = new PDO($serverDsn, $c['username'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $c['database']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $server = null;

            $pdo = new PDO($dsn, $c['username'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            throw $e;
        }
    }

    db_install_or_migrate($pdo);
    return $pdo;
}

function db_install_or_migrate(PDO $pdo): void {
    $requiredTables = ['users', 'instagram_posts', 'events', 'registrations', 'feedback'];
    $placeholders = implode(',', array_fill(0, count($requiredTables), '?'));
    $stmt = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ($placeholders)");
    $stmt->execute($requiredTables);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('users', $tables, true)) {
        $pdo->exec("CREATE TABLE users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
            status ENUM('active','suspended') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_users_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    if (!in_array('instagram_posts', $tables, true)) {
        $pdo->exec("CREATE TABLE instagram_posts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            url VARCHAR(1000) NOT NULL,
            caption TEXT NULL,
            added_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_instagram_added_by (added_by),
            CONSTRAINT fk_instagram_user FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    if (!in_array('events', $tables, true)) {
        $pdo->exec("CREATE TABLE events (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            category VARCHAR(60) NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL,
            venue VARCHAR(255) NOT NULL,
            city VARCHAR(120) NOT NULL,
            organizer VARCHAR(190) NOT NULL,
            description TEXT NULL,
            google_form_url VARCHAR(1000) NULL,
            created_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_events_date_time (date, time),
            KEY idx_events_category (category),
            KEY idx_events_created_by (created_by),
            CONSTRAINT fk_events_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        $columns = $pdo->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('google_form_url', $columns, true)) $pdo->exec("ALTER TABLE events ADD COLUMN google_form_url VARCHAR(1000) NULL AFTER description");
        if (!in_array('created_by', $columns, true)) $pdo->exec("ALTER TABLE events ADD COLUMN created_by INT UNSIGNED NULL AFTER google_form_url");
        if (!in_array('created_at', $columns, true)) $pdo->exec("ALTER TABLE events ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        if (!in_array('updated_at', $columns, true)) $pdo->exec("ALTER TABLE events ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    if (!in_array('registrations', $tables, true)) {
        $pdo->exec("CREATE TABLE registrations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_event_user (event_id, user_id),
            KEY idx_registrations_user (user_id),
            KEY idx_registrations_event (event_id),
            CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            CONSTRAINT fk_reg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    if (!in_array('feedback', $tables, true)) {
        $pdo->exec("CREATE TABLE feedback (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            comment TEXT NOT NULL,
            photo_url VARCHAR(1000) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_feedback_event_user (event_id, user_id),
            KEY idx_feedback_event (event_id),
            KEY idx_feedback_user (user_id),
            CONSTRAINT fk_feedback_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT chk_feedback_rating CHECK (rating BETWEEN 1 AND 5)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Seed only when the tables are empty. Existing MySQL data is never overwritten.
    if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Site Admin', 'admin@eliteevent.local', password_hash('Admin@123', PASSWORD_DEFAULT), 'admin']);
        $adminId = (int)$pdo->lastInsertId();

        if ((int)$pdo->query('SELECT COUNT(*) FROM instagram_posts')->fetchColumn() === 0) {
            $stmt = $pdo->prepare('INSERT INTO instagram_posts (url, caption, added_by) VALUES (?, ?, ?)');
            foreach ([
                'https://www.instagram.com/p/DZ19EVVgkzn/?igsh=bHU1b3R4anRlaTk=',
                'https://www.instagram.com/p/DZrXjbFC2bR/?igsh=MTF1NWpmNGR6Zm94ZQ==',
                'https://www.instagram.com/p/DZg1eOOgtuB/?igsh=ZGpqb3lkem9zMnB1',
                'https://www.instagram.com/reel/DYjBB8bBmnP/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA==',
            ] as $url) {
                $stmt->execute([$url, '', $adminId]);
            }
        }
    }

    db_seed_events_mysql($pdo);
}

function db_seed_events_mysql(PDO $pdo): void {
    if ((int)$pdo->query('SELECT COUNT(*) FROM events')->fetchColumn() > 0) return;

    $events = [
        ['Sunset Indie Music Festival','music','2026-08-28','18:00:00','Riverside Amphitheatre','Mumbai','Indie Sounds Collective','A magical evening of indie music under the open sky with local and national artists performing live on two stages.'],
        ['UI/UX Design Bootcamp','workshop','2026-08-30','10:00:00','TechHub Co-working Space','Bangalore','DesignMind India','Hands-on two-day bootcamp covering user research, wireframing, prototyping in Figma, and usability testing.'],
        ['Heritage Craft Market','market','2026-09-02','11:00:00','Central Park Grounds','Ahmedabad','Craft Guild of Gujarat','Explore 100+ stalls of handmade crafts, textiles, pottery, and traditional art from artisans across Gujarat.'],
        ['Startup Founders Meetup','meetup','2026-09-05','18:30:00','WeWork Galaxy','Hyderabad','StartupSphere HYD','Monthly meetup for startup founders and aspiring entrepreneurs. Pitch, network, and learn from fellow builders.'],
        ['Rangmanch Drama Festival','cultural','2026-09-10','17:00:00','Main Auditorium, GEC','Ahmedabad','GEC Student Council','Annual college drama competition featuring performances from 10 teams across theatre, comedy, and mime.'],
        ['Hackathon 3.0 — 24 Hours','tech','2026-09-15','09:00:00','CS Block Lab 3, GEC','Ahmedabad','IEEE Student Chapter','Build innovative solutions in 24 hours. Open to all students.'],
        ['Street Food Festival 2026','food','2026-09-20','16:00:00','GMDC Grounds','Ahmedabad','Ahmedabad Food Lovers Club','Over 60 food stalls serving street food from across India. Live music, cooking demos, and food competitions.'],
        ['Inter-Dept Cricket League','sports','2026-09-25','08:00:00','College Sports Ground','Ahmedabad','Sports Committee, GEC','Annual inter-departmental cricket tournament. Register your team and compete for the championship trophy.'],
    ];

    $stmt = $pdo->prepare('INSERT INTO events (title, category, date, time, venue, city, organizer, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($events as $event) $stmt->execute($event);
}
