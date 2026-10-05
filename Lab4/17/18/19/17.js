// Zadanie 17. fetch()
async function pobierzIDanesRenderuj() {
  const lista = document.querySelector("#lista");

  try {
  
    const response = await fetch("api.php");


    if (!response.ok) {
      throw new Error(`Błąd HTTP! Status: ${response.status}`);
    }


    const result = await response.json();

  
    lista.innerHTML = "";

    const dane = Array.isArray(result) ? result : result.data;

    dane.forEach((item) => {
      const li = document.createElement("li");
      li.textContent = item.nazwa ? `${item.nazwa} - ${item.cena} zł` : JSON.stringify(item);
      lista.appendChild(li);
    });

  } catch (error) {

    console.error("Błąd pobierania danych:", error.message);
    
    if (lista) {
      lista.innerHTML = `<li class="blad">Nie udało się pobrać danych.</li>`;
    }
  }
}

// Wywołanie funkcji
pobierzIDanesRenderuj();