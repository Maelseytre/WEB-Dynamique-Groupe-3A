// ===== ONGLETS =====
var boutonsOnglets = document.querySelectorAll('.bouton-onglet');
var panneauxOnglets = document.querySelectorAll('.panneau-onglet');

for (var i = 0; i < boutonsOnglets.length; i++) {
  boutonsOnglets[i].addEventListener('click', function() {
    for (var j = 0; j < boutonsOnglets.length; j++) boutonsOnglets[j].classList.remove('actif');
    for (var j = 0; j < panneauxOnglets.length; j++) panneauxOnglets[j].classList.remove('actif');
    this.classList.add('actif');
    var panneau = document.getElementById('tab-' + this.getAttribute('data-tab'));
    if (panneau) panneau.classList.add('actif');
  });
}

// ===== ACTIONS SUR LES ÉVÉNEMENTS =====
var evenementASupprimer = null;

function voirParticipants(idEvenement) {
  for (var i = 0; i < boutonsOnglets.length; i++) boutonsOnglets[i].classList.remove('actif');
  for (var i = 0; i < panneauxOnglets.length; i++) panneauxOnglets[i].classList.remove('actif');
  var boutonInscrits = document.querySelector('[data-tab="inscrits"]');
  var panneauInscrits = document.getElementById('tab-inscrits');
  if (boutonInscrits) boutonInscrits.classList.add('actif');
  if (panneauInscrits) panneauInscrits.classList.add('actif');
}

function supprimerEvenement(idEvenement) {
  evenementASupprimer = idEvenement;
  var modale = document.getElementById('deleteModal');
  if (modale) modale.classList.add('ouvert');
}

function confirmerSuppression() {
  var modale = document.getElementById('deleteModal');
  if (modale) modale.classList.remove('ouvert');
  if (evenementASupprimer) {
    window.location.href = 'supprimer-evenement.php?id=' + evenementASupprimer;
  }
}

function publierEvenement(idEvenement) {
  afficherNotification('Événement publié avec succès !', 'success');
}

// ===== PARTICIPANTS =====
function validerPresence(idParticipant) {
  var bouton = event.target;
  if (bouton) bouton.outerHTML = '<span class="pastille pastille-succes">Présent(e)</span>';
  afficherNotification('Présence validée !', 'success');
}

function exporterCSV() {
  window.location.href = 'export-inscrits.php';
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

// ===== NOTIFICATION TOAST =====
function afficherNotification(message, type) {
  if (!type) type = 'success';
  var notification = document.createElement('div');
  notification.className = 'alerte alerte-' + type;
  notification.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--ombre-grande);min-width:280px;';
  notification.textContent = message;
  document.body.appendChild(notification);
  setTimeout(function() {
    if (notification.parentNode) notification.parentNode.removeChild(notification);
  }, 3500);
}