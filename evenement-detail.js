// ===== MODAL RÉSERVATION =====
function showReservationModal() {
  var modal = document.getElementById('reservationModal');
  if (modal) modal.classList.add('open');
}

function closeModal() {
  var modal = document.getElementById('reservationModal');
  if (modal) modal.classList.remove('open');
}

function closeSuccessModal() {
  var modal = document.getElementById('successModal');
  if (modal) modal.classList.remove('open');
}

// ===== FERMER LES MODALS EN CLIQUANT SUR LE FOND =====
var overlays = document.querySelectorAll('.modal-overlay');
for (var i = 0; i < overlays.length; i++) {
  overlays[i].addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
    }
  });
}