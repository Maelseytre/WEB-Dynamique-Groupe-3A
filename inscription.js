// ===== HAMBURGER (page auth n'a pas de navbar, mais bon) =====

// ===== ROLE SELECTOR =====
const roleInputs = document.querySelectorAll('input[name="role"]');
const associationGroup = document.getElementById('associationGroup');

roleInputs.forEach(input => {
  input.addEventListener('change', () => {
    if (input.value === 'organisateur') {
      associationGroup.style.display = 'block';
    } else {
      associationGroup.style.display = 'none';
    }
  });
});

// ===== PASSWORD TOGGLE =====
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  togglePassword.textContent = type === 'password' ? 'Voir' : 'Cacher';
});

// ===== PASSWORD STRENGTH =====
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');
const passwordStrength = document.getElementById('passwordStrength');

passwordInput.addEventListener('input', () => {
  const val = passwordInput.value;
  passwordStrength.style.display = val.length > 0 ? 'block' : 'none';

  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { width: '25%', color: '#EF4444', label: 'Très faible' },
    { width: '50%', color: '#F59E0B', label: 'Faible' },
    { width: '75%', color: '#3B82F6', label: 'Moyen' },
    { width: '100%', color: '#10B981', label: 'Fort' },
  ];

  const level = levels[Math.max(0, score - 1)];
  strengthBar.style.width = level.width;
  strengthBar.style.background = level.color;
  strengthText.textContent = `Force du mot de passe : ${level.label}`;
});

// ===== CONFIRM PASSWORD REAL-TIME CHECK =====
const confirmPasswordInput = document.getElementById('confirmPassword');
const confirmPasswordError = document.getElementById('confirmPasswordError');

confirmPasswordInput.addEventListener('input', () => {
  const pwd = passwordInput.value;
  const confirm = confirmPasswordInput.value;

  if (confirm.length === 0) {
    confirmPasswordError.classList.remove('show');
    confirmPasswordError.style.color = '';
    confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
    return;
  }

  if (pwd === confirm) {
    confirmPasswordError.classList.add('show');
    confirmPasswordError.style.color = '#10B981';
    confirmPasswordError.textContent = 'Les mots de passe correspondent.';
  } else {
    confirmPasswordError.classList.add('show');
    confirmPasswordError.style.color = '';
    confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
  }
});

// ===== FORM VALIDATION =====
const registerForm = document.getElementById('registerForm');
const alertBox = document.getElementById('alertBox');
const successBox = document.getElementById('successBox');
const submitBtn = document.getElementById('submitBtn');

registerForm.addEventListener('submit', (e) => {
  e.preventDefault();
  alertBox.classList.add('d-none');

  const prenom = document.getElementById('prenom').value.trim();
  const nom = document.getElementById('nom').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = passwordInput.value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  const cgu = document.getElementById('cgu').checked;
  const role = document.querySelector('input[name="role"]:checked').value;
  const association = document.getElementById('association').value;

  let valid = true;

  if (!prenom) { showError('prenomError'); valid = false; } else hideError('prenomError');
  if (!nom) { showError('nomError'); valid = false; } else hideError('nomError');
  if (!email || !isValidEmail(email)) { showError('emailError'); valid = false; } else hideError('emailError');
  if (password.length < 8) { showError('passwordError'); valid = false; } else hideError('passwordError');
  if (password !== confirmPassword) {
    confirmPasswordError.style.color = '';
    confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
    showError('confirmPasswordError');
    valid = false;
  } else {
    confirmPasswordError.style.color = '';
    confirmPasswordError.textContent = 'Les mots de passe ne correspondent pas.';
    hideError('confirmPasswordError');
  }
  if (role === 'organisateur' && !association) { showError('associationError'); valid = false; } else hideError('associationError');
  if (!cgu) { showError('cguError'); valid = false; } else hideError('cguError');

  if (!valid) return;

  submitBtn.disabled = true;
  submitBtn.textContent = 'Création en cours...';

  setTimeout(() => {
    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const emailExists = users.find(u => u.email.toLowerCase() === email.toLowerCase());

    if (emailExists) {
      alertBox.textContent = 'Un compte avec cet email existe déjà.';
      alertBox.classList.remove('d-none');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Créer mon compte';
      return;
    }

    users.push({ email, password, prenom, nom, role, association });
    localStorage.setItem('users', JSON.stringify(users));

    successBox.classList.remove('d-none');
    registerForm.style.display = 'none';
    submitBtn.disabled = false;
    submitBtn.textContent = 'Créer mon compte';
  }, 1000);
});

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}