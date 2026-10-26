<?php
header('Content-Type: text/html; charset=utf-8');

$bledy = [];
$sukces = false;

// Zadanie 21
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Zadanie 22
    $imie = $_POST["imie"] ?? "";
    
    // Zadanie 23
    $email = $_POST["email"] ?? "";
    
    $telefon = $_POST["telefon"] ?? "";
    $haslo = $_POST["haslo"] ?? "";
    $temat = $_POST["temat"] ?? "";
    $dataKontaktu = $_POST["data_kontaktu"] ?? "Brak wyboru";
    $wiadomosc = $_POST["wiadomosc"] ?? "";
    
    // Zadanie 27
    $zgoda = $_POST["zgoda"] ?? "";

    // Zadanie 25
    $imie = trim($imie);
    $email = trim($email);
    $telefon = trim($telefon);
    $wiadomosc = trim($wiadomosc);

    if (empty($imie)) {
        $bledy[] = "Imię nie może być puste.";
    } elseif (mb_strlen($imie) < 2) {
        $bledy[] = "Imię musi składać się z co najmniej 2 znaków.";
    }

    // Zadanie 26
    if (empty($email)) {
        $bledy[] = "Adres e-mail jest wymagany.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $bledy[] = "Podany adres e-mail jest nieprawidłowy.";
    }

    // Zadanie dodatkowe w PHP: Walidacja telefonu
    if (!empty($telefon) && (strlen($telefon) !== 9 || !ctype_digit($telefon))) {
        $bledy[] = "Telefon musi zawierać dokładnie 9 cyfr.";
    }

    if (empty($wiadomosc)) {
        $bledy[] = "Treść wiadomości nie może być pusta.";
    }

    // Zadanie 27
    if ($zgoda !== "tak") {
        $bledy[] = "Wymagana jest akceptacja regulaminu.";
    }

    // Zadanie 28
    if (empty($bledy)) {
        $sukces = true;
    }
} else {
    $bledy[] = "Formularz nie został przesłany prawidłowo (metoda POST).";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podsumowanie formularza</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .result-card {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 30px auto;
        }
        .success {
            color: #27ae60;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .error-title {
            color: #c0392b;
            border-bottom: 2px solid #c0392b;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f2f2f2;
            width: 35%;
        }
        .error-list {
            color: #c0392b;
            padding-left: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="result-card">
    <?php if ($sukces): ?>
        <!-- Zadanie 28 -->
        <h2 class="success">Dziękujemy! Formularz został wysłany.</h2>
        <p>Oto podsumowanie odebranych danych:</p>

        <!-- Zadanie 24 & Zadanie dodatkowe: Czytelna tabela -->
        <table class="data-table">
            <tr>
                <th>Imię:</th>
                <td><?= htmlspecialchars($imie, ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>E-mail:</th>
                <td><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>Telefon:</th>
                <td><?= htmlspecialchars($telefon ?: "Nie podano", ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>Hasło:</th>
                <td><?= htmlspecialchars($haslo ? "••••••••" : "Nie podano", ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>Temat:</th>
                <td><?= htmlspecialchars($temat, ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>Data kontaktu:</th>
                <td><?= htmlspecialchars($dataKontaktu, ENT_QUOTES, "UTF-8") ?></td>
            </tr>
            <tr>
                <th>Wiadomość:</th>
                <td><?= nl2br(htmlspecialchars($wiadomosc, ENT_QUOTES, "UTF-8")) ?></td>
            </tr>
            <tr>
                <th>Zgoda na regulamin:</th>
                <td>Tak</td>
            </tr>
        </table>

    <?php else: ?>
        <!-- Zadanie 28 -->
        <h2 class="error-title">Wystąpiły błędy:</h2>
        <ul class="error-list">
            <?php foreach ($bledy as $blad): ?>
                <li><?= htmlspecialchars($blad, ENT_QUOTES, "UTF-8") ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="index.php" class="back-link">&laquo; Powrót do formularza</a>
</div>

</body>
</html>