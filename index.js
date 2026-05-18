// ===== FILTER PILLS =====
var pills = document.querySelectorAll('.pill');
var eventCards = document.querySelectorAll('.event-card');
var resultCount = document.getElementById('resultCount');
var noResults = document.getElementById('noResults');
var eventsGrid = document.getElementById('eventsGrid');

var currentFilter = 'all';
var currentSearch = '';

for (var i = 0; i < pills.length; i++) {
  pills[i].addEventListener('click', function() {
    for (var j = 0; j < pills.length; j++) pills[j].classList.remove('active');
    this.classList.add('active');
    currentFilter = this.getAttribute('data-filter');
    filterEvents();
  });
}

// ===== RECHERCHE =====
var searchInput = document.getElementById('searchInput');
var searchBtn = document.getElementById('searchBtn');
var resetBtn = document.getElementById('resetSearch');

if (searchBtn) {
  searchBtn.addEventListener('click', function() {
    currentSearch = searchInput ? searchInput.value.trim().toLowerCase() : '';
    filterEvents();
  });
}

if (searchInput) {
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      currentSearch = searchInput.value.trim().toLowerCase();
      filterEvents();
    }
  });
}

if (resetBtn) {
  resetBtn.addEventListener('click', function() {
    if (searchInput) searchInput.value = '';
    currentSearch = '';
    currentFilter = 'all';
    for (var j = 0; j < pills.length; j++) pills[j].classList.remove('active');
    var allPill = document.querySelector('[data-filter="all"]');
    if (allPill) allPill.classList.add('active');
    filterEvents();
  });
}

function filterEvents() {
  var visible = 0;

  for (var i = 0; i < eventCards.length; i++) {
    var card = eventCards[i];
    var category = card.getAttribute('data-category') || '';
    var titleEl = card.querySelector('.event-card-title');
    var metaEl = card.querySelector('.event-card-meta');
    var title = titleEl ? titleEl.textContent.toLowerCase() : '';
    var meta = metaEl ? metaEl.textContent.toLowerCase() : '';

    var matchFilter = currentFilter === 'all' || category === currentFilter;
    var matchSearch = currentSearch === '' || title.indexOf(currentSearch) !== -1 || meta.indexOf(currentSearch) !== -1;

    if (matchFilter && matchSearch) {
      card.style.display = 'block';
      visible++;
    } else {
      card.style.display = 'none';
    }
  }

  if (resultCount) {
    resultCount.textContent = visible + ' événement' + (visible > 1 ? 's' : '') + ' trouvé' + (visible > 1 ? 's' : '');
  }

  if (visible === 0) {
    if (noResults) noResults.classList.remove('d-none');
    if (eventsGrid) eventsGrid.style.display = 'none';
  } else {
    if (noResults) noResults.classList.add('d-none');
    if (eventsGrid) eventsGrid.style.display = 'grid';
  }
}

// ===== TRI =====
var sortSelect = document.getElementById('sortSelect');
if (sortSelect) {
  sortSelect.addEventListener('change', function() {
    var cards = [];
    var allCards = eventsGrid ? eventsGrid.querySelectorAll('.event-card') : [];
    for (var i = 0; i < allCards.length; i++) {
      cards.push(allCards[i]);
    }

    if (sortSelect.value === 'name') {
      cards.sort(function(a, b) {
        var titleA = a.querySelector('.event-card-title');
        var titleB = b.querySelector('.event-card-title');
        var nameA = titleA ? titleA.textContent : '';
        var nameB = titleB ? titleB.textContent : '';
        return nameA.localeCompare(nameB);
      });
    }

    for (var i = 0; i < cards.length; i++) {
      eventsGrid.appendChild(cards[i]);
    }
  });
}