<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-projekt: Formularz kontaktowy Fetch API</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

    <div class="form-card">
        <h2>Skontaktuj się z nami</h2>
        
        <div id="statusMessage" class="status-msg"></div>

        <form id="contactForm" novalidate>
            <div class="form-group">
                <label for="imie">Imię:</label>
                <input type="text" id="imie" name="imie" placeholder="Wpisz swoje imię">
                <span class="error-msg" id="imieError"></span>
            </div>

            <div class="form-group">
                <label for="email">Adres e-mail:</label>
                <input type="email" id="email" name="email" placeholder="jan@example.com">
                <span class="error-msg" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="wiadomosc">Wiadomość:</label>
                <textarea id="wiadomosc" name="wiadomosc" rows="4" placeholder="Wpisz treść wiadomości..."></textarea>
                <span class="error-msg" id="wiadomoscError"></span>
            </div>

            <!-- Grupa przycisków wysyłania i czyszczenia -->
            <div class="button-group">
                <button type="submit" id="submitBtn">Wyślij wiadomość</button>
                <button type="reset" id="resetBtn" class="btn-secondary">Wyczyść formularz</button>
            </div>
        </form>
    </div>

</body>
</html>