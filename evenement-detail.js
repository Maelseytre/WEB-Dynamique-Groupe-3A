// ===== MODAL RÉSERVATION =====
function afficherModalReservation() {
  var modale = document.getElementById('reservationModal');
  if (modale) modale.classList.add('ouvert');
}

function fermerModal() {
  var modale = document.getElementById('reservationModal');
  if (modale) modale.classList.remove('ouvert');
}

function fermerModalSucces() {
  var modale = document.getElementById('successModal');
  if (modale) modale.classList.remove('ouvert');
}

// ===== FERMER LES MODALS EN CLIQUANT SUR LE FOND =====
var voilesModal = document.querySelectorAll('.voile-modal');
for (var i = 0; i < voilesModal.length; i++) {
  voilesModal[i].addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('ouvert');
    }
  });
}