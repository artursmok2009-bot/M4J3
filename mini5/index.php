<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_ticket') {
        $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $room        = trim((string)($_POST['room'] ?? ''));
        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $priority    = (string)($_POST['priority'] ?? 'sredni');

        $allowedPriorities = ['niski', 'sredni', 'wysoki'];

        if (
            !$categoryId ||
            $room === '' ||
            $title === '' ||
            $description === '' ||
            !in_array($priority, $allowedPriorities, true)
        ) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'text' => 'Uzupełnij wszystkie wymagane pola formularza (sala, kategoria, tytuł, opis).'
            ];
            header('Location: index.php');
            exit;
        }

        if (mb_strlen($title, 'UTF-8') < 5 || mb_strlen($title, 'UTF-8') > 100) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'text' => 'Tytuł zgłoszenia musi mieć od 5 do 100 znaków.'
            ];
            header('Location: index.php');
            exit;
        }

        if (mb_strlen($description, 'UTF-8') > 300) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'text' => 'Opis usterki nie może przekraczać 300 znaków.'
            ];
            header('Location: index.php');
            exit;
        }

        $stmtInsert = $pdo->prepare("
            INSERT INTO tickets (category_id, room, title, description, priority, status)
            VALUES (:category_id, :room, :title, :description, :priority, 'nowe')
        ");
        $stmtInsert->execute([
            ':category_id' => $categoryId,
            ':room'        => $room,
            ':title'       => $title,
            ':description' => $description,
            ':priority'    => $priority,
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Nowe zgłoszenie serwisowe zostało pomyślnie dodane.'
        ];
        header('Location: index.php');
        exit;
    }

    if ($action === 'update_status') {
        $ticketId  = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
        $newStatus = (string)($_POST['new_status'] ?? '');
        $allowedStatuses = ['nowe', 'w_trakcie', 'zamkniete'];

        if ($ticketId && in_array($newStatus, $allowedStatuses, true)) {
            $stmtUpdate = $pdo->prepare("UPDATE tickets SET status = :status WHERE id = :id");
            $stmtUpdate->execute([
                ':status' => $newStatus,
                ':id'     => $ticketId,
            ]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'text' => 'Status zgłoszenia #' . $ticketId . ' został zaktualizowany.'
            ];
        }

        header('Location: index.php');
        exit;
    }

    if ($action === 'delete_ticket') {
        $ticketId = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);

        if ($ticketId) {
       
            $stmtDelete = $pdo->prepare("DELETE FROM tickets WHERE id = :id AND status = 'zamkniete'");
            $stmtDelete->execute([':id' => $ticketId]);

            if ($stmtDelete->rowCount() > 0) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'text' => 'Zamknięte zgłoszenie #' . $ticketId . ' zostało usunięte.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'text' => 'Można usuwać wyłącznie zgłoszenia ze statusem Zamknięte.'
                ];
            }
        }

        header('Location: index.php');
        exit;
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);


$categories = $pdo->query("SELECT id, name FROM categories ORDER BY id ASC")->fetchAll();


$stmtTickets = $pdo->query("
    SELECT 
        t.id,
        t.category_id,
        t.room,
        t.title,
        t.description,
        t.priority,
        t.status,
        t.created_at,
        c.name AS category_name
    FROM tickets t
    INNER JOIN categories c ON t.category_id = c.id
    ORDER BY t.created_at DESC
");
$tickets = $stmtTickets->fetchAll();


$stats = [
    'nowe'      => 0,
    'w_trakcie' => 0,
    'zamkniete' => 0,
];

foreach ($tickets as $ticket) {
    if (isset($stats[$ticket['status']])) {
        $stats[$ticket['status']]++;
    }
}

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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Helpdesk IT — System Zgłoszeń Serwisowych</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="app-header">
    <div class="container header-inner">
      <div class="brand">
        <span class="brand-icon">IT</span>
        <div>
          <h1>Helpdesk IT</h1>
          <p class="subtitle">System Zgłoszeń Serwisowych i Wsparcia Technicznego</p>
        </div>
      </div>
      <a href="export.php" class="btn btn-export">📥 Eksportuj do CSV</a>
    </div>
  </header>

  <main class="container main-wrapper">
 
    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
        <?= e($flash['text']) ?>
      </div>
    <?php endif; ?>

   
    <section class="stats-grid" aria-label="Podsumowanie zgłoszeń">
      <article class="stat-card stat-new">
        <span class="stat-label">Nowe zgłoszenia</span>
        <strong class="stat-value"><?= $stats['nowe'] ?></strong>
      </article>
      <article class="stat-card stat-progress">
        <span class="stat-label">W trakcie realizacji</span>
        <strong class="stat-value"><?= $stats['w_trakcie'] ?></strong>
      </article>
      <article class="stat-card stat-closed">
        <span class="stat-label">Zamknięte</span>
        <strong class="stat-value"><?= $stats['zamkniete'] ?></strong>
      </article>
    </section>

   
    <div class="layout-grid">
     
      <aside class="panel form-panel">
        <h2>Nowe zgłoszenie</h2>
        <p class="panel-desc">Opisz problem techniczny, aby przekazać go do działu IT.</p>

        <form id="ticket-form" action="index.php" method="POST" novalidate>
          <input type="hidden" name="action" value="add_ticket">

          <div class="form-group">
            <label for="room">Numer sali / lokalizacja *</label>
            <input
              type="text"
              id="room"
              name="room"
              maxlength="20"
              placeholder="np. Sala 204, Sekretariat"
              required
            >
            <small class="error-msg" id="room-error"></small>
          </div>

          <div class="form-group">
            <label for="category_id">Kategoria usterki *</label>
            <select id="category_id" name="category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>">
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="priority">Priorytet zgłoszenia *</label>
            <select id="priority" name="priority" required>
              <option value="niski">Niski</option>
              <option value="sredni" selected>Średni</option>
              <option value="wysoki">Wysoki</option>
            </select>
          </div>

          <div class="form-group">
            <label for="title">Tytuł zgłoszenia (min. 5 znaków) *</label>
            <input
              type="text"
              id="title"
              name="title"
              maxlength="100"
              placeholder="np. Brak połączenia z drukarką sieciową"
              required
            >
            <small class="error-msg" id="title-error"></small>
          </div>

          <div class="form-group">
            <label for="description">Szczegółowy opis usterki *</label>
            <textarea
              id="description"
              name="description"
              rows="4"
              maxlength="300"
              placeholder="Opisz objawy problemu oraz numer stanowiska..."
              required
            ></textarea>
            <div class="textarea-footer">
              <small class="error-msg" id="description-error"></small>
              <span id="char-counter" class="char-counter">0 / 300</span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block">Wyślij zgłoszenie</button>
        </form>
      </aside>

      
      <section class="panel tickets-panel" aria-labelledby="tickets-heading">
        <div class="tickets-header">
          <h2 id="tickets-heading">Lista zgłoszeń (<?= count($tickets) ?>)</h2>
        </div>

        <div class="filter-bar">
          <div class="search-box">
            <input
              type="search"
              id="search-input"
              placeholder="Szukaj po tytule, sali, kategorii lub opisie..."
              aria-label="Szukaj zgłoszeń"
            >
          </div>

          <div class="status-filters" role="group" aria-label="Filtruj według statusu">
            <button type="button" class="filter-btn active" data-filter="all">Wszystkie</button>
            <button type="button" class="filter-btn" data-filter="nowe">Nowe</button>
            <button type="button" class="filter-btn" data-filter="w_trakcie">W trakcie</button>
            <button type="button" class="filter-btn" data-filter="zamkniete">Zamknięte</button>
          </div>
        </div>

        <!-- Lista kart zgłoszeń -->
        <div id="tickets-list" class="tickets-list">
          <?php if (empty($tickets)): ?>
            <p class="empty-state">Brak zgłoszeń serwisowych w bazie danych.</p>
          <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
              <?php
                $prio = $ticket['priority'];
                $stat = $ticket['status'];
              ?>
              <article
                class="ticket-card priority-<?= e($prio) ?>"
                data-status="<?= e($stat) ?>"
              >
                <div class="ticket-top">
                  <div class="ticket-meta">
                    <span class="ticket-id">#<?= (int)$ticket['id'] ?></span>
                    <span class="badge-room">📍 <?= e($ticket['room']) ?></span>
                    <span class="badge-category"><?= e($ticket['category_name']) ?></span>
                  </div>

                  <div class="ticket-badges">
                    <span class="badge priority-badge-<?= e($prio) ?>">
                      Priorytet: <?= e($priorityLabels[$prio] ?? $prio) ?>
                    </span>
                    <span class="badge status-badge-<?= e($stat) ?>">
                      <?= e($statusLabels[$stat] ?? $stat) ?>
                    </span>
                  </div>
                </div>

                <h3 class="ticket-title"><?= e($ticket['title']) ?></h3>
                <p class="ticket-desc"><?= nl2br(e($ticket['description'])) ?></p>

                <div class="ticket-footer">
                  <time class="ticket-date" datetime="<?= e($ticket['created_at']) ?>">
                    Zgłoszono: <?= e(date('d.m.Y H:i', strtotime($ticket['created_at']))) ?>
                  </time>

                  <div class="ticket-actions">
                    <?php if ($stat === 'nowe'): ?>
                      <form action="index.php" method="POST" class="inline-form">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                        <input type="hidden" name="new_status" value="w_trakcie">
                        <button type="submit" class="btn btn-sm btn-progress">Przyjmij (W trakcie)</button>
                      </form>
                    <?php elseif ($stat === 'w_trakcie'): ?>
                      <form action="index.php" method="POST" class="inline-form">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                        <input type="hidden" name="new_status" value="zamkniete">
                        <button type="submit" class="btn btn-sm btn-complete">Zakończ (Zamknięte)</button>
                      </form>
                    <?php elseif ($stat === 'zamkniete'): ?>
                      <form action="index.php" method="POST" class="inline-form">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                        <input type="hidden" name="new_status" value="nowe">
                        <button type="submit" class="btn btn-sm btn-reopen">Otwórz ponownie</button>
                      </form>

                      <form action="index.php" method="POST" class="inline-form delete-form">
                        <input type="hidden" name="action" value="delete_ticket">
                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <p id="no-results-msg" class="empty-state hidden">
          Brak zgłoszeń spełniających wybrane kryteria wyszukiwania.
        </p>
      </section>
    </div>
  </main>

  <script src="js/script.js"></script>
</body>
</html>