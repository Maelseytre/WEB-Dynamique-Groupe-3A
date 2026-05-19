// ===== MODAL BILLET =====
var codeAnnulation = null;

function afficherModalBillet(code, nomEvenement, date) {
  var elementCode = document.getElementById('ticketCodeDisplay');
  var elementNom = document.getElementById('ticketEventName');
  var elementDate = document.getElementById('ticketEventDate');
  var modale = document.getElementById('ticketModal');
  if (elementCode) elementCode.textContent = code;
  if (elementNom) elementNom.textContent = nomEvenement || '';
  if (elementDate) elementDate.textContent = date || '';
  if (modale) modale.classList.add('ouvert');
}

function fermerModalBillet() {
  var modale = document.getElementById('ticketModal');
  if (modale) modale.classList.remove('ouvert');
}

function telechargerBillet() {
  alert('Téléchargement du billet en PDF... (fonctionnalité backend requise)');
}

// ===== ANNULATION =====
function confirmerAnnulation(code) {
  codeAnnulation = code;
  var modale = document.getElementById('cancelModal');
  if (modale) modale.classList.add('ouvert');
}

function fermerModalAnnulation() {
  var modale = document.getElementById('cancelModal');
  if (modale) modale.classList.remove('ouvert');
  codeAnnulation = null;
}

function annulerReservation() {
  if (!codeAnnulation) return;
  fermerModalAnnulation();
  window.location.href = 'annuler-reservation.php?code=' + codeAnnulation;
}

// ===== LISTE D'ATTENTE =====
function quitterListeAttente(id) {
  if (confirm("Quitter la liste d'attente pour cet événement ?")) {
    window.location.href = 'quitter-attente.php?id=' + id;
  }
}

// ===== FERMER MODALS EN CLIQUANT SUR LE FOND =====
var voilesModal = document.querySelectorAll('.voile-modal');
for (var i = 0; i < voilesModal.length; i++) {
  voilesModal[i].addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('ouvert');
    }
  });
}