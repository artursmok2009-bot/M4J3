<?php
// PHP dostarcza dane początkowe
$poczatekZadan = [
    ["id" => 1, "nazwa" => "Nauczyć się podstaw PHP", "wykonane" => true],
    ["id" => 2, "nazwa" => "Napełnić miskę psa", "wykonane" => false],
    ["id" => 3, "nazwa" => "Zrobić powtórkę z JS", "wykonane" => false],
    ["id" => 4, "nazwa" => "Zaprojektować styl CSS", "wykonane" => false]
];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Lista Zadań</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <h1>Moja Lista Zadań</h1>

  <div class="controls">
    <input type="text" id="szukaj" class="input-szukaj" placeholder="Szukaj zadania...">
  </div>

  <ul id="lista" class="lista-zadan"></ul>

  <script>
    const danePoczatkowe = <?= json_encode($poczatekZadan, JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="script.js"></script>
</body>
</html>