document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contactForm");
    const imieInput = document.getElementById("imie");
    const emailInput = document.getElementById("email");
    const wiadomoscInput = document.getElementById("wiadomosc");
    
    const imieError = document.getElementById("imieError");
    const emailError = document.getElementById("emailError");
    const wiadomoscError = document.getElementById("wiadomoscError");
    const statusMessage = document.getElementById("statusMessage");
    const submitBtn = document.getElementById("submitBtn");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        resetErrors();

        let hasError = false;

        const imieVal = imieInput.value.trim();
        if (imieVal === "") {
            showFieldError(imieInput, imieError, "Podaj swoje imię.");
            hasError = true;
        } else if (imieVal.length < 2) {
            showFieldError(imieInput, imieError, "Imię musi mieć co najmniej 2 znaki.");
            hasError = true;
        }

        const emailVal = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailVal === "") {
            showFieldError(emailInput, emailError, "Podaj adres e-mail.");
            hasError = true;
        } else if (!emailRegex.test(emailVal)) {
            showFieldError(emailInput, emailError, "Wpisz poprawny adres e-mail.");
            hasError = true;
        }

        const wiadomoscVal = wiadomoscInput.value.trim();
        if (wiadomoscVal === "") {
            showFieldError(wiadomoscInput, wiadomoscError, "Treść wiadomości nie może być pusta.");
            hasError = true;
        }

        if (hasError) return;

        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.textContent = "Wysyłanie...";

        try {
            const response = await fetch("odbierz.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showStatus(result.message, "success");
                form.reset();
                resetErrors();
            } else {
                showStatus(result.errors.join("<br>"), "error");
            }
        } catch (error) {
            console.error("Błąd przesyłania:", error);
            showStatus("Wystąpił błąd połączenia z serwerem.", "error");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Wyślij wiadomość";
        }
    });

    // Obsługa zdarzenia wyczyszczenia formularza (reset)
    form.addEventListener("reset", () => {
        resetErrors();
    });

    function showFieldError(input, errorSpan, message) {
        input.classList.add("invalid");
        errorSpan.textContent = message;
    }

    function resetErrors() {
        [imieInput, emailInput, wiadomoscInput].forEach(input => input.classList.remove("invalid"));
        [imieError, emailError, wiadomoscError].forEach(span => span.textContent = "");
        statusMessage.className = "status-msg";
        statusMessage.style.display = "none";
        statusMessage.innerHTML = "";
    }

    function showStatus(message, type) {
        statusMessage.className = `status-msg ${type}`;
        statusMessage.innerHTML = message;
    }
});