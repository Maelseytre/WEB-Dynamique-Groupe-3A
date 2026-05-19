// ===== TOGGLE VISIBILITÉ MOT DE PASSE =====
var basculerMdp = document.getElementById('togglePassword');
var champMdp = document.getElementById('password');
var champEmail = document.getElementById('email');
var formulaireConnexion = document.getElementById('loginForm');
var boiteAlerte = document.getElementById('alertBox');

if (basculerMdp && champMdp) {
  basculerMdp.addEventListener('click', function() {
    var type = champMdp.type === 'password' ? 'text' : 'password';
    champMdp.type = type;
    basculerMdp.textContent = type === 'password' ? 'Voir' : 'Cacher';
  });
}

// ===== VALIDATION DU FORMULAIRE =====
if (formulaireConnexion) {
  formulaireConnexion.addEventListener('submit', function(e) {
    if (boiteAlerte) boiteAlerte.classList.add('masque');

    var email = champEmail ? champEmail.value.trim() : '';
    var motDePasse = champMdp ? champMdp.value : '';
    var valide = true;

    if (!email || !estEmailValide(email)) {
      afficherErreur('emailError');
      if (champEmail) champEmail.classList.add('erreur');
      valide = false;
    } else {
      cacherErreur('emailError');
      if (champEmail) champEmail.classList.remove('erreur');
    }

    if (!motDePasse) {
      afficherErreur('passwordError');
      if (champMdp) champMdp.classList.add('erreur');
      valide = false;
    } else {
      cacherErreur('passwordError');
      if (champMdp) champMdp.classList.remove('erreur');
    }

    if (!valide) {
      e.preventDefault();
    }
  });
}

if (champEmail) {
  champEmail.addEventListener('input', function() {
    champEmail.classList.remove('erreur');
    cacherErreur('emailError');
  });
}

if (champMdp) {
  champMdp.addEventListener('input', function() {
    champMdp.classList.remove('erreur');
    cacherErreur('passwordError');
  });
}

function estEmailValide(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function afficherErreur(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('visible');
}

function cacherErreur(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('visible');
}