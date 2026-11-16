<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

function sendJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function parseDateTime(string $value): ?DateTimeImmutable
{
    $normalized = str_replace('T', ' ', trim($value));
    foreach (['Y-m-d H:i', 'Y-m-d H:i:s'] as $format) {
        $dt = DateTimeImmutable::createFromFormat($format, $normalized);
        if ($dt !== false && $dt->format($format) === $normalized) {
            return $dt;
        }
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':
            $startRaw = (string)($_GET['start'] ?? '');
            $endRaw   = (string)($_GET['end'] ?? '');

            $startDt = parseDateTime($startRaw);
            $endDt   = parseDateTime($endRaw);

            if (!$startDt || !$endDt) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Wymagane parametry start oraz end w formacie YYYY-MM-DD HH:MM.'
                ]);
            }

            if ($endDt <= $startDt) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.'
                ]);
            }

            $startSql = $startDt->format('Y-m-d H:i:s');
            $endSql   = $endDt->format('Y-m-d H:i:s');

            // Sprawdzenie dostępności zasobów (kolizja: StartA < EndB AND EndA > StartB)
            $stmtResources = $pdo->prepare("
                SELECT 
                    r.id,
                    r.name,
                    r.type,
                    r.capacity,
                    EXISTS (
                        SELECT 1 
                        FROM reservations res 
                        WHERE res.resource_id = r.id 
                          AND res.start_time < :end_time 
                          AND res.end_time > :start_time
                    ) AS is_occupied
                FROM resources r
                ORDER BY r.id ASC
            ");
            $stmtResources->execute([
                ':start_time' => $startSql,
                ':end_time'   => $endSql,
            ]);

            $resources = array_map(static function (array $row): array {
                $row['id'] = (int)$row['id'];
                $row['capacity'] = (int)$row['capacity'];
                $row['is_occupied'] = (bool)$row['is_occupied'];
                return $row;
            }, $stmtResources->fetchAll());

            // Pobranie listy aktywnych (bieżących i przyszłych) rezerwacji
            $stmtReservations = $pdo->prepare("
                SELECT 
                    res.id,
                    res.resource_id,
                    res.user_name,
                    DATE_FORMAT(res.start_time, '%Y-%m-%d %H:%i') AS start_time,
                    DATE_FORMAT(res.end_time, '%Y-%m-%d %H:%i') AS end_time,
                    res.created_at,
                    r.name AS resource_name,
                    r.type AS resource_type
                FROM reservations res
                INNER JOIN resources r ON res.resource_id = r.id
                WHERE res.end_time >= NOW()
                ORDER BY res.start_time ASC
            ");
            $stmtReservations->execute();
            $reservations = $stmtReservations->fetchAll();

            sendJson(200, [
                'status'       => 'success',
                'resources'    => $resources,
                'reservations' => $reservations,
            ]);
            break;

        case 'POST':
            $rawBody = file_get_contents('php://input');
            $input = json_decode($rawBody ?: '', true);

            if (!is_array($input)) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Nieprawidłowy format danych JSON.'
                ]);
            }

            $resourceId  = filter_var($input['resource_id'] ?? null, FILTER_VALIDATE_INT);
            $rawUserName = trim((string)($input['user_name'] ?? ''));
            $startRaw    = (string)($input['start_time'] ?? '');
            $endRaw      = (string)($input['end_time'] ?? '');

            if (!$resourceId || $resourceId <= 0) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Nieprawidłowy identyfikator stanowiska.'
                ]);
            }

            if (mb_strlen($rawUserName, 'UTF-8') < 3 || mb_strlen($rawUserName, 'UTF-8') > 100) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Imię i nazwisko musi zawierać od 3 do 100 znaków.'
                ]);
            }

            $startDt = parseDateTime($startRaw);
            $endDt   = parseDateTime($endRaw);

            if (!$startDt || !$endDt) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Nieprawidłowy format daty lub godziny.'
                ]);
            }

            if ($endDt <= $startDt) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.'
                ]);
            }

            // Blokada rezerwacji w przeszłości (z 1-minutowym marginesem technicznym)
            if ($startDt < new DateTimeImmutable('-1 minute')) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Nie można utworzyć rezerwacji w przeszłości.'
                ]);
            }

            // Sprawdzenie, czy zasób istnieje
            $stmtCheckResource = $pdo->prepare("SELECT id, name FROM resources WHERE id = :id");
            $stmtCheckResource->execute([':id' => $resourceId]);
            $resource = $stmtCheckResource->fetch();

            if (!$resource) {
                sendJson(404, [
                    'status'  => 'error',
                    'message' => 'Wybrane stanowisko nie istnieje.'
                ]);
            }

            $startSql = $startDt->format('Y-m-d H:i:s');
            $endSql   = $endDt->format('Y-m-d H:i:s');

            // Wykrywanie kolizji: StartA < EndB AND EndA > StartB
            $stmtCollision = $pdo->prepare("
                SELECT COUNT(*) 
                FROM reservations 
                WHERE resource_id = :resource_id 
                  AND start_time < :end_time 
                  AND end_time > :start_time
            ");
            $stmtCollision->execute([
                ':resource_id' => $resourceId,
                ':start_time'  => $startSql,
                ':end_time'    => $endSql,
            ]);

            if ((int)$stmtCollision->fetchColumn() > 0) {
                sendJson(409, [
                    'status'  => 'error',
                    'message' => 'Wybrane stanowisko jest już zajęte w tym przedziale czasowym.'
                ]);
            }

            $safeUserName = htmlspecialchars($rawUserName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $stmtInsert = $pdo->prepare("
                INSERT INTO reservations (resource_id, user_name, start_time, end_time)
                VALUES (:resource_id, :user_name, :start_time, :end_time)
            ");
            $stmtInsert->execute([
                ':resource_id' => $resourceId,
                ':user_name'   => $safeUserName,
                ':start_time'  => $startSql,
                ':end_time'    => $endSql,
            ]);

            $newId = (int)$pdo->lastInsertId();

            sendJson(201, [
                'status'        => 'success',
                'message'       => 'Rezerwacja została pomyślnie utworzona.',
                'id'            => $newId,
                'resource_name' => $resource['name'],
            ]);
            break;

        case 'DELETE':
            $rawId = $_GET['id'] ?? null;
            if ($rawId === null) {
                $input = json_decode(file_get_contents('php://input') ?: '', true);
                if (is_array($input) && isset($input['id'])) {
                    $rawId = $input['id'];
                }
            }

            $id = filter_var($rawId, FILTER_VALIDATE_INT);
            if (!$id || $id <= 0) {
                sendJson(400, [
                    'status'  => 'error',
                    'message' => 'Nieprawidłowy identyfikator rezerwacji.'
                ]);
            }

            $stmtDelete = $pdo->prepare("DELETE FROM reservations WHERE id = :id");
            $stmtDelete->execute([':id' => $id]);

            if ($stmtDelete->rowCount() === 0) {
                sendJson(404, [
                    'status'  => 'error',
                    'message' => 'Nie znaleziono rezerwacji o podanym identyfikatorze.'
                ]);
            }

            sendJson(200, [
                'status'  => 'success',
                'message' => 'Rezerwacja została anulowana.'
            ]);
            break;

        default:
            sendJson(405, [
                'status'  => 'error',
                'message' => 'Niedozwolona metoda HTTP.'
            ]);
    }
} catch (Throwable $e) {
    sendJson(500, [
        'status'  => 'error',
        'message' => 'Wystąpił wewnętrzny błąd serwera.'
    ]);
}