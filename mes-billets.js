// ===== MODAL BILLET =====
var currentCancelCode = null;

function showTicketModal(code, eventName, date) {
  var codeEl = document.getElementById('ticketCodeDisplay');
  var nameEl = document.getElementById('ticketEventName');
  var dateEl = document.getElementById('ticketEventDate');
  var modal = document.getElementById('ticketModal');
  if (codeEl) codeEl.textContent = code;
  if (nameEl) nameEl.textContent = eventName || '';
  if (dateEl) dateEl.textContent = date || '';
  if (modal) modal.classList.add('open');
}

function closeTicketModal() {
  var modal = document.getElementById('ticketModal');
  if (modal) modal.classList.remove('open');
}

function downloadTicket() {
  alert('Téléchargement du billet en PDF... (fonctionnalité backend requise)');
}

// ===== ANNULATION =====
function confirmCancel(code) {
  currentCancelCode = code;
  var modal = document.getElementById('cancelModal');
  if (modal) modal.classList.add('open');
}

function closeCancelModal() {
  var modal = document.getElementById('cancelModal');
  if (modal) modal.classList.remove('open');
  currentCancelCode = null;
}

function cancelReservation() {
  if (!currentCancelCode) return;
  closeCancelModal();
  window.location.href = 'annuler-reservation.php?code=' + currentCancelCode;
}

// ===== LISTE D'ATTENTE =====
function leaveWaitlist(id) {
  if (confirm("Quitter la liste d'attente pour cet événement ?")) {
    window.location.href = 'quitter-attente.php?id=' + id;
  }
}

// ===== FERMER MODALS EN CLIQUANT SUR LE FOND =====
var overlays = document.querySelectorAll('.modal-overlay');
for (var i = 0; i < overlays.length; i++) {
  overlays[i].addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
    }
  });
}