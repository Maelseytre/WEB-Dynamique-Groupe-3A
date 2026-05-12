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