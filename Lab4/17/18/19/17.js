// Zadanie 17. fetch()
async function pobierzIDanesRenderuj() {
  const lista = document.querySelector("#lista");

  try {
    // Pobranie danych z pliku PHP za pomocą fetch() i async/await
    const response = await fetch("api.php");

    // Sprawdzenie response.ok
    if (!response.ok) {
      throw new Error(`Błąd HTTP! Status: ${response.status}`);
    }

    // Odczytanie JSON
    const result = await response.json();

    // Wyczyszczenie listy i wyrenderowanie elementów
    lista.innerHTML = "";
    
    // Założenie: API zwraca obiekt z tablicą danych (np. result.data) lub samą tablicę
    const dane = Array.isArray(result) ? result : result.data;

    dane.forEach((item) => {
      const li = document.createElement("li");
      li.textContent = item.nazwa ? `${item.nazwa} - ${item.cena} zł` : JSON.stringify(item);
      lista.appendChild(li);
    });

  } catch (error) {
    // Obsługa błędu w try/catch
    console.error("Błąd pobierania danych:", error.message);
    
    if (lista) {
      lista.innerHTML = `<li class="blad">Nie udało się pobrać danych.</li>`;
    }
  }
}

// Wywołanie funkcji
pobierzIDanesRenderuj();