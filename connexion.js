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
  'participant@demo.fr': { password: 'demo123', role: 'participant', prenom: 'Demo', nom: 'Participant', redirect: 'Index.html' },
  'organisateur@demo.fr': { password: 'demo123', role: 'organisateur', prenom: 'Demo', nom: 'Organisateur', redirect: 'tableau-de-bord.html' },
  'admin@demo.fr': { password: 'demo123', role: 'admin', prenom: 'Demo', nom: 'Admin', redirect: 'admin.html' },
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
      localStorage.setItem('user', JSON.stringify({ email, role: account.role, prenom: account.prenom, nom: account.nom }));
      window.location.href = account.redirect;
      return;
    }

    const users = JSON.parse(localStorage.getItem('users') || '[]');
    const registeredUser = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);

    if (registeredUser) {
      const redirect = registeredUser.role === 'organisateur' ? 'tableau-de-bord.html' : 'Index.html';
      localStorage.setItem('user', JSON.stringify({
        email: registeredUser.email,
        role: registeredUser.role,
        prenom: registeredUser.prenom,
        nom: registeredUser.nom
      }));
      window.location.href = redirect;
      return;
    }

    alertBox.classList.remove('d-none');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Se connecter';
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