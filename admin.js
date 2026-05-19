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

// ===== VALIDATION DES COMPTES =====
var ligneActionEnAttente = null;
var nomActionEnAttente = '';

function approuverCompte(id) {
  var bouton = event.target;
  var ligne = bouton.parentNode.parentNode;
  var elementNom = ligne.querySelector('.semi-gras');
  var nom = elementNom ? elementNom.textContent : '';
  ligne.style.opacity = '0.5';
  ligne.style.transition = 'opacity 0.3s';
  setTimeout(function() {
    if (ligne.parentNode) ligne.parentNode.removeChild(ligne);
  }, 300);
  afficherNotification('Compte de ' + nom + ' approuvé. Un email de confirmation a été envoyé.', 'success');
  majCompteurAttente(-1);
}

function refuserCompte(id) {
  var bouton = event.target;
  var ligne = bouton.parentNode.parentNode;
  var elementNom = ligne.querySelector('.semi-gras');
  nomActionEnAttente = elementNom ? elementNom.textContent : '';
  ligneActionEnAttente = ligne;

  document.getElementById('actionModalTitle').textContent = 'Refuser le compte';
  document.getElementById('actionModalMessage').textContent = 'Êtes-vous sûr de vouloir refuser le compte organisateur de ' + nomActionEnAttente + ' ?';
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').classList.remove('bouton-primaire');
  document.getElementById('actionModalConfirm').classList.add('bouton-danger');
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'confirmerRefusCompte()');
  document.getElementById('actionModal').classList.add('ouvert');
}

function confirmerRefusCompte() {
  document.getElementById('actionModal').classList.remove('ouvert');
  if (ligneActionEnAttente) {
    ligneActionEnAttente.style.opacity = '0.5';
    var refLigne = ligneActionEnAttente;
    setTimeout(function() {
      if (refLigne.parentNode) refLigne.parentNode.removeChild(refLigne);
    }, 300);
  }
  afficherNotification('Compte de ' + nomActionEnAttente + ' refusé.', 'warning');
  majCompteurAttente(-1);
  ligneActionEnAttente = null;
  nomActionEnAttente = '';
}

function majCompteurAttente(delta) {
  var pastilleElement = document.querySelector('[data-tab="comptes"] .pastille');
  if (pastilleElement) {
    var actuel = parseInt(pastilleElement.textContent) || 0;
    pastilleElement.textContent = Math.max(0, actuel + delta);
  }
}

// ===== UTILISATEURS =====
function voirUtilisateur(id) {
  afficherNotification('Ouverture du profil utilisateur...', 'success');
}

function suspendreUtilisateur(id) {
  if (confirm('Suspendre cet utilisateur ? Il ne pourra plus se connecter.')) {
    afficherNotification('Utilisateur suspendu.', 'warning');
  }
}

function supprimerUtilisateur(id) {
  document.getElementById('actionModalTitle').textContent = 'Supprimer l\'utilisateur';
  document.getElementById('actionModalMessage').textContent = 'Cette action est irréversible. Toutes les données de l\'utilisateur seront supprimées.';
  document.getElementById('actionReasonGroup').style.display = 'none';
  document.getElementById('actionModalConfirm').className = 'bouton bouton-danger';
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'executerAction()');
  document.getElementById('actionModal').classList.add('ouvert');
}

// ===== ÉVÉNEMENTS =====
function voirEvenement(id) {
  window.location.href = 'evenement-detail.php?id=' + id;
}

function retirerEvenement(id) {
  document.getElementById('actionModalTitle').textContent = 'Retirer l\'événement';
  document.getElementById('actionModalMessage').textContent = 'L\'événement sera retiré de la plateforme. L\'organisateur et les inscrits seront notifiés.';
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').className = 'bouton bouton-danger';
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'executerAction()');
  document.getElementById('actionModal').classList.add('ouvert');
}

// ===== SIGNALEMENTS =====
function avertirUtilisateur(id) {
  afficherNotification('Avertissement envoyé à l\'organisateur.', 'warning');
}

function contacterOrganisateur(id) {
  afficherNotification('Email envoyé à l\'organisateur.', 'success');
}

function ignorerSignalement(id) {
  var bouton = event.target;
  var carte = bouton.parentNode;
  while (carte && !carte.classList.contains('carte')) {
    carte = carte.parentNode;
  }
  if (carte) {
    carte.style.opacity = '0.5';
    setTimeout(function() {
      if (carte.parentNode) carte.parentNode.removeChild(carte);
    }, 300);
  }
  afficherNotification('Signalement ignoré.', 'success');
}

// ===== RECHERCHE UTILISATEURS =====
var rechercheUtilisateur = document.getElementById('userSearch');
if (rechercheUtilisateur) {
  rechercheUtilisateur.addEventListener('input', function() {
    var requete = rechercheUtilisateur.value.toLowerCase();
    var lignes = document.querySelectorAll('#tab-utilisateurs tbody tr');
    for (var i = 0; i < lignes.length; i++) {
      var texte = lignes[i].textContent.toLowerCase();
      lignes[i].style.display = texte.indexOf(requete) !== -1 ? '' : 'none';
    }
  });
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

function executerAction() {
  document.getElementById('actionModal').classList.remove('ouvert');
}

// ===== NOTIFICATION TOAST =====
function afficherNotification(message, type) {
  if (!type) type = 'success';
  var couleurs = { success: 'alerte-succes', warning: 'alerte-avertissement', danger: 'alerte-danger' };
  var notification = document.createElement('div');
  notification.className = 'alerte ' + (couleurs[type] || 'alerte-succes');
  notification.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--ombre-grande);min-width:300px;';
  notification.textContent = message;
  document.body.appendChild(notification);
  setTimeout(function() {
    if (notification.parentNode) notification.parentNode.removeChild(notification);
  }, 3500);
}