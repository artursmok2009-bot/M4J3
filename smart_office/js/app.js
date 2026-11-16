'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const API_URL = 'api/reservations.php';

  // Elementy DOM
  const themeToggleBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');
  const themeLabel = document.getElementById('theme-label');

  const filterDateInput = document.getElementById('filter-date');
  const filterStartInput = document.getElementById('filter-start');
  const filterEndInput = document.getElementById('filter-end');
  const filterStatusMsg = document.getElementById('filter-status-msg');

  const officeMap = document.getElementById('office-map');
  const reservationsBody = document.getElementById('reservations-body');
  const reservationsCount = document.getElementById('reservations-count');

  const icsBanner = document.getElementById('ics-banner');
  const icsBannerText = document.getElementById('ics-banner-text');
  const icsDownloadLink = document.getElementById('ics-download-link');
  const icsBannerClose = document.getElementById('ics-banner-close');

  const modal = document.getElementById('booking-modal');
  const modalCloseBtn = document.getElementById('modal-close');
  const modalCancelBtn = document.getElementById('modal-cancel');
  const bookingForm = document.getElementById('booking-form');
  const modalResourceId = document.getElementById('modal-resource-id');
  const modalResourceName = document.getElementById('modal-resource-name');
  const modalTimeRange = document.getElementById('modal-time-range');
  const userNameInput = document.getElementById('user-name');
  const userNameError = document.getElementById('user-name-error');

  const toastContainer = document.getElementById('toast-container');

  // Stan aplikacji
  let currentResources = [];
  let selectedResourceId = null;

  // --- 1. Obsługa motywu Light / Dark Mode (localStorage) ---
  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      themeIcon.textContent = '☀️';
      themeLabel.textContent = 'Tryb jasny';
    } else {
      document.documentElement.removeAttribute('data-theme');
      themeIcon.textContent = '🌙';
      themeLabel.textContent = 'Tryb ciemny';
    }
  }

  const savedTheme = localStorage.getItem('smart_office_theme') || 'light';
  applyTheme(savedTheme);

  themeToggleBtn.addEventListener('click', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const nextTheme = isDark ? 'light' : 'dark';
    localStorage.setItem('smart_office_theme', nextTheme);
    applyTheme(nextTheme);
  });

  // --- 2. Powiadomienia Toast ---
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('fade-out');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }, 3500);
  }

  // --- 3. Pomocnicze funkcje daty i bezpieczeństwa ---
  function formatDateYMD(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function initDefaultDateTime() {
    const now = new Date();
    const todayStr = formatDateYMD(now);
    filterDateInput.min = todayStr;

    let defaultDate = new Date(now);
    let startHour = now.getHours() + 1;

    if (startHour >= 22) {
      defaultDate.setDate(defaultDate.getDate() + 1);
      startHour = 9;
    }

    const endHour = startHour + 1;
    filterDateInput.value = formatDateYMD(defaultDate);
    filterStartInput.value = `${String(startHour).padStart(2, '0')}:00`;
    filterEndInput.value = `${String(endHour).padStart(2, '0')}:00`;
  }

  function validateFilterRange() {
    const dateVal = filterDateInput.value;
    const startVal = filterStartInput.value;
    const endVal = filterEndInput.value;

    if (!dateVal || !startVal || !endVal) {
      filterStatusMsg.textContent = 'Uzupełnij datę oraz godziny rozpoczęcia i zakończenia.';
      filterStatusMsg.classList.add('error');
      return null;
    }

    const startDateTime = new Date(`${dateVal}T${startVal}`);
    const endDateTime = new Date(`${dateVal}T${endVal}`);

    if (endDateTime <= startDateTime) {
      filterStatusMsg.textContent = 'Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.';
      filterStatusMsg.classList.add('error');
      return null;
    }

    filterStatusMsg.textContent = `Wybrany przedział: ${dateVal}, ${startVal} – ${endVal}`;
    filterStatusMsg.classList.remove('error');

    return {
      start: `${dateVal} ${startVal}`,
      end: `${dateVal} ${endVal}`,
      startDateTime,
      endDateTime
    };
  }

  // --- 4. Pobieranie dostępności i aktywnych rezerwacji (GET) ---
  async function loadAvailability() {
    const range = validateFilterRange();
    if (!range) {
      officeMap.innerHTML = '<p class="hint">Popraw przedział czasowy powyżej, aby wyświetlić dostępność stanowisk.</p>';
      return;
    }

    const params = new URLSearchParams({
      start: range.start,
      end: range.end
    });

    try {
      const response = await fetch(`${API_URL}?${params.toString()}`);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Nie udało się pobrać danych z serwera.');
      }

      currentResources = data.resources || [];
      renderOfficeMap(currentResources);
      renderReservationsTable(data.reservations || []);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // --- 5. Renderowanie rzutu biura (CSS Grid) ---
  function renderOfficeMap(resources) {
    officeMap.innerHTML = '';

    if (resources.length === 0) {
      officeMap.innerHTML = '<p class="hint">Brak zdefiniowanych stanowisk w bazie danych.</p>';
      return;
    }

    resources.forEach((item) => {
      const card = document.createElement('div');
      const isRoom = item.type === 'room';
      const isOccupied = Boolean(item.is_occupied);
      const isSelected = selectedResourceId === item.id;

      card.className = [
        'resource-card',
        isRoom ? 'type-room' : 'type-desk',
        isOccupied ? 'occupied' : 'free',
        isSelected ? 'selected' : ''
      ].filter(Boolean).join(' ');

      card.dataset.id = item.id;
      card.setAttribute('role', 'button');
      card.setAttribute('tabindex', isOccupied ? '-1' : '0');

      const typeLabel = isRoom ? '🏢 Sala konferencyjna' : '💻 Biurko';
      const statusLabel = isOccupied ? 'Zajęte' : 'Wolne';

      card.innerHTML = `
        <div class="resource-top">
          <span class="resource-type-tag">${typeLabel}</span>
          <span>👥 ${item.capacity} os.</span>
        </div>
        <div class="resource-name">${escapeHtml(item.name)}</div>
        <div class="resource-meta">
          <span>ID #${item.id}</span>
          <span class="status-pill">${statusLabel}</span>
        </div>
      `;

      card.addEventListener('click', () => {
        if (isOccupied) {
          showToast('To stanowisko jest już zajęte w wybranym terminie.', 'error');
          return;
        }
        openBookingModal(item);
      });

      card.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && !isOccupied) {
          e.preventDefault();
          openBookingModal(item);
        }
      });

      officeMap.appendChild(card);
    });
  }

  // --- 6. Renderowanie listy aktywnych rezerwacji ---
  function renderReservationsTable(reservations) {
    reservationsCount.textContent = String(reservations.length);
    reservationsBody.innerHTML = '';

    if (reservations.length === 0) {
      reservationsBody.innerHTML = `
        <tr>
          <td colspan="6" class="empty-row">Brak aktywnych rezerwacji w systemie.</td>
        </tr>
      `;
      return;
    }

    reservations.forEach((res) => {
      const tr = document.createElement('tr');
      const typeText = res.resource_type === 'room' ? 'Sala konferencyjna' : 'Biurko';

      tr.innerHTML = `
        <td><strong>${escapeHtml(res.resource_name)}</strong></td>
        <td>${typeText}</td>
        <td>${res.user_name}</td>
        <td>${escapeHtml(res.start_time)}</td>
        <td>${escapeHtml(res.end_time)}</td>
        <td>
          <div class="action-group">
            <a href="api/ics.php?id=${res.id}" class="btn btn-ics-sm" title="Pobierz plik kalendarza .ics">📅 .ics</a>
            <button type="button" class="btn btn-danger cancel-res-btn" data-id="${res.id}">Anuluj</button>
          </div>
        </td>
      `;

      reservationsBody.appendChild(tr);
    });
  }

  // --- 7. Okno modalne rezerwacji i wysyłka POST ---
  function openBookingModal(resource) {
    const range = validateFilterRange();
    if (!range) {
      showToast('Wybierz poprawny termin przed dokonaniem rezerwacji.', 'error');
      return;
    }

    selectedResourceId = resource.id;
    renderOfficeMap(currentResources);

    modalResourceId.value = String(resource.id);
    modalResourceName.textContent = `${resource.name} (${resource.type === 'room' ? 'Sala konferencyjna' : 'Biurko'})`;
    modalTimeRange.textContent = `${range.start} – ${range.end}`;
    userNameInput.value = '';
    userNameError.textContent = '';

    modal.hidden = false;
    userNameInput.focus();
  }

  function closeBookingModal() {
    modal.hidden = true;
    selectedResourceId = null;
    renderOfficeMap(currentResources);
  }

  modalCloseBtn.addEventListener('click', closeBookingModal);
  modalCancelBtn.addEventListener('click', closeBookingModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeBookingModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) {
      closeBookingModal();
    }
  });

  bookingForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    userNameError.textContent = '';

    const range = validateFilterRange();
    if (!range) {
      showToast('Nieprawidłowy zakres daty lub godzin.', 'error');
      return;
    }

    // Walidacja daty z przeszłości po stronie klienta
    if (range.startDateTime < new Date(Date.now() - 60000)) {
      showToast('Nie można zarezerwować terminu z przeszłości.', 'error');
      return;
    }

    const userName = userNameInput.value.trim();
    if (userName.length < 3) {
      userNameError.textContent = 'Wpisz co najmniej 3 znaki.';
      userNameInput.focus();
      return;
    }

    const payload = {
      resource_id: Number(modalResourceId.value),
      user_name: userName,
      start_time: range.start,
      end_time: range.end
    };

    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (!response.ok) {
        showToast(data.message || 'Błąd podczas zapisu rezerwacji.', 'error');
        await loadAvailability();
        return;
      }

      closeBookingModal();
      showToast(data.message || 'Rezerwacja została utworzona!', 'success');

      // Wyświetlenie banera z pobieraniem pliku .ics
      if (data.id) {
        icsBannerText.textContent = `Zarezerwowano: ${data.resource_name} (${range.start} – ${range.end}).`;
        icsDownloadLink.href = `api/ics.php?id=${data.id}`;
        icsBanner.classList.remove('hidden');
      }

      await loadAvailability();
    } catch (err) {
      showToast('Błąd komunikacji z serwerem.', 'error');
    }
  });

  icsBannerClose.addEventListener('click', () => {
    icsBanner.classList.add('hidden');
  });

  // --- 8. Anulowanie rezerwacji (DELETE) ---
  reservationsBody.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cancel-res-btn');
    if (!btn) return;

    const reservationId = Number(btn.dataset.id);
    if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) {
      return;
    }

    try {
      const response = await fetch(`${API_URL}?id=${reservationId}`, {
        method: 'DELETE'
      });
      const data = await response.json();

      if (!response.ok) {
        showToast(data.message || 'Nie udało się anulować rezerwacji.', 'error');
        return;
      }

      showToast(data.message || 'Rezerwacja została anulowana.', 'success');
      await loadAvailability();
    } catch (err) {
      showToast('Błąd komunikacji z serwerem.', 'error');
    }
  });

  // --- 9. Nasłuchiwanie zmian filtrów i start aplikacji ---
  [filterDateInput, filterStartInput, filterEndInput].forEach((input) => {
    input.addEventListener('change', () => {
      selectedResourceId = null;
      loadAvailability();
    });
  });

  initDefaultDateTime();
  loadAvailability();
});