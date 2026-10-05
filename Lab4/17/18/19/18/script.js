// Inicjalizacja tablicy zadań danymi z PHP
let zadania = Array.isArray(danePoczatkowe) ? [...danePoczatkowe] : [];

const listaEl = document.querySelector("#lista");
const inputSzukaj = document.querySelector("#szukaj");


function renderujListe(listaDoWyswietlenia) {
  listaEl.innerHTML = "";

  listaDoWyswietlenia.forEach((zadanie) => {
    const li = document.createElement("li");
    li.dataset.id = zadanie.id;
    
    if (zadanie.wykonane) {
      li.classList.add("wykonane");
    }

    const spanTekst = document.createElement("span");
    spanTekst.textContent = zadanie.nazwa;

    const btnUsun = document.createElement("button");
    btnUsun.textContent = "Usuń";
    btnUsun.className = "btn-usun";

    li.appendChild(spanTekst);
    li.appendChild(btnUsun);
    listaEl.appendChild(li);
  });
}


listaEl.addEventListener("click", (e) => {
  const li = e.target.closest("li");
  if (!li) return;

  const id = Number(li.dataset.id);

 
  if (e.target.classList.contains("btn-usun")) {
    e.stopPropagation();
    zadania = zadania.filter((t) => t.id !== id);
    renderujListe(filtrujZadania(inputSzukaj.value));
  } 
 
  else {
    const zadanie = zadania.find((t) => t.id === id);
    if (zadanie) {
      zadanie.wykonane = !zadanie.wykonane;
      li.classList.toggle("wykonane");
    }
  }
});


function filtrujZadania(fraza) {
  const czystaFraza = fraza.toLowerCase().trim();
  return zadania.filter((item) =>
    item.nazwa.toLowerCase().includes(czystaFraza)
  );
}

inputSzukaj.addEventListener("input", (e) => {
  const przefiltrowane = filtrujZadania(e.target.value);
  renderujListe(przefiltrowane);
});

// Pierwsze renderowanie po załadowaniu strony
renderujListe(zadania);