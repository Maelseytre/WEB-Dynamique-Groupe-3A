// ===== TOGGLE PASSWORD VISIBILITY =====
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  togglePassword.textContent = type === 'password' ? '👁' : '🙈';
});

// ===== FORM VALIDATION =====
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const alertBox = document.getElementById('alertBox');
const submitBtn = document.getElementById('submitBtn');

const DEMO_ACCOUNTS = {
  'participant@demo.fr': { password: 'demo123', role: 'participant', redirect: 'Index.html' },
  'organisateur@demo.fr': { password: 'demo123', role: 'organisateur', redirect: 'tableau-de-bord.html' },
  'admin@demo.fr': { password: 'demo123', role: 'admin', redirect: 'admin.html' },
};

loginForm.addEventListener('submit', (e) => {
  e.preventDefault();
  alertBox.classList.add('d-none');

  const email = emailInput.value.trim();
  const password = passwordInput.value;

  let valid = true;

  if (!email || !isValidEmail(email)) {
    showError('emailError');
    emailInput.classList.add('error');
    valid = false;
  } else {
    hideError('emailError');
    emailInput.classList.remove('error');
  }

  if (!password) {
    showError('passwordError');
    passwordInput.classList.add('error');
    valid = false;
  } else {
    hideError('passwordError');
    passwordInput.classList.remove('error');
  }

  if (!valid) return;

  submitBtn.disabled = true;
  submitBtn.textContent = 'Connexion en cours...';

  setTimeout(() => {
    const account = DEMO_ACCOUNTS[email.toLowerCase()];

    if (account && account.password === password) {
      sessionStorage.setItem('user', JSON.stringify({ email, role: account.role }));
      window.location.href = account.redirect;
    } else {
      alertBox.classList.remove('d-none');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Se connecter';
    }
  }, 800);
});

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(id) {
  document.getElementById(id).classList.add('show');
}

function hideError(id) {
  document.getElementById(id).classList.remove('show');
}

emailInput.addEventListener('input', () => {
  emailInput.classList.remove('error');
  hideError('emailError');
});

passwordInput.addEventListener('input', () => {
  passwordInput.classList.remove('error');
  hideError('passwordError');
});