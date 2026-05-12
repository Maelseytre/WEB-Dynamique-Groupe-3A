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

// ===== TICKET MODAL =====
let currentCancelCode = null;

function showTicketModal(code) {
  document.getElementById('ticketCodeDisplay').textContent = code;
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

  const alert = document.createElement('div');
  alert.className = 'alert alert-success';
  alert.textContent = `✅ Réservation ${currentCancelCode} annulée. Ta place a été libérée.`;
  document.querySelector('.container').prepend(alert);

  setTimeout(() => alert.remove(), 4000);
  currentCancelCode = null;
}

// ===== WAITLIST =====
function leaveWaitlist(id) {
  if (confirm('Quitter la liste d\'attente pour cet événement ?')) {
    alert('Vous avez quitté la liste d\'attente.');
  }
}

// ===== MODAL CLOSE ON OVERLAY =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});