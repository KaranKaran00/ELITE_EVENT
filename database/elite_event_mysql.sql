-- Elite Event — MySQL database
-- Import this file in phpMyAdmin, or simply open the website once and
-- includes/db.php will create the database/tables automatically.

CREATE DATABASE IF NOT EXISTS elite_event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE elite_event;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS instagram_posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    url VARCHAR(1000) NOT NULL,
    caption TEXT NULL,
    added_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_instagram_added_by (added_by),
    CONSTRAINT fk_instagram_user FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing project accounts are preserved during the SQLite -> MySQL conversion.
INSERT INTO users (id, name, email, password_hash, role, status, created_at) VALUES
(1, 'Site Admin', 'admin@eliteevent.local', '$2y$10$mSpO2CdHtfeiE/ozlExw/OLeITtK6nZPDzuDirqQVPQ.BuPknCuvi', 'admin', 'active', '2026-08-08 06:51:47'),
(2, 'karan khara', 'karan2khara2@gmail.com', '$2y$10$kgLfCLkCUPpvBmCxVQJQCe5C7rCngSTUDm.Z1WUfnDFUpzmDkhnc2', 'student', 'active', '2026-08-08 06:52:54'),
(3, 'mayur', 'mayur@gamil.com', '$2y$10$eJ8FpNCpA1pAXgySKmt0O.2b.XuCx/kKibBQWqTbDeL3N4sbnf.g6', 'teacher', 'active', '2026-08-08 06:55:05'),
(4, 'mayur', 'mayur@gmail.com', '$2y$10$dSzHrnbX.3BDTQ.x4AQVBuSn5S628e7Ip/do.uQqoPhbngDw09hQm', 'teacher', 'active', '2026-08-08 15:15:55')
ON DUPLICATE KEY UPDATE
name=VALUES(name), password_hash=VALUES(password_hash), role=VALUES(role), status=VALUES(status);

INSERT INTO instagram_posts (id, url, caption, added_by, created_at) VALUES
(1, 'https://www.instagram.com/p/DZ19EVVgkzn/?igsh=bHU1b3R4anRlaTk=', '', 1, '2026-08-08 06:51:47'),
(2, 'https://www.instagram.com/p/DZrXjbFC2bR/?igsh=MTF1NWpmNGR6Zm94ZQ==', '', 1, '2026-08-08 06:51:47'),
(3, 'https://www.instagram.com/p/DZg1eOOgtuB/?igsh=ZGpqb3lkem9zMnB1', '', 1, '2026-08-08 06:51:47'),
(4, 'https://www.instagram.com/reel/DYjBB8bBmnP/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA==', '', 1, '2026-08-08 06:51:47')
ON DUPLICATE KEY UPDATE url=VALUES(url), caption=VALUES(caption), added_by=VALUES(added_by);

INSERT INTO events (id, title, category, date, time, venue, city, organizer, description, google_form_url, created_by, created_at, updated_at) VALUES
(1, 'Sunset Indie Music Festival', 'music', '2026-08-28', '18:00:00', 'Riverside Amphitheatre', 'Mumbai', 'Indie Sounds Collective', 'A magical evening of indie music under the open sky with local and national artists performing live on two stages.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(2, 'UI/UX Design Bootcamp', 'workshop', '2026-08-30', '10:00:00', 'TechHub Co-working Space', 'Bangalore', 'DesignMind India', 'Hands-on two-day bootcamp covering user research, wireframing, prototyping in Figma, and usability testing.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(3, 'Heritage Craft Market', 'market', '2026-09-02', '11:00:00', 'Central Park Grounds', 'Ahmedabad', 'Craft Guild of Gujarat', 'Explore 100+ stalls of handmade crafts, textiles, pottery, and traditional art from artisans across Gujarat.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(4, 'Startup Founders Meetup', 'meetup', '2026-09-05', '18:30:00', 'WeWork Galaxy', 'Hyderabad', 'StartupSphere HYD', 'Monthly meetup for startup founders and aspiring entrepreneurs. Pitch, network, and learn from fellow builders.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(5, 'Rangmanch Drama Festival', 'cultural', '2026-09-10', '17:00:00', 'Main Auditorium, GEC', 'Ahmedabad', 'GEC Student Council', 'Annual college drama competition featuring performances from 10 teams across theatre, comedy, and mime.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(6, 'Hackathon 3.0 — 24 Hours', 'tech', '2026-09-15', '09:00:00', 'CS Block Lab 3, GEC', 'Ahmedabad', 'IEEE Student Chapter', 'Build innovative solutions in 24 hours. Open to all students.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(7, 'Street Food Festival 2026', 'food', '2026-09-20', '16:00:00', 'GMDC Grounds', 'Ahmedabad', 'Ahmedabad Food Lovers Club', 'Over 60 food stalls serving street food from across India. Live music, cooking demos, and food competitions.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04'),
(8, 'Inter-Dept Cricket League', 'sports', '2026-09-25', '08:00:00', 'College Sports Ground', 'Ahmedabad', 'Sports Committee, GEC', 'Annual inter-departmental cricket tournament. Register your team and compete for the championship trophy.', NULL, NULL, '2026-08-08 17:26:04', '2026-08-08 17:26:04')
ON DUPLICATE KEY UPDATE
 title=VALUES(title), category=VALUES(category), date=VALUES(date), time=VALUES(time), venue=VALUES(venue), city=VALUES(city), organizer=VALUES(organizer), description=VALUES(description), google_form_url=VALUES(google_form_url);

-- registrations intentionally start empty. Each (event_id,user_id) pair is unique.
