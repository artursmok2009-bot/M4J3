<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$stmt = $pdo->query("
    SELECT 
        t.id,
        c.name AS category_name,
        t.room,
        t.title,
        t.description,
        t.priority,
        t.status,
        t.created_at
    FROM tickets t
    INNER JOIN categories c ON t.category_id = c.id
    ORDER BY t.created_at DESC
");
$tickets = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="zgloszenia_it.csv"');

$output = fopen('php://output', 'w');

// Znacznik UTF-8 BOM zapewniający poprawne wyświetlanie polskich znaków w Excelu
fwrite($output, "\xEF\xBB\xBF");

// Nagłówki kolumn CSV
fputcsv($output, [
    'ID',
    'Kategoria',
    'Sala / Lokalizacja',
    'Tytuł zgłoszenia',
    'Opis usterki',
    'Priorytet',
    'Status',
    'Data utworzenia'
], ';');

$priorityLabels = [
    'niski'  => 'Niski',
    'sredni' => 'Średni',
    'wysoki' => 'Wysoki',
];

$statusLabels = [
    'nowe'      => 'Nowe',
    'w_trakcie' => 'W trakcie',
    'zamkniete' => 'Zamknięte',
];

foreach ($tickets as $row) {
    fputcsv($output, [
        $row['id'],
        $row['category_name'],
        $row['room'],
        $row['title'],
        $row['description'],
        $priorityLabels[$row['priority']] ?? $row['priority'],
        $statusLabels[$row['status']] ?? $row['status'],
        $row['created_at'],
    ], ';');
}

fclose($output);
exit;