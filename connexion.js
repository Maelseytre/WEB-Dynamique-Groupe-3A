// ===== TOGGLE VISIBILITÉ MOT DE PASSE =====
var togglePassword = document.getElementById('togglePassword');
var passwordInput = document.getElementById('password');
var emailInput = document.getElementById('email');
var loginForm = document.getElementById('loginForm');
var alertBox = document.getElementById('alertBox');

if (togglePassword && passwordInput) {
  togglePassword.addEventListener('click', function() {
    var type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    togglePassword.textContent = type === 'password' ? 'Voir' : 'Cacher';
  });
}

// ===== VALIDATION DU FORMULAIRE =====
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    if (alertBox) alertBox.classList.add('d-none');

    var email = emailInput ? emailInput.value.trim() : '';
    var password = passwordInput ? passwordInput.value : '';
    var valid = true;

    if (!email || !isValidEmail(email)) {
      showError('emailError');
      if (emailInput) emailInput.classList.add('error');
      valid = false;
    } else {
      hideError('emailError');
      if (emailInput) emailInput.classList.remove('error');
    }

    if (!password) {
      showError('passwordError');
      if (passwordInput) passwordInput.classList.add('error');
      valid = false;
    } else {
      hideError('passwordError');
      if (passwordInput) passwordInput.classList.remove('error');
    }

    if (!valid) {
      e.preventDefault();
    }
  });
}

if (emailInput) {
  emailInput.addEventListener('input', function() {
    emailInput.classList.remove('error');
    hideError('emailError');
  });
}

if (passwordInput) {
  passwordInput.addEventListener('input', function() {
    passwordInput.classList.remove('error');
    hideError('passwordError');
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