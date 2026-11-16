'use strict';

document.addEventListener('DOMContentLoaded', () => {
  // --- 1. Licznik znaków opisu (limit: 300 znaków) ---
  const descriptionInput = document.getElementById('description');
  const charCounter = document.getElementById('char-counter');
  const MAX_CHARS = 300;

  function updateCharCounter() {
    if (!descriptionInput || !charCounter) return;
    const currentLength = descriptionInput.value.length;
    charCounter.textContent = `${currentLength} /${MAX_CHARS}`;

    charCounter.classList.remove('limit-near', 'limit-full');
    if (currentLength >= MAX_CHARS) {
      charCounter.classList.add('limit-full');
    } else if (currentLength >= 250) {
      charCounter.classList.add('limit-near');
    }
  }

  if (descriptionInput) {
    descriptionInput.addEventListener('input', updateCharCounter);
    updateCharCounter();
  }

  // --- 2. Walidacja formularza przed wysłaniem ---
  const ticketForm = document.getElementById('ticket-form');
  const roomInput = document.getElementById('room');
  const titleInput = document.getElementById('title');

  const roomError = document.getElementById('room-error');
  const titleError = document.getElementById('title-error');
  const descriptionError = document.getElementById('description-error');

  if (ticketForm) {
    ticketForm.addEventListener('submit', (event) => {
      let isValid = true;

      roomError.textContent = '';
      titleError.textContent = '';
      descriptionError.textContent = '';

      const roomVal = roomInput.value.trim();
      const titleVal = titleInput.value.trim();
      const descVal = descriptionInput.value.trim();

      if (roomVal === '') {
        roomError.textContent = 'Podaj numer sali lub lokalizację usterki.';
        isValid = false;
      }

      if (titleVal.length < 5) {
        titleError.textContent = 'Tytuł zgłoszenia musi zawierać co najmniej 5 znaków.';
        isValid = false;
      }

      if (descVal === '') {
        descriptionError.textContent = 'Uzupełnij opis usterki.';
        isValid = false;
      } else if (descriptionInput.value.length > MAX_CHARS) {
        descriptionError.textContent = `Opis nie może przekraczać ${MAX_CHARS} znaków.`;
        isValid = false;
      }

      if (!isValid) {
        event.preventDefault();
      }
    });
  }

  // --- 3. Filtrowanie i wyszukiwanie na żywo w drzewie DOM ---
  const searchInput = document.getElementById('search-input');
  const filterButtons = document.querySelectorAll('.filter-btn');
  const ticketCards = document.querySelectorAll('.ticket-card');
  const noResultsMsg = document.getElementById('no-results-msg');

  let activeStatus = 'all';

  function filterTickets() {
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    let visibleCount = 0;

    ticketCards.forEach((card) => {
      const cardStatus = card.dataset.status || '';
      const cardText = card.textContent.toLowerCase();

      const matchesStatus = activeStatus === 'all' || cardStatus === activeStatus;
      const matchesQuery = query === '' || cardText.includes(query);

      if (matchesStatus && matchesQuery) {
        card.classList.remove('hidden');
        visibleCount++;
      } else {
        card.classList.add('hidden');
      }
    });

    if (noResultsMsg && ticketCards.length > 0) {
      if (visibleCount === 0) {
        noResultsMsg.classList.remove('hidden');
      } else {
        noResultsMsg.classList.add('hidden');
      }
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterTickets);
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      filterButtons.forEach((btn) => btn.classList.remove('active'));
      button.classList.add('active');
      activeStatus = button.dataset.filter || 'all';
      filterTickets();
    });
  });

  // --- 4. Potwierdzenie usunięcia zgłoszenia ---
  const deleteForms = document.querySelectorAll('.delete-form');
  deleteForms.forEach((form) => {
    form.addEventListener('submit', (event) => {
      const confirmed = confirm('Czy na pewno chcesz trwale usunąć to zamknięte zgłoszenie?');
      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
});