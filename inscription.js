// ===== SÉLECTEUR DE RÔLE =====
var inputsRole = document.querySelectorAll('input[name="role"]');
var groupeAssociation = document.getElementById('associationGroup');

for (var r = 0; r < inputsRole.length; r++) {
  inputsRole[r].addEventListener('change', function() {
    if (groupeAssociation) {
      groupeAssociation.style.display = this.value === 'organisateur' ? 'block' : 'none';
    }
  });
}

// ===== TOGGLE VISIBILITÉ MOT DE PASSE =====
var basculerMdp = document.getElementById('togglePassword');
var champMdp = document.getElementById('password');

if (basculerMdp && champMdp) {
  basculerMdp.addEventListener('click', function() {
    var type = champMdp.type === 'password' ? 'text' : 'password';
    champMdp.type = type;
    basculerMdp.textContent = type === 'password' ? 'Voir' : 'Cacher';
  });
}

// ===== FORCE DU MOT DE PASSE =====
var barreForce = document.getElementById('strengthBar');
var texteForce = document.getElementById('strengthText');
var forceMotDePasse = document.getElementById('passwordStrength');

if (champMdp && barreForce) {
  champMdp.addEventListener('input', function() {
    var valeur = champMdp.value;

    if (forceMotDePasse) {
      forceMotDePasse.style.display = valeur.length > 0 ? 'block' : 'none';
    }

    var score = 0;
    if (valeur.length >= 8) score++;
    if (/[A-Z]/.test(valeur)) score++;
    if (/[0-9]/.test(valeur)) score++;
    if (/[^A-Za-z0-9]/.test(valeur)) score++;

    var niveaux = [
      { largeur: '25%', couleur: '#EF4444', libelle: 'Tres faible' },
      { largeur: '50%', couleur: '#F59E0B', libelle: 'Faible' },
      { largeur: '75%', couleur: '#3B82F6', libelle: 'Moyen' },
      { largeur: '100%', couleur: '#10B981', libelle: 'Fort' }
    ];

    var niveau = niveaux[score > 0 ? score - 1 : 0];
    barreForce.style.width = niveau.largeur;
    barreForce.style.background = niveau.couleur;
    if (texteForce) texteForce.textContent = 'Force du mot de passe : ' + niveau.libelle;
  });
}

// ===== VÉRIFICATION MOT DE PASSE EN TEMPS RÉEL =====
var champConfirmation = document.getElementById('confirmPassword');
var erreurConfirmation = document.getElementById('confirmPasswordError');

if (champConfirmation) {
  champConfirmation.addEventListener('input', function() {
    var mdp = champMdp ? champMdp.value : '';
    var confirmation = champConfirmation.value;

    if (confirmation.length === 0) {
      if (erreurConfirmation) erreurConfirmation.classList.remove('visible');
      return;
    }

    if (erreurConfirmation) {
      erreurConfirmation.classList.add('visible');
      if (mdp === confirmation) {
        erreurConfirmation.style.color = '#10B981';
        erreurConfirmation.textContent = 'Les mots de passe correspondent.';
      } else {
        erreurConfirmation.style.color = '';
        erreurConfirmation.textContent = 'Les mots de passe ne correspondent pas.';
      }
    }
  });
}

// ===== VALIDATION DU FORMULAIRE =====
var formulaireInscription = document.getElementById('registerForm');
var boiteAlerte = document.getElementById('alertBox');

if (formulaireInscription) {
  formulaireInscription.addEventListener('submit', function(e) {
    if (boiteAlerte) boiteAlerte.classList.add('masque');

    var prenom = document.getElementById('prenom') ? document.getElementById('prenom').value.trim() : '';
    var nom = document.getElementById('nom') ? document.getElementById('nom').value.trim() : '';
    var email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
    var motDePasse = champMdp ? champMdp.value : '';
    var confirmationMdp = champConfirmation ? champConfirmation.value : '';
    var cguEl = document.getElementById('cgu');
    var cgu = cguEl ? cguEl.checked : false;
    var roleSelectionne = document.querySelector('input[name="role"]:checked');
    var role = roleSelectionne ? roleSelectionne.value : '';
    var associationEl = document.getElementById('association');
    var association = associationEl ? associationEl.value : '';

    var valide = true;

    if (!prenom) { afficherErreur('prenomError'); valide = false; } else { cacherErreur('prenomError'); }
    if (!nom) { afficherErreur('nomError'); valide = false; } else { cacherErreur('nomError'); }
    if (!email || !estEmailValide(email)) { afficherErreur('emailError'); valide = false; } else { cacherErreur('emailError'); }
    if (motDePasse.length < 8) { afficherErreur('passwordError'); valide = false; } else { cacherErreur('passwordError'); }
    if (motDePasse !== confirmationMdp) {
      afficherErreur('confirmPasswordError');
      valide = false;
    } else {
      cacherErreur('confirmPasswordError');
    }
    if (role === 'organisateur' && !association) { afficherErreur('associationError'); valide = false; } else { cacherErreur('associationError'); }
    if (!cgu) { afficherErreur('cguError'); valide = false; } else { cacherErreur('cguError'); }

    if (!valide) {
      e.preventDefault();
    }
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