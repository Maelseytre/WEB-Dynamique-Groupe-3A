// ===== SÉLECTEUR DE RÔLE =====
var roleInputs = document.querySelectorAll('input[name="role"]');
var associationGroup = document.getElementById('associationGroup');

for (var r = 0; r < roleInputs.length; r++) {
  roleInputs[r].addEventListener('change', function() {
    if (associationGroup) {
      associationGroup.style.display = this.value === 'organisateur' ? 'block' : 'none';
    }
  });
}

// ===== TOGGLE VISIBILITÉ MOT DE PASSE =====
var togglePassword = document.getElementById('togglePassword');
var passwordInput = document.getElementById('password');

if (togglePassword && passwordInput) {
  togglePassword.addEventListener('click', function() {
    var type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePassword.textContent = type === 'password' ? 'Voir' : 'Cacher';
  });
}

// ===== FORCE DU MOT DE PASSE =====
var strengthBar = document.getElementById('strengthBar');
var strengthText = document.getElementById('strengthText');
var passwordStrength = document.getElementById('passwordStrength');

if (passwordInput && strengthBar) {
  passwordInput.addEventListener('input', function() {
    var val = passwordInput.value;

    if (passwordStrength) {
      passwordStrength.style.display = val.length > 0 ? 'block' : 'none';
    }

    var score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    var levels = [
      { width: '25%', color: '#EF4444', label: 'Tres faible' },
      { width: '50%', color: '#F59E0B', label: 'Faible' },
      { width: '75%', color: '#3B82F6', label: 'Moyen' },
      { width: '100%', color: '#10B981', label: 'Fort' }
    ];

    var level = levels[score > 0 ? score - 1 : 0];
    strengthBar.style.width = level.width;
    strengthBar.style.background = level.color;
    if (strengthText) strengthText.textContent = 'Force du mot de passe : ' + level.label;
  });
}

// ===== VÉRIFICATION MOT DE PASSE EN TEMPS RÉEL =====
var confirmPasswordInput = document.getElementById('confirmPassword');
var confirmPasswordError = document.getElementById('confirmPasswordError');

if (confirmPasswordInput) {
  confirmPasswordInput.addEventListener('input', function() {
    var pwd = passwordInput ? passwordInput.value : '';
    var confirm = confirmPasswordInput.value;

    if (confirm.length === 0) {
      if (confirmPasswordError) confirmPasswordError.classList.remove('show');
      return;
    }

    if (confirmPasswordError) {
      confirmPasswordError.classList.add('show');
      if (pwd === confirm) {
        confirmPasswordError.style.color = '#10B981';
        confirmPasswordError.textContent = 'Les mots de passe correspondent.';
      } else {
        confirmPasswordError.style.color = '';
        confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
      }
    }
  });
}

// ===== VALIDATION DU FORMULAIRE =====
var registerForm = document.getElementById('registerForm');
var alertBox = document.getElementById('alertBox');

if (registerForm) {
  registerForm.addEventListener('submit', function(e) {
    if (alertBox) alertBox.classList.add('d-none');

    var prenom = document.getElementById('prenom') ? document.getElementById('prenom').value.trim() : '';
    var nom = document.getElementById('nom') ? document.getElementById('nom').value.trim() : '';
    var email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
    var password = passwordInput ? passwordInput.value : '';
    var confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
    var cguEl = document.getElementById('cgu');
    var cgu = cguEl ? cguEl.checked : false;
    var roleChecked = document.querySelector('input[name="role"]:checked');
    var role = roleChecked ? roleChecked.value : '';
    var associationEl = document.getElementById('association');
    var association = associationEl ? associationEl.value : '';

    var valid = true;

    if (!prenom) { showError('prenomError'); valid = false; } else { hideError('prenomError'); }
    if (!nom) { showError('nomError'); valid = false; } else { hideError('nomError'); }
    if (!email || !isValidEmail(email)) { showError('emailError'); valid = false; } else { hideError('emailError'); }
    if (password.length < 8) { showError('passwordError'); valid = false; } else { hideError('passwordError'); }
    if (password !== confirmPassword) {
      showError('confirmPasswordError');
      valid = false;
    } else {
      hideError('confirmPasswordError');
    }
    if (role === 'organisateur' && !association) { showError('associationError'); valid = false; } else { hideError('associationError'); }
    if (!cgu) { showError('cguError'); valid = false; } else { hideError('cguError'); }

    if (!valid) {
      e.preventDefault();
    }
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('show');
}