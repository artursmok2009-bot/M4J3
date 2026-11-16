<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Nieprawidłowy identyfikator rezerwacji.';
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        res.id,
        res.user_name,
        res.start_time,
        res.end_time,
        r.name AS resource_name,
        r.type AS resource_type
    FROM reservations res
    INNER JOIN resources r ON res.resource_id = r.id
    WHERE res.id = :id
");
$stmt->execute([':id' => $id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Rezerwacja nie została znaleziona.';
    exit;
}

function escapeIcsText(string $text): string
{
    $decoded = htmlspecialchars_decode($text, ENT_QUOTES);
    $decoded = str_replace(['\\', ';', ',', "\r\n", "\n", "\r"], ['\\\\', '\;', '\,', '\n', '\n', '\n'], $decoded);
    return $decoded;
}

$dtStamp = gmdate('Ymd\THis\Z');
$dtStart = date('Ymd\THis', strtotime($reservation['start_time']));
$dtEnd   = date('Ymd\THis', strtotime($reservation['end_time']));
$uid     = 'reservation-' . $reservation['id'] . '@smartoffice.local';
$summary = escapeIcsText('Rezerwacja: ' . $reservation['resource_name'] . ' (' . $reservation['user_name'] . ')');
$desc    = escapeIcsText('Stanowisko: ' . $reservation['resource_name'] . ' | Rezerwujący: ' . $reservation['user_name']);

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="rezerwacja_' . $reservation['id'] . '.ics"');

$icsLines = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Smart Office SPA//System Rezerwacji//PL',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    'BEGIN:VEVENT',
    'UID:' . $uid,
    'DTSTAMP:' . $dtStamp,
    'DTSTART:' . $dtStart,
    'DTEND:' . $dtEnd,
    'SUMMARY:' . $summary,
    'DESCRIPTION:' . $desc,
    'LOCATION:' . escapeIcsText($reservation['resource_name']),
    'END:VEVENT',
    'END:VCALENDAR'
];

echo implode("\r\n", $icsLines) . "\r\n";