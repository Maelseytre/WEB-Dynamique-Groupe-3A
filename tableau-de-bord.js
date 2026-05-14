// ===== NAVBAR =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => mobileMenu.classList.toggle('open'));
document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

// ===== TABS =====
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');

tabBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    tabPanels.forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
  });
});

// ===== LOCALSTORAGE EVENTS =====
function getEvents() {
  return JSON.parse(localStorage.getItem('events') || '[]');
}

function saveEvents(events) {
  localStorage.setItem('events', JSON.stringify(events));
}

// ===== FORMATAGE DATE =====
function formatDateFr(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T00:00:00');
  const days = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];
  const months = ['Jan.', 'Fév.', 'Mar.', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sep.', 'Oct.', 'Nov.', 'Déc.'];
  return `${days[d.getDay()]} ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function categoryColor(cat) {
  const colors = {
    soiree: 'linear-gradient(135deg, #F5C542, #E0B030)',
    sport: 'linear-gradient(135deg, #34D399, #059669)',
    culture: 'linear-gradient(135deg, #60A5FA, #3B82F6)',
    conference: 'linear-gradient(135deg, #FBBF24, #D97706)'
  };
  return colors[cat] || 'linear-gradient(135deg, #8B8897, #6B7280)';
}

function renderEventRow(evt) {
  const pct = evt.capacite > 0 ? Math.min(100, Math.round((evt.inscrits / evt.capacite) * 100)) : 0;
  const isFull = evt.inscrits >= evt.capacite;
  const badgeClass = isFull ? 'badge-danger' : 'badge-success';
  const badgeText = isFull ? 'Complet' : 'Publié';

  return `
    <tr id="row-${evt.id}">
      <td>
        <div class="d-flex align-center gap-2">
          <div style="width:36px; height:36px; border-radius:6px; background:${categoryColor(evt.categorie)}; flex-shrink:0;"></div>
          <div>
            <p class="fw-semibold fs-sm">${evt.titre}</p>
            <p class="fs-xs text-muted">${evt.categorieLabel} · ${evt.associationLabel}</p>
          </div>
        </div>
      </td>
      <td class="fs-sm">${formatDateFr(evt.dateDebut)}</td>
      <td>
        <div>
          <span class="fw-semibold">${evt.inscrits}</span><span class="text-muted fs-sm">/${evt.capacite}</span>
          <div class="capacity-bar mt-1" style="height:4px; width:80px;">
            <div class="capacity-fill" style="width:${pct}%;"></div>
          </div>
        </div>
      </td>
      <td><span class="badge ${badgeClass}">${badgeText}</span></td>
      <td>
        <div class="d-flex gap-1">
          <button class="btn btn-outline btn-sm" onclick="viewParticipants('${evt.id}')">Inscrits</button>
          <button class="btn btn-ghost btn-sm" onclick="deleteEvent('${evt.id}')" style="color:var(--danger);">Supprimer</button>
        </div>
      </td>
    </tr>
  `;
}

function renderEvents() {
  const events = getEvents();
  const tbody = document.getElementById('eventsTableBody');

  if (!tbody) return;

  if (events.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center" style="padding:2.5rem; color:var(--text-muted);">
          Aucun événement créé pour l'instant.
          <a href="creer-evenement.html" style="color:var(--primary); margin-left:0.5rem;">Créer un événement →</a>
        </td>
      </tr>
    `;
  } else {
    tbody.innerHTML = events.map(renderEventRow).join('');
  }

  const today = new Date().toISOString().split('T')[0];
  const totalInscrits = events.reduce((sum, e) => sum + (e.inscrits || 0), 0);
  const totalPasses = events.filter(e => e.dateDebut && e.dateDebut < today).length;
  const totalActifs = events.filter(e => !e.dateDebut || e.dateDebut >= today).length;

  const statActifs = document.getElementById('statActifsDB');
  const statInscrits = document.getElementById('statInscritsDB');
  const statAttente = document.getElementById('statAttenteDB');
  const statPasses = document.getElementById('statPassesDB');

  if (statActifs) statActifs.textContent = totalActifs;
  if (statInscrits) statInscrits.textContent = totalInscrits;
  if (statAttente) statAttente.textContent = 0;
  if (statPasses) statPasses.textContent = totalPasses;
}

// ===== EVENTS ACTIONS =====
let eventToDelete = null;

function viewParticipants(evtId) {
  tabBtns.forEach(b => b.classList.remove('active'));
  tabPanels.forEach(p => p.classList.remove('active'));
  document.querySelector('[data-tab="inscrits"]').classList.add('active');
  document.getElementById('tab-inscrits').classList.add('active');
}

function editEvent(evtId) {
  window.location.href = 'creer-evenement.html';
}

function deleteEvent(evtId) {
  eventToDelete = evtId;
  document.getElementById('deleteModal').classList.add('open');
}

function confirmDelete() {
  document.getElementById('deleteModal').classList.remove('open');
  if (eventToDelete) {
    const events = getEvents().filter(e => e.id !== eventToDelete);
    saveEvents(events);
    renderEvents();
    showToast('Événement supprimé. Les inscrits ont été notifiés.', 'danger');
    eventToDelete = null;
  }
}

function publishEvent(evtId) {
  showToast('Événement publié avec succès !', 'success');
}

// ===== PARTICIPANTS =====
function validatePresence(participantId) {
  const btn = event.target;
  btn.outerHTML = '<span class="badge badge-success">Présent(e)</span>';
  showToast('Présence validée !', 'success');
}

function exportCSV() {
  const data = [
    ['Nom', 'Email', 'Événement', 'Date inscription', 'Statut'],
    ['Jean Dupont', 'jean.dupont@omnes.fr', 'Soirée Intégration', '12/05/2025', 'Confirmé'],
    ['Marie Laurent', 'marie.laurent@omnes.fr', 'Soirée Intégration', '13/05/2025', 'Confirmé'],
  ];
  const csv = data.map(row => row.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'inscrits_omnesevent.csv';
  a.click();
  URL.revokeObjectURL(url);
}

// ===== MODAL CLOSE =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

// ===== TOAST =====
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `alert alert-${type}`;
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--shadow-lg);min-width:280px;';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ===== INIT =====
renderEvents();

const welcomeEl = document.getElementById('dashWelcome');
if (welcomeEl) {
  const user = typeof getUser === 'function' ? getUser() : null;
  if (user) {
    const name = [user.prenom, user.nom].filter(Boolean).join(' ') || user.email;
    welcomeEl.innerHTML = `Bienvenue, <strong>${name}</strong>`;
  }
}
