// ===== COMPTEURS DE CARACTÈRES =====
var titre = document.getElementById('titre');
var titreCount = document.getElementById('titreCount');

if (titre && titreCount) {
  titre.addEventListener('input', function() {
    titreCount.textContent = titre.value.length;
  });
}

var description = document.getElementById('description');
var descriptionCount = document.getElementById('descriptionCount');

if (description && descriptionCount) {
  description.addEventListener('input', function() {
    descriptionCount.textContent = description.value.length;
  });
}

// ===== AFFICHAGE NOM DU FICHIER =====
var afficheInput = document.getElementById('afficheInput');
var uploadArea = document.getElementById('uploadArea');

if (afficheInput && uploadArea) {
  afficheInput.addEventListener('change', function() {
    var file = afficheInput.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop lourd (max 5 Mo).');
      afficheInput.value = '';
      return;
    }

    var uploadText = uploadArea.querySelector('.upload-text');
    if (uploadText) uploadText.textContent = file.name;
  });
}

// ===== DATE MINIMUM =====
var today = new Date().toISOString().split('T')[0];
var dateDebut = document.getElementById('dateDebut');
var dateFin = document.getElementById('dateFin');

if (dateDebut) {
  dateDebut.min = today;
  dateDebut.addEventListener('change', function() {
    if (dateFin) dateFin.min = dateDebut.value;
  });
}
if (dateFin) {
  dateFin.min = today;
}

// ===== VALIDATION DU FORMULAIRE =====
var createEventForm = document.getElementById('createEventForm');
var alertBox = document.getElementById('alertBox');

if (createEventForm) {
  createEventForm.addEventListener('submit', function(e) {
    if (alertBox) alertBox.classList.add('d-none');

    var titreVal = document.getElementById('titre') ? document.getElementById('titre').value.trim() : '';
    var categorieVal = document.getElementById('categorie') ? document.getElementById('categorie').value : '';
    var associationVal = document.getElementById('association') ? document.getElementById('association').value : '';
    var descriptionVal = document.getElementById('description') ? document.getElementById('description').value.trim() : '';
    var dateDebutVal = document.getElementById('dateDebut') ? document.getElementById('dateDebut').value : '';
    var heureDebutVal = document.getElementById('heureDebut') ? document.getElementById('heureDebut').value : '';
    var lieuVal = document.getElementById('lieu') ? document.getElementById('lieu').value.trim() : '';
    var capaciteVal = document.getElementById('capacite') ? document.getElementById('capacite').value : '';

    var valid = true;

    if (titreVal.length < 3) { showError('titreError'); valid = false; } else { hideError('titreError'); }
    if (!categorieVal) { showError('categorieError'); valid = false; } else { hideError('categorieError'); }
    if (!associationVal) { showError('associationError'); valid = false; } else { hideError('associationError'); }
    if (descriptionVal.length < 50) { showError('descriptionError'); valid = false; } else { hideError('descriptionError'); }
    if (!dateDebutVal) { showError('dateDebutError'); valid = false; } else { hideError('dateDebutError'); }
    if (!heureDebutVal) { showError('heureDebutError'); valid = false; } else { hideError('heureDebutError'); }
    if (lieuVal.length < 3) { showError('lieuError'); valid = false; } else { hideError('lieuError'); }
    if (!capaciteVal || parseInt(capaciteVal) <= 0) { showError('capaciteError'); valid = false; } else { hideError('capaciteError'); }

    if (!valid) {
      if (alertBox) {
        alertBox.textContent = 'Veuillez corriger les erreurs dans le formulaire.';
        alertBox.classList.remove('d-none');
      }
      e.preventDefault();
      window.scrollTo(0, 0);
    }
  });
}

function showError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('show');
}