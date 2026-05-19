// ===== FILTRES =====
var filtres = document.querySelectorAll('.filtre');
var cartesEvenements = document.querySelectorAll('.carte-evenement');
var compteurResultats = document.getElementById('resultCount');
var sansResultat = document.getElementById('noResults');
var grilleEvenements = document.getElementById('eventsGrid');

var filtreActif = 'all';
var rechercheActuelle = '';

for (var i = 0; i < filtres.length; i++) {
  filtres[i].addEventListener('click', function() {
    for (var j = 0; j < filtres.length; j++) filtres[j].classList.remove('actif');
    this.classList.add('actif');
    filtreActif = this.getAttribute('data-filter');
    filtrerEvenements();
  });
}

// ===== RECHERCHE =====
var champRecherche = document.getElementById('searchInput');
var boutonRecherche = document.getElementById('searchBtn');
var boutonReinitialisation = document.getElementById('resetSearch');

if (boutonRecherche) {
  boutonRecherche.addEventListener('click', function() {
    rechercheActuelle = champRecherche ? champRecherche.value.trim().toLowerCase() : '';
    filtrerEvenements();
  });
}

if (champRecherche) {
  champRecherche.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      rechercheActuelle = champRecherche.value.trim().toLowerCase();
      filtrerEvenements();
    }
  });
}

if (boutonReinitialisation) {
  boutonReinitialisation.addEventListener('click', function() {
    if (champRecherche) champRecherche.value = '';
    rechercheActuelle = '';
    filtreActif = 'all';
    for (var j = 0; j < filtres.length; j++) filtres[j].classList.remove('actif');
    var tousFiltre = document.querySelector('[data-filter="all"]');
    if (tousFiltre) tousFiltre.classList.add('actif');
    filtrerEvenements();
  });
}

function filtrerEvenements() {
  var visible = 0;

  for (var i = 0; i < cartesEvenements.length; i++) {
    var carte = cartesEvenements[i];
    var categorie = carte.getAttribute('data-category') || '';
    var elementTitre = carte.querySelector('.carte-evenement-titre');
    var elementMeta = carte.querySelector('.carte-evenement-meta');
    var titre = elementTitre ? elementTitre.textContent.toLowerCase() : '';
    var meta = elementMeta ? elementMeta.textContent.toLowerCase() : '';

    var correspondFiltre = filtreActif === 'all' || categorie === filtreActif;
    var correspondRecherche = rechercheActuelle === '' || titre.indexOf(rechercheActuelle) !== -1 || meta.indexOf(rechercheActuelle) !== -1;

    if (correspondFiltre && correspondRecherche) {
      carte.style.display = 'block';
      visible++;
    } else {
      carte.style.display = 'none';
    }
  }

  if (compteurResultats) {
    compteurResultats.textContent = visible + ' événement' + (visible > 1 ? 's' : '') + ' trouvé' + (visible > 1 ? 's' : '');
  }

  if (visible === 0) {
    if (sansResultat) sansResultat.classList.remove('masque');
    if (grilleEvenements) grilleEvenements.style.display = 'none';
  } else {
    if (sansResultat) sansResultat.classList.add('masque');
    if (grilleEvenements) grilleEvenements.style.display = 'grid';
  }
}

// ===== TRI =====
var selecteurTri = document.getElementById('sortSelect');
if (selecteurTri) {
  selecteurTri.addEventListener('change', function() {
    var cartes = [];
    var toutesLesCartes = grilleEvenements ? grilleEvenements.querySelectorAll('.carte-evenement') : [];
    for (var i = 0; i < toutesLesCartes.length; i++) {
      cartes.push(toutesLesCartes[i]);
    }

    if (selecteurTri.value === 'name') {
      cartes.sort(function(a, b) {
        var titreA = a.querySelector('.carte-evenement-titre');
        var titreB = b.querySelector('.carte-evenement-titre');
        var nomA = titreA ? titreA.textContent : '';
        var nomB = titreB ? titreB.textContent : '';
        return nomA.localeCompare(nomB);
      });
    }

    for (var i = 0; i < cartes.length; i++) {
      grilleEvenements.appendChild(cartes[i]);
    }
  });
}