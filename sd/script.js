document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contactForm");
    const imieInput = document.getElementById("imie");
    const nazwiskoInput = document.getElementById("nazwisko");
    const emailInput = document.getElementById("email");
    
    const imieError = document.getElementById("imieError");
    const nazwiskoError = document.getElementById("nazwiskoError");
    const emailError = document.getElementById("emailError");
    const powitanieDiv = document.getElementById("powitanie");

    // Obsługa wysyłania formularza (przycisk submit)
    form.addEventListener("submit", (e) => {
        e.preventDefault(); // Zatrzymuje domyślne wysłanie, aby sprawdzić walidację

        let isValid = true;

        // Czyszczenie poprzednich komunikatów o błędach i powitań
        imieError.textContent = "";
        nazwiskoError.textContent = "";
        emailError.textContent = "";
        powitanieDiv.textContent = "";

        // Walidacja imienia
        if (imieInput.value.trim().length < 2) {
            imieError.textContent = "Imię musi mieć co najmniej 2 znaki.";
            isValid = false;
        }

        // Walidacja nazwiska
        if (nazwiskoInput.value.trim().length < 2) {
            nazwiskoError.textContent = "Nazwisko musi mieć co najmniej 2 znaki.";
            isValid = false;
        }

        // Walidacja emaila (proste sprawdzenie czy zawiera @)
        if (!emailInput.value.includes("@") || emailInput.value.trim().length < 2) {
            emailError.textContent = "Wprowadź poprawny adres e-mail.";
            isValid = false;
        }

        // Jeśli wszystko jest poprawne
        if (isValid) {
            const imie = imieInput.value.trim();
            const nazwisko = nazwiskoInput.value.trim();
            
            powitanieDiv.textContent = `Witaj, ${imie} ${nazwisko}! Formularz został zweryfikowany pomyślnie.`;
            
            // Jeśli chcesz, aby formularz fizycznie przesłał dane do "odbierz.php" po sukcesie,
            // usuń powyższą linijkę z powitaniem i odkomentuj poniższą:
            // form.submit();
        }
    });

    // Obsługa czyszczenia formularza (przycisk reset)
    form.addEventListener("reset", () => {
        // Czyszczenie komunikatów i powitania po zresetowaniu formularza
        imieError.textContent = "";
        nazwiskoError.textContent = "";
        emailError.textContent = "";
        powitanieDiv.textContent = "";
    });
});