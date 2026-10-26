// Zadanie 11
console.log("Skrypt działa");

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formularzKontaktowy");
    const imieInput = document.getElementById("imie");
    const emailInput = document.getElementById("email");
    const telefonInput = document.getElementById("telefon");
    const hasloInput = document.getElementById("haslo");
    const btnToggleHaslo = document.getElementById("btnToggleHaslo");
    const wiadomoscInput = document.getElementById("wiadomosc");
    const zgodaInput = document.getElementById("zgoda");
    
    const charCountSpan = document.getElementById("charCount");
    const charRemainingSpan = document.getElementById("charRemaining");
    const powitanieBox = document.getElementById("powitanie");
    const btnTest = document.getElementById("btnTest");

    const imieError = document.getElementById("imieError");
    const emailError = document.getElementById("emailError");
    const telefonError = document.getElementById("telefonError");
    const wiadomoscError = document.getElementById("wiadomoscError");
    const zgodaError = document.getElementById("zgodaError");

    const udaneSpan = document.getElementById("udaneCount");
    const nieudaneSpan = document.getElementById("nieudaneCount");

    const maxWiadomoscLen = 200;

    // Zadanie dodatkowe: Licznik prób zapisywany w sessionStorage
    let udaneProby = parseInt(sessionStorage.getItem("udaneProby") || "0");
    let nieudaneProby = parseInt(sessionStorage.getItem("nieudaneProby") || "0");
    udaneSpan.textContent = udaneProby;
    nieudaneSpan.textContent = nieudaneProby;

    // Zadanie dodatkowe: Pokaż / ukryj hasło
    btnToggleHaslo.addEventListener("click", () => {
        if (hasloInput.type === "password") {
            hasloInput.type = "text";
            btnToggleHaslo.textContent = "Ukryj";
        } else {
            hasloInput.type = "password";
            btnToggleHaslo.textContent = "Pokaż";
        }
    });

    // Zadanie 12
    btnTest.addEventListener("click", () => {
        const wartoscImienia = imieInput.value.trim();
        if (wartoscImienia) {
            alert(`Pobrane imię: ${wartoscImienia}`);
        } else {
            alert("Pole imię jest puste!");
        }
    });

    // Zadanie 13
    imieInput.addEventListener("input", () => {
        const imie = imieInput.value.trim();
        if (imie.length > 0) {
            powitanieBox.style.display = "block";
            powitanieBox.textContent = `Witaj, ${imie}!`;
        } else {
            powitanieBox.style.display = "none";
            powitanieBox.textContent = "";
        }
    });

    // Zadanie 14, 15
    wiadomoscInput.addEventListener("input", () => {
        const dlugosc = wiadomoscInput.value.length;
        const pozostalo = maxWiadomoscLen - dlugosc;

        charCountSpan.textContent = dlugosc;
        charRemainingSpan.textContent = pozostalo;
    });

    // Zadanie 16, 17, 18, 19, 20
    form.addEventListener("submit", (e) => {
        let maBledy = false;

        wyczyscBledy();

        // Zadanie 16, 17
        const imieVal = imieInput.value.trim();
        if (imieVal === "") {
            pokazBlad(imieInput, imieError, "Pole imię nie może być puste.");
            maBledy = true;
        } else if (imieVal.length < 2) {
            pokazBlad(imieInput, imieError, "Imię musi zawierać co najmniej 2 znaki.");
            maBledy = true;
        }

        // Zadanie 18
        if (!emailInput.value.trim()) {
            pokazBlad(emailInput, emailError, "Pole e-mail jest wymagane.");
            maBledy = true;
        } else if (!emailInput.checkValidity()) {
            pokazBlad(emailInput, emailError, "Proszę podać poprawny adres e-mail.");
            maBledy = true;
        }

        // Zadanie dodatkowe: Sprawdzenie długości telefonu (jeśli wpisano)
        const telefonVal = telefonInput.value.trim();
        if (telefonVal !== "" && (telefonVal.length !== 9 || isNaN(telefonVal))) {
            pokazBlad(telefonInput, telefonError, "Numer telefonu musi składać się z 9 cyfr.");
            maBledy = true;
        }

        if (wiadomoscInput.value.trim() === "") {
            pokazBlad(wiadomoscInput, wiadomoscError, "Treść wiadomości nie może być pusta.");
            maBledy = true;
        }

        // Zadanie 19
        if (!zgodaInput.checked) {
            pokazBlad(zgodaInput, zgodaError, "Musisz zaakceptować regulamin.");
            maBledy = true;
        }

        // Zadanie 20 & Licznik prób
        if (maBledy) {
            e.preventDefault();
            nieudaneProby++;
            sessionStorage.setItem("nieudaneProby", nieudaneProby);
            nieudaneSpan.textContent = nieudaneProby;
        } else {
            udaneProby++;
            sessionStorage.setItem("udaneProby", udaneProby);
            udaneSpan.textContent = udaneProby;
        }
    });

    function pokazBlad(inputEl, errorEl, trescBledy) {
        errorEl.textContent = trescBledy;
        inputEl.classList.add("invalid");
    }

    function wyczyscBledy() {
        const errors = document.querySelectorAll(".error-msg");
        errors.forEach(el => el.textContent = "");

        const inputs = document.querySelectorAll("input, textarea, select");
        inputs.forEach(el => el.classList.remove("invalid"));
    }

    form.addEventListener("reset", () => {
        wyczyscBledy();
        powitanieBox.style.display = "none";
        charCountSpan.textContent = "0";
        charRemainingSpan.textContent = maxWiadomoscLen;
    });
});