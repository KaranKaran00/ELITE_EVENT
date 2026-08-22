<?php
/* Elite Event — database-backed event data and helpers. */
require_once __DIR__ . '/auth.php';

$currentUser = current_user();

$categories = [
    ['slug'=>'music','name'=>'Music','icon'=>'🎵','color'=>'#7B2D8B'],
    ['slug'=>'workshop','name'=>'Workshops','icon'=>'👥','color'=>'#1A5276'],
    ['slug'=>'market','name'=>'Markets','icon'=>'🏪','color'=>'#1E8449'],
    ['slug'=>'meetup','name'=>'Meetups','icon'=>'🤝','color'=>'#B7950B'],
    ['slug'=>'food','name'=>'Food & Drink','icon'=>'🍽️','color'=>'#C0392B'],
    ['slug'=>'cultural','name'=>'Cultural','icon'=>'🎭','color'=>'#6C3483'],
    ['slug'=>'tech','name'=>'Tech','icon'=>'💻','color'=>'#1A5276'],
    ['slug'=>'sports','name'=>'Sports','icon'=>'🏆','color'=>'#1E8449'],
];

function getAllEvents(): array {
    return db()->query('SELECT * FROM events ORDER BY date ASC, time ASC, id ASC')->fetchAll();
}

$events = getAllEvents();

function getEventById(array $events, int $id): ?array {
    foreach ($events as $e) if ((int)$e['id'] === $id) return $e;
    return null;
}

function getCategory(string $slug, array $categories): array {
    foreach ($categories as $cat) if ($cat['slug'] === $slug) return $cat;
    return ['slug'=>$slug,'name'=>ucfirst($slug),'icon'=>'📌','color'=>'#888'];
}

function formatEventDate(string $date): string {
    $ts = strtotime($date);
    return $ts ? date('D, d M Y', $ts) : $date;
}

function formatEventTime(string $time): string {
    $ts = strtotime($time);
    return $ts ? date('g:i A', $ts) : $time;
}

function filterEvents(array $events, string $q, string $category): array {
    return array_values(array_filter($events, function ($e) use ($q, $category) {
        if ($q !== '') {
            $haystack = strtolower($e['title'].' '.$e['venue'].' '.$e['city'].' '.$e['organizer']);
            if (strpos($haystack, strtolower($q)) === false) return false;
        }
        return $category === 'all' || $e['category'] === $category;
    }));
}

function userRegisteredForEvent(int $userId, int $eventId): bool {
    $stmt = db()->prepare('SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1');
    $stmt->execute([$userId, $eventId]);
    return (bool)$stmt->fetchColumn();
}

function registrationCount(int $eventId): int {
    $stmt = db()->prepare('SELECT COUNT(*) FROM registrations WHERE event_id = ?');
    $stmt->execute([$eventId]);
    return (int)$stmt->fetchColumn();
}

function userRegistrations(int $userId): array {
    $stmt = db()->prepare('SELECT r.*, e.title, e.date, e.time, e.venue, e.city, e.category FROM registrations r JOIN events e ON e.id = r.event_id WHERE r.user_id = ? ORDER BY e.date ASC, e.time ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/* ---------- Feedback ---------- */

/**
 * Feedback for a single event, newest first, with the reviewer's name attached.
 */
function eventFeedback(int $eventId): array {
    $stmt = db()->prepare(
        'SELECT f.*, u.name AS user_name FROM feedback f JOIN users u ON u.id = f.user_id
         WHERE f.event_id = ? ORDER BY f.created_at DESC'
    );
    $stmt->execute([$eventId]);
    return $stmt->fetchAll();
}

/**
 * Recent feedback across all events, for the homepage testimonial cards.
 */
function recentFeedback(int $limit = 6): array {
    $limit = max(1, $limit);
    $stmt = db()->prepare(
        'SELECT f.*, u.name AS user_name, e.title AS event_title FROM feedback f
         JOIN users u ON u.id = f.user_id JOIN events e ON e.id = f.event_id
         ORDER BY f.created_at DESC LIMIT ' . $limit
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function userFeedbackForEvent(int $userId, int $eventId): ?array {
    $stmt = db()->prepare('SELECT * FROM feedback WHERE user_id = ? AND event_id = ? LIMIT 1');
    $stmt->execute([$userId, $eventId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Submits (or updates) a student's feedback for an event they registered for.
 * $mediaUrl/$mediaType come from handleFeedbackMediaUpload() — pass both null
 * to leave feedback with no photo/video attached, or reuse the existing
 * values when updating feedback and no new file was chosen.
 * Returns true on success, or a string error message on failure.
 */
function submitFeedback(int $userId, int $eventId, int $rating, string $comment, ?string $mediaUrl, ?string $mediaType) {
    $comment = trim($comment);

    if (!userRegisteredForEvent($userId, $eventId)) {
        return 'You can only leave feedback for events you registered for.';
    }
    if ($rating < 1 || $rating > 5) {
        return 'Please choose a star rating from 1 to 5.';
    }
    if ($comment === '') {
        return 'Please write a short comment about the event.';
    }
    if ($mediaType !== null && !in_array($mediaType, ['image', 'video'], true)) {
        $mediaType = null;
        $mediaUrl = null;
    }

    $stmt = db()->prepare(
        'INSERT INTO feedback (event_id, user_id, rating, comment, media_url, media_type) VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), media_url = VALUES(media_url), media_type = VALUES(media_type), created_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute([$eventId, $userId, $rating, $comment, $mediaUrl, $mediaType]);

    return true;
}

/**
 * Renders the ⭐ stars for a given rating (1-5), used by the feedback card partial.
 */
function starRating(int $rating): string {
    $rating = max(0, min(5, $rating));
    return str_repeat('⭐', $rating) . str_repeat('☆', 5 - $rating);
}
