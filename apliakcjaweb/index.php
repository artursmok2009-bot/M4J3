<?php
$bledy = array();
$pokazKarta = false;
$zapisano = false;

$imie = "";
$klasa = "Wojownik";
$sila = 10;
$zrecznosc = 10;
$inteligencja = 10;
$hp = 0;

$plikJson = "postacie.json";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $imie = isset($_POST["imie"]) ? trim($_POST["imie"]) : "";
    $klasa = isset($_POST["klasa"]) ? trim($_POST["klasa"]) : "";
    $sila = isset($_POST["sila"]) ? (int)$_POST["sila"] : 0;
    $zrecznosc = isset($_POST["zrecznosc"]) ? (int)$_POST["zrecznosc"] : 0;
    $inteligencja = isset($_POST["inteligencja"]) ? (int)$_POST["inteligencja"] : 0;

    $dozwoloneKlasy = array("Wojownik", "Mag", "Łotrzyk");

    if ($imie === "") {
        $bledy[] = "Wpisz imię postaci.";
    }
    if (!in_array($klasa, $dozwoloneKlasy)) {
        $bledy[] = "Nieprawidłowa klasa.";
    }
    if ($sila < 1 || $sila > 20) {
        $bledy[] = "Siła musi być z zakresu 1-20.";
    }
    if ($zrecznosc < 1 || $zrecznosc > 20) {
        $bledy[] = "Zręczność musi być z zakresu 1-20.";
    }
    if ($inteligencja < 1 || $inteligencja > 20) {
        $bledy[] = "Inteligencja musi być z zakresu 1-20.";
    }

    if (count($bledy) === 0) {
        $hp = ($sila * 10) + ($zrecznosc * 2);
        $pokazKarta = true;

        $nowaPostac = array(
            "imie" => $imie,
            "klasa" => $klasa,
            "sila" => $sila,
            "zrecznosc" => $zrecznosc,
            "inteligencja" => $inteligencja,
            "hp" => $hp,
            "data" => date("Y-m-d H:i")
        );

        $listaPostaci = array();
        if (file_exists($plikJson)) {
            $zawartosc = file_get_contents($plikJson);
            $listaPostaci = json_decode($zawartosc, true);
            if (!is_array($listaPostaci)) {
                $listaPostaci = array();
            }
        }

        $listaPostaci[] = $nowaPostac;
        if (file_put_contents($plikJson, json_encode($listaPostaci, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $zapisano = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karta Postaci RPG</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #1a1a24;
            color: #dcdcdc;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .main-box {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h1, h2 {
            text-align: center;
            color: #e67e22;
            margin-bottom: 15px;
        }

        .form-rpg {
            background-color: #242434;
            padding: 25px;
            border-radius: 8px;
            border: 3px solid #e74c3c;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        .field {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="number"],
        select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #444;
            background-color: #1a1a24;
            color: #fff;
            font-size: 1rem;
            width: 100%;
        }

        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 15px;
            font-weight: bold;
            font-size: 0.95rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit {
            flex: 2;
            background-color: #e67e22;
            color: #fff;
        }

        .btn-submit:hover {
            background-color: #d35400;
        }

        .btn-random {
            flex: 1;
            background-color: #8e44ad;
            color: #fff;
        }

        .btn-random:hover {
            background-color: #7d3c98;
        }

        .btn-reset {
            flex: 1;
            background-color: #555;
            color: #fff;
        }

        .btn-reset:hover {
            background-color: #666;
        }

        .error-msg {
            background-color: #5c1d1d;
            border: 1px solid #e74c3c;
            color: #ff9999;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .error-msg ul {
            margin-left: 20px;
        }

        .success-msg {
            background-color: #1e4620;
            border: 1px solid #2ecc71;
            color: #a3e6b1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }

        .card {
            background-color: #242434;
            border: 2px solid #e67e22;
            border-radius: 8px;
            padding: 20px;
        }

        .card-header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .card-header span {
            color: #aaa;
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-item {
            background-color: #1a1a24;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #333;
        }

        .stat-item.hp {
            grid-column: span 2;
            background-color: #381818;
            border-color: #c0392b;
        }

        .stat-title {
            font-size: 0.8rem;
            color: #888;
            text-transform: uppercase;
        }

        .stat-val {
            font-size: 1.4rem;
            font-weight: bold;
            color: #fff;
            margin-top: 3px;
        }
    </style>
</head>
<body>

<div class="main-box">

    <form action="index.php" method="POST" class="form-rpg" id="rpgForm">
        <h1>Kreator Postaci RPG</h1>

        <?php if ($zapisano): ?>
            <div class="success-msg">
                Postać została zapisana do pliku postacie.json!
            </div>
        <?php endif; ?>

        <?php if (!empty($bledy)): ?>
            <div class="error-msg">
                <ul>
                    <?php foreach ($bledy as $b): ?>
                        <li><?php echo htmlspecialchars($b); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="field">
            <label for="imie">Imię postaci:</label>
            <input type="text" id="imie" name="imie" required value="<?php echo htmlspecialchars($imie); ?>">
        </div>

        <div class="field">
            <label for="klasa">Klasa postaci:</label>
            <select id="klasa" name="klasa" required>
                <option value="Wojownik" <?php if($klasa === "Wojownik") echo "selected"; ?>>Wojownik</option>
                <option value="Mag" <?php if($klasa === "Mag") echo "selected"; ?>>Mag</option>
                <option value="Łotrzyk" <?php if($klasa === "Łotrzyk") echo "selected"; ?>>Łotrzyk</option>
            </select>
        </div>

        <div class="field">
            <label for="sila">Siła (1-20):</label>
            <input type="number" id="sila" name="sila" min="1" max="20" value="<?php echo $sila; ?>" required>
        </div>

        <div class="field">
            <label for="zrecznosc">Zręczność (1-20):</label>
            <input type="number" id="zrecznosc" name="zrecznosc" min="1" max="20" value="<?php echo $zrecznosc; ?>" required>
        </div>

        <div class="field">
            <label for="inteligencja">Inteligencja (1-20):</label>
            <input type="number" id="inteligencja" name="inteligencja" min="1" max="20" value="<?php echo $inteligencja; ?>" required>
        </div>

        <div class="btn-container">
            <button type="submit" class="btn btn-submit">Generuj i zapisz</button>
            <button type="button" id="btnRandom" class="btn btn-random">Losuj</button>
            <button type="button" id="btnReset" class="btn btn-reset">Wyczyść</button>
        </div>
    </form>

    <?php if ($pokazKarta): ?>
        <div class="card" id="characterCard">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($imie); ?></h2>
                <span>Klasa: <?php echo htmlspecialchars($klasa); ?></span>
            </div>

            <div class="stats-grid">
                <div class="stat-item hp">
                    <div class="stat-title">Punkty Życia (HP)</div>
                    <div class="stat-val"><?php echo $hp; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-title">Siła</div>
                    <div class="stat-val"><?php echo $sila; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-title">Zręczność</div>
                    <div class="stat-val"><?php echo $zrecznosc; ?></div>
                </div>
                <div class="stat-item" style="grid-column: span 2;">
                    <div class="stat-title">Inteligencja</div>
                    <div class="stat-val"><?php echo $inteligencja; ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    const selectKlasa = document.getElementById('klasa');
    const rpgForm = document.getElementById('rpgForm');
    const inputImie = document.getElementById('imie');
    const inputSila = document.getElementById('sila');
    const inputZrecznosc = document.getElementById('zrecznosc');
    const inputInteligencja = document.getElementById('inteligencja');
    const btnRandom = document.getElementById('btnRandom');
    const btnReset = document.getElementById('btnReset');
    const characterCard = document.getElementById('characterCard');

    function zmienKolorObramowania() {
        const klasa = selectKlasa.value;
        if (klasa === 'Wojownik') {
            rpgForm.style.borderColor = '#e74c3c';
        } else if (klasa === 'Mag') {
            rpgForm.style.borderColor = '#3498db';
        } else if (klasa === 'Łotrzyk') {
            rpgForm.style.borderColor = '#2ecc71';
        }
    }

    selectKlasa.addEventListener('change', zmienKolorObramowania);
    zmienKolorObramowania();

    btnRandom.addEventListener('click', function () {
        const imiona = ['Thorin', 'Geralt', 'Legolas', 'Yennefer', 'Ezio', 'Valendor'];
        const klasy = ['Wojownik', 'Mag', 'Łotrzyk'];

        inputImie.value = imiona[Math.floor(Math.random() * imiona.length)];
        selectKlasa.value = klasy[Math.floor(Math.random() * klasy.length)];
        inputSila.value = Math.floor(Math.random() * 20) + 1;
        inputZrecznosc.value = Math.floor(Math.random() * 20) + 1;
        inputInteligencja.value = Math.floor(Math.random() * 20) + 1;

        zmienKolorObramowania();
    });

    btnReset.addEventListener('click', function () {
        inputImie.value = '';
        selectKlasa.value = 'Wojownik';
        inputSila.value = '10';
        inputZrecznosc.value = '10';
        inputInteligencja.value = '10';

        zmienKolorObramowania();
        if (characterCard) {
            characterCard.style.display = 'none';
        }
    });
</script>

</body>
</html>