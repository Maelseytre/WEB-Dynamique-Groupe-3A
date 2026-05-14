// ===== NAVBAR HAMBURGER =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');

hamburger.addEventListener('click', () => {
  mobileMenu.classList.toggle('open');
});

document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

// ===== FILTER PILLS =====
const pills = document.querySelectorAll('.pill');
const eventCards = document.querySelectorAll('.event-card');
const resultCount = document.getElementById('resultCount');
const noResults = document.getElementById('noResults');
const eventsGrid = document.getElementById('eventsGrid');

let currentFilter = 'all';
let currentSearch = '';

pills.forEach(pill => {
  pill.addEventListener('click', () => {
    pills.forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    currentFilter = pill.dataset.filter;
    filterEvents();
  });
});

// ===== SEARCH =====
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const resetBtn = document.getElementById('resetSearch');

searchBtn.addEventListener('click', () => {
  currentSearch = searchInput.value.trim().toLowerCase();
  filterEvents();
});

searchInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    currentSearch = searchInput.value.trim().toLowerCase();
    filterEvents();
  }
});

if (resetBtn) {
  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    currentSearch = '';
    currentFilter = 'all';
    pills.forEach(p => p.classList.remove('active'));
    document.querySelector('[data-filter="all"]').classList.add('active');
    filterEvents();
  });
}

function filterEvents() {
  let visible = 0;

  eventCards.forEach(card => {
    const category = card.dataset.category || '';
    const title = card.querySelector('.event-card-title')?.textContent.toLowerCase() || '';
    const meta = card.querySelector('.event-card-meta')?.textContent.toLowerCase() || '';

    const matchFilter = currentFilter === 'all' || category === currentFilter;
    const matchSearch = currentSearch === '' || title.includes(currentSearch) || meta.includes(currentSearch);

    if (matchFilter && matchSearch) {
      card.style.display = 'block';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  resultCount.textContent = `${visible} événement${visible > 1 ? 's' : ''} trouvé${visible > 1 ? 's' : ''}`;

  if (visible === 0) {
    noResults.classList.remove('d-none');
    eventsGrid.style.display = 'none';
  } else {
    noResults.classList.add('d-none');
    eventsGrid.style.display = 'grid';
  }
}

// ===== EVENEMENTS CREES PAR LES UTILISATEURS =====
function renderUserEvents() {
  const events = JSON.parse(localStorage.getItem('events') || '[]');
  if (events.length === 0) return;

  events.forEach(evt => {
    const card = document.createElement('a');
    card.href = 'evenement-detail.html';
    card.className = 'event-card';
    card.dataset.category = evt.categorie;

    const pct = evt.capacite > 0 ? Math.min(100, Math.round((evt.inscrits / evt.capacite) * 100)) : 0;
    const dateLabel = evt.dateDebut
      ? new Date(evt.dateDebut + 'T00:00:00').toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
      : '';

    card.innerHTML = `
      <div class="event-card-image" style="${evt.image ? '' : 'background:var(--bg-elevated);'}">
        ${evt.image ? `<img src="${evt.image}" alt="${evt.titre}">` : ''}
      </div>
      <div class="event-card-body">
        <span class="event-card-category category-${evt.categorie}">${evt.categorieLabel}</span>
        <h3 class="event-card-title">${evt.titre}</h3>
        <div class="event-card-meta">
          <span>${dateLabel}</span>
          <span>${evt.lieu}</span>
          <span>${evt.associationLabel}</span>
        </div>
        <div class="event-card-footer">
          <div class="capacity-bar"><div class="capacity-fill" style="width:${pct}%;"></div></div>
          <span class="capacity-text">${evt.inscrits}/${evt.capacite} places</span>
        </div>
      </div>
    `;

    eventsGrid.appendChild(card);
  });

  const total = eventsGrid.querySelectorAll('.event-card').length;
  resultCount.textContent = `${total} événement${total > 1 ? 's' : ''} trouvé${total > 1 ? 's' : ''}`;
}

renderUserEvents();

// ===== SORT =====
const sortSelect = document.getElementById('sortSelect');
sortSelect.addEventListener('change', () => {
  const cards = Array.from(eventCards);
  const sorted = cards.sort((a, b) => {
    if (sortSelect.value === 'name') {
      const nameA = a.querySelector('.event-card-title')?.textContent || '';
      const nameB = b.querySelector('.event-card-title')?.textContent || '';
      return nameA.localeCompare(nameB);
    }
    return 0;
  });
  sorted.forEach(card => eventsGrid.appendChild(card));
});