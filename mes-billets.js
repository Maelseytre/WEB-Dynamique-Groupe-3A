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
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
  });
});

// ===== LOCALSTORAGE =====
function getBillets() {
  return JSON.parse(localStorage.getItem('billets') || '[]');
}

function saveBillets(billets) {
  localStorage.setItem('billets', JSON.stringify(billets));
}

// ===== RENDU D'UNE CARTE BILLET =====
function renderTicketCard(billet) {
  const safeEventName = billet.eventName.replace(/'/g, "\\'");
  const safeDate = billet.date.replace(/'/g, "\\'");
  return `
    <div class="ticket-card" id="ticket-${billet.code}">
      <div class="ticket-body">
        <div class="d-flex justify-between align-center mb-1" style="flex-wrap:wrap; gap:0.5rem;">
          <span class="event-card-category category-${billet.category}">${billet.categoryLabel}</span>
          <span class="badge badge-success">Confirmé</span>
        </div>
        <h3 style="font-size:1rem; font-weight:800; margin-bottom:0.5rem;">${billet.eventName}</h3>
        <div style="display:flex; flex-direction:column; gap:0.25rem; margin-bottom:0.75rem;">
          <span class="fs-sm text-muted">📅 ${billet.date}</span>
          <span class="fs-sm text-muted">📍 ${billet.lieu}</span>
        </div>
        <div class="d-flex gap-1 flex-wrap">
          <button class="btn btn-primary btn-sm" onclick="showTicketModal('${billet.code}', '${safeEventName}', '${safeDate}')">Voir le billet</button>
          <button class="btn btn-outline btn-sm" onclick="downloadTicket()">⬇ Télécharger</button>
          <button class="btn btn-ghost btn-sm" onclick="confirmCancel('${billet.code}')" style="color:var(--danger);">✕ Annuler</button>
        </div>
      </div>
      <div class="ticket-side">
        <div class="ticket-code">${billet.shortCode}</div>
      </div>
    </div>
  `;
}

// ===== AFFICHAGE DES BILLETS =====
function renderUpcomingTickets() {
  const billets = getBillets();
  const container = document.getElementById('upcomingTickets');
  const emptyState = document.getElementById('noUpcomingTickets');
  const statActifs = document.getElementById('statActifs');
  const badge = document.getElementById('badgeActifs');

  if (billets.length === 0) {
    container.style.display = 'none';
    emptyState.style.display = 'block';
    statActifs.textContent = '0';
    badge.textContent = '0';
  } else {
    container.style.display = 'flex';
    emptyState.style.display = 'none';
    container.innerHTML = billets.map(renderTicketCard).join('');
    statActifs.textContent = billets.length;
    badge.textContent = billets.length;
  }
}

// ===== MODAL BILLET =====
let currentCancelCode = null;

function showTicketModal(code, eventName, date) {
  document.getElementById('ticketCodeDisplay').textContent = code;
  document.getElementById('ticketEventName').textContent = eventName || '';
  document.getElementById('ticketEventDate').textContent = date ? '📅 ' + date : '';
  document.getElementById('ticketModal').classList.add('open');
}

function closeTicketModal() {
  document.getElementById('ticketModal').classList.remove('open');
}

function downloadTicket() {
  alert('Téléchargement du billet en PDF... (fonctionnalité backend requise)');
}

// ===== ANNULATION =====
function confirmCancel(code) {
  currentCancelCode = code;
  document.getElementById('cancelModal').classList.add('open');
}

function cancelReservation() {
  if (!currentCancelCode) return;
  document.getElementById('cancelModal').classList.remove('open');

  const billets = getBillets().filter(b => b.code !== currentCancelCode);
  saveBillets(billets);

  renderUpcomingTickets();

  const alertEl = document.createElement('div');
  alertEl.className = 'alert alert-success';
  alertEl.textContent = `✅ Réservation ${currentCancelCode} annulée. Ta place a été libérée.`;
  document.querySelector('.container').prepend(alertEl);
  setTimeout(() => alertEl.remove(), 4000);

  currentCancelCode = null;
}

// ===== LISTE D'ATTENTE =====
function leaveWaitlist(id) {
  if (confirm("Quitter la liste d'attente pour cet événement ?")) {
    alert("Vous avez quitté la liste d'attente.");
  }
}

// ===== FERMER MODALS EN CLIQUANT SUR LE FOND =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

// ===== INIT =====
renderUpcomingTickets();