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

// ===== AFFICHAGE NOM DU FICHIER AVATAR =====
var inputAvatar = document.getElementById('avatarInput');
if (inputAvatar) {
  inputAvatar.addEventListener('change', function() {
    var fichier = inputAvatar.files[0];
    if (!fichier) return;
    var apercu = document.getElementById('avatarPreview');
    if (apercu) apercu.textContent = fichier.name;
  });
}

// ===== FORMULAIRE PROFIL =====
var formulaireProfil = document.getElementById('profileForm');

if (formulaireProfil) {
  formulaireProfil.addEventListener('submit', function(e) {
    var prenom = document.getElementById('prenom') ? document.getElementById('prenom').value.trim() : '';
    var nom = document.getElementById('nom') ? document.getElementById('nom').value.trim() : '';
    var email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
    var valide = true;

    if (!prenom) { afficherErreur('prenomError'); valide = false; } else { cacherErreur('prenomError'); }
    if (!nom) { afficherErreur('nomError'); valide = false; } else { cacherErreur('nomError'); }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { afficherErreur('emailError'); valide = false; } else { cacherErreur('emailError'); }

    if (!valide) e.preventDefault();
  });
}

function reinitialiserFormulaire() {
  var formulaire = document.getElementById('profileForm');
  if (formulaire) formulaire.reset();
}

// ===== FORMULAIRE MOT DE PASSE =====
var formulaireMdp = document.getElementById('passwordForm');

if (formulaireMdp) {
  formulaireMdp.addEventListener('submit', function(e) {
    var actuel = document.getElementById('currentPassword') ? document.getElementById('currentPassword').value : '';
    var nouveauMdp = document.getElementById('newPassword') ? document.getElementById('newPassword').value : '';
    var confirmation = document.getElementById('confirmNewPassword') ? document.getElementById('confirmNewPassword').value : '';
    var valide = true;

    if (!actuel) { afficherErreur('currentPasswordError'); valide = false; } else { cacherErreur('currentPasswordError'); }
    if (nouveauMdp.length < 8) { afficherErreur('newPasswordError'); valide = false; } else { cacherErreur('newPasswordError'); }
    if (nouveauMdp !== confirmation) { afficherErreur('confirmNewPasswordError'); valide = false; } else { cacherErreur('confirmNewPasswordError'); }

    if (!valide) e.preventDefault();
  });
}

// ===== NOTIFICATIONS =====
function sauvegarderNotifications() {
  alert('Préférences de notifications enregistrées !');
}

// ===== SUPPRESSION DE COMPTE =====
function confirmerSuppressionCompte() {
  var modale = document.getElementById('deleteModal');
  if (modale) modale.classList.add('ouvert');
}

function supprimerCompte() {
  var confirmationSuppression = document.getElementById('deleteConfirm');
  var valeur = confirmationSuppression ? confirmationSuppression.value : '';
  if (valeur === 'SUPPRIMER') {
    window.location.href = 'supprimer-compte.php';
  } else {
    alert('Veuillez taper "SUPPRIMER" pour confirmer.');
  }
}

function revoquerSession() {
  if (confirm('Révoquer cette session ?')) {
    alert('Session révoquée.');
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

function afficherErreur(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('visible');
}

function cacherErreur(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('visible');
}