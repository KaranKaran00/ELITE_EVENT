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
