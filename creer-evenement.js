// ===== COMPTEURS DE CARACTÈRES =====
var champTitre = document.getElementById('titre');
var compteurTitre = document.getElementById('titreCount');

if (champTitre && compteurTitre) {
  champTitre.addEventListener('input', function() {
    compteurTitre.textContent = champTitre.value.length;
  });
}

var champDescription = document.getElementById('description');
var compteurDescription = document.getElementById('descriptionCount');

if (champDescription && compteurDescription) {
  champDescription.addEventListener('input', function() {
    compteurDescription.textContent = champDescription.value.length;
  });
}

// ===== AFFICHAGE NOM DU FICHIER =====
var inputAffiche = document.getElementById('afficheInput');
var zoneDepot = document.getElementById('uploadArea');

if (inputAffiche && zoneDepot) {
  inputAffiche.addEventListener('change', function() {
    var fichier = inputAffiche.files[0];
    if (!fichier) return;

    if (fichier.size > 5 * 1024 * 1024) {
      alert('Le fichier est trop lourd (max 5 Mo).');
      inputAffiche.value = '';
      return;
    }

    var texteDepot = zoneDepot.querySelector('.texte-depot');
    if (texteDepot) texteDepot.textContent = fichier.name;
  });
}

// ===== DATE MINIMUM =====
var aujourdhui = new Date().toISOString().split('T')[0];
var dateDebut = document.getElementById('dateDebut');
var dateFin = document.getElementById('dateFin');

if (dateDebut) {
  dateDebut.min = aujourdhui;
  dateDebut.addEventListener('change', function() {
    if (dateFin) dateFin.min = dateDebut.value;
  });
}
if (dateFin) {
  dateFin.min = aujourdhui;
}

// ===== VALIDATION DU FORMULAIRE =====
var formulaireCreation = document.getElementById('createEventForm');
var boiteAlerte = document.getElementById('alertBox');

if (formulaireCreation) {
  formulaireCreation.addEventListener('submit', function(e) {
    if (boiteAlerte) boiteAlerte.classList.add('masque');

    var valeurTitre = document.getElementById('titre') ? document.getElementById('titre').value.trim() : '';
    var valeurCategorie = document.getElementById('categorie') ? document.getElementById('categorie').value : '';
    var valeurAssociation = document.getElementById('association') ? document.getElementById('association').value : '';
    var valeurDescription = document.getElementById('description') ? document.getElementById('description').value.trim() : '';
    var valeurDateDebut = document.getElementById('dateDebut') ? document.getElementById('dateDebut').value : '';
    var valeurHeureDebut = document.getElementById('heureDebut') ? document.getElementById('heureDebut').value : '';
    var valeurLieu = document.getElementById('lieu') ? document.getElementById('lieu').value.trim() : '';
    var valeurCapacite = document.getElementById('capacite') ? document.getElementById('capacite').value : '';

    var valide = true;

    if (valeurTitre.length < 3) { afficherErreur('titreError'); valide = false; } else { cacherErreur('titreError'); }
    if (!valeurCategorie) { afficherErreur('categorieError'); valide = false; } else { cacherErreur('categorieError'); }
    if (!valeurAssociation) { afficherErreur('associationError'); valide = false; } else { cacherErreur('associationError'); }
    if (valeurDescription.length < 50) { afficherErreur('descriptionError'); valide = false; } else { cacherErreur('descriptionError'); }
    if (!valeurDateDebut) { afficherErreur('dateDebutError'); valide = false; } else { cacherErreur('dateDebutError'); }
    if (!valeurHeureDebut) { afficherErreur('heureDebutError'); valide = false; } else { cacherErreur('heureDebutError'); }
    if (valeurLieu.length < 3) { afficherErreur('lieuError'); valide = false; } else { cacherErreur('lieuError'); }
    if (!valeurCapacite || parseInt(valeurCapacite) <= 0) { afficherErreur('capaciteError'); valide = false; } else { cacherErreur('capaciteError'); }

    if (!valide) {
      if (boiteAlerte) {
        boiteAlerte.textContent = 'Veuillez corriger les erreurs dans le formulaire.';
        boiteAlerte.classList.remove('masque');
      }
      e.preventDefault();
      window.scrollTo(0, 0);
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