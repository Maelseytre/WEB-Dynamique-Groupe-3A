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
  showToast('Événement supprimé. Les inscrits ont été notifiés.', 'danger');
  eventToDelete = null;
}

function publishEvent(evtId) {
  showToast('Événement publié avec succès !', 'success');
}

// ===== PARTICIPANTS =====
function validatePresence(participantId) {
  const btn = event.target;
  btn.outerHTML = '<span class="badge badge-success">✅ Présent(e)</span>';
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
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--shadow-lg);min-width:280px;animation:slideIn 0.3s ease;';
  toast.textContent = type === 'success' ? `✅ ${message}` : `⚠️ ${message}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}