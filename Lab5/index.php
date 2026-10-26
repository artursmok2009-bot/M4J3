<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Zadanie 1 -->
    <title>Formularz kontaktowy</title>
    <!-- Zadanie 9 -->
    <link rel="stylesheet" href="style.css">
    <!-- Zadanie 11 -->
    <script src="script.js" defer></script>
</head>
<body>

    <div class="form-container">
        <h1>Formularz kontaktowy</h1>

        <!-- Zadanie dodatkowe: Licznik prób wysłania -->
        <div class="counter-box">
            Próby wysłania – Udane: <strong id="udaneCount">0</strong> | Nieudane: <strong id="nieudaneCount">0</strong>
        </div>

        <!-- Zadanie 1, 21 -->
        <form action="odbierz.php" method="post" id="formularzKontaktowy" novalidate>
            
            <!-- Zadanie 2, 6 -->
            <div class="form-group">
                <label for="imie">Imię:</label>
                <input type="text" id="imie" name="imie" required placeholder="Wpisz swoje imię">
                <span class="error-msg" id="imieError"></span>
            </div>

            <!-- Zadanie 3, 6 -->
            <div class="form-group">
                <label for="email">Adres e-mail:</label>
                <input type="email" id="email" name="email" required placeholder="jan.kowalski@example.com">
                <span class="error-msg" id="emailError"></span>
            </div>

            <!-- Zadanie dodatkowe: Pole telefonu -->
            <div class="form-group">
                <label for="telefon">Numer telefonu (9 cyfr):</label>
                <input type="tel" id="telefon" name="telefon" placeholder="123456789">
                <span class="error-msg" id="telefonError"></span>
            </div>

            <!-- Zadanie dodatkowe: Pole hasła + Pokaż/ukryj -->
            <div class="form-group">
                <label for="haslo">Hasło:</label>
                <div class="password-wrapper">
                    <input type="password" id="haslo" name="haslo" placeholder="Wpisz hasło">
                    <button type="button" id="btnToggleHaslo" class="btn-toggle">Pokaż</button>
                </div>
            </div>

            <!-- Zadanie 7 -->
            <div class="form-group">
                <label for="temat">Temat wiadomości:</label>
                <select id="temat" name="temat">
                    <option value="Zapytanie ogólne">Zapytanie ogólne</option>
                    <option value="Wsparcie techniczne">Wsparcie techniczne</option>
                    <option value="Współpraca">Współpraca</option>
                </select>
            </div>

            <!-- Zadanie dodatkowe: Data -->
            <div class="form-group">
                <label for="data_kontaktu">Preferowana data kontaktu:</label>
                <input type="date" id="data_kontaktu" name="data_kontaktu">
            </div>

            <!-- Zadanie 4, 6, 15 -->
            <div class="form-group">
                <label for="wiadomosc">Treść wiadomości:</label>
                <textarea id="wiadomosc" name="wiadomosc" rows="3" maxlength="200" required placeholder="Napisz swoją wiadomość..."></textarea>
                <!-- Zadanie 14, 15 -->
                <div class="char-counter">
                    Liczba znaków: <span id="charCount">0</span> | Pozostało: <span id="charRemaining">200</span>
                </div>
                <span class="error-msg" id="wiadomoscError"></span>
            </div>

            <!-- Zadanie 8 -->
            <div class="form-group checkbox-group">
                <input type="checkbox" id="zgoda" name="zgoda" value="tak" required>
                <label for="zgoda">Akceptuję regulamin</label>
                <span class="error-msg" id="zgodaError"></span>
            </div>

            <!-- Zadanie 12 -->
            <div class="form-group">
                <button type="button" id="btnTest" class="btn-secondary">Test: Odczytaj imię</button>
            </div>

            <!-- Zadanie 5 -->
            <div class="button-group">
                <button type="submit" id="btnSubmit">Wyślij</button>
                <button type="reset" id="btnReset" class="btn-secondary">Wyczyść formularz</button>
            </div>
        </form>

        <!-- Zadanie 13 -->
        <div id="powitanie" class="powitanie-box"></div>
    </div>

</body>
</html>