// ===== NAVBAR =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => mobileMenu.classList.toggle('open'));
document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

// ===== CHARGEMENT DES DONNÉES UTILISATEUR =====
requireAuth();

(function loadUserData() {
  const user = getUser();
  if (!user) return;

  const prenom = user.prenom || '';
  const nom = user.nom || '';
  const email = user.email || '';
  const initiales = ((prenom[0] || '') + (nom[0] || '')).toUpperCase() || '?';

  document.getElementById('displayName').textContent = `${prenom} ${nom}`.trim() || email;
  document.getElementById('displayEmail').textContent = email;
  document.getElementById('avatarDisplay').textContent = initiales;

  const prenomInput = document.getElementById('prenom');
  const nomInput = document.getElementById('nom');
  const emailInput = document.getElementById('email');
  if (prenomInput) prenomInput.value = prenom;
  if (nomInput) nomInput.value = nom;
  if (emailInput) emailInput.value = email;
})();

// ===== TABS =====
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanels = document.querySelectorAll('.tab-panel');

tabBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    tabBtns.forEach(b => b.classList.remove('active'));
    tabPanels.forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
  });
});

// ===== AVATAR PREVIEW =====
const avatarInput = document.getElementById('avatarInput');
avatarInput.addEventListener('change', () => {
  const file = avatarInput.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
    document.getElementById('avatarDisplay').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
  };
  reader.readAsDataURL(file);
});

// ===== PROFILE FORM =====
const profileForm = document.getElementById('profileForm');
const saveAlert = document.getElementById('saveAlert');

profileForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const prenom = document.getElementById('prenom').value.trim();
  const nom = document.getElementById('nom').value.trim();
  const email = document.getElementById('email').value.trim();

  let valid = true;
  if (!prenom) { showError('prenomError'); valid = false; } else hideError('prenomError');
  if (!nom) { showError('nomError'); valid = false; } else hideError('nomError');
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('emailError'); valid = false; } else hideError('emailError');

  if (!valid) return;

  document.getElementById('displayName').textContent = `${prenom} ${nom}`;
  document.getElementById('displayEmail').textContent = email;
  const initiales = (prenom[0] + nom[0]).toUpperCase();
  document.getElementById('avatarDisplay').textContent = initiales;
  document.getElementById('avatarPreview').textContent = initiales;

  const user = getUser();
  if (user) {
    setUser({ ...user, prenom, nom, email });
    updateNavbarAuth();
  }

  saveAlert.classList.remove('d-none');
  setTimeout(() => saveAlert.classList.add('d-none'), 3000);
});

function resetForm() {
  profileForm.reset();
}

// ===== PASSWORD FORM =====
const passwordForm = document.getElementById('passwordForm');
const passwordAlert = document.getElementById('passwordAlert');

passwordForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const current = document.getElementById('currentPassword').value;
  const newPwd = document.getElementById('newPassword').value;
  const confirm = document.getElementById('confirmNewPassword').value;

  let valid = true;
  if (!current) { showError('currentPasswordError'); valid = false; } else hideError('currentPasswordError');
  if (newPwd.length < 8) { showError('newPasswordError'); valid = false; } else hideError('newPasswordError');
  if (newPwd !== confirm) { showError('confirmNewPasswordError'); valid = false; } else hideError('confirmNewPasswordError');

  if (!valid) return;

  passwordAlert.classList.remove('d-none');
  passwordForm.reset();
  setTimeout(() => passwordAlert.classList.add('d-none'), 3000);
});

// ===== NOTIFICATIONS =====
function saveNotifications() {
  alert('Préférences de notifications enregistrées !');
}

// ===== DELETE ACCOUNT =====
function confirmDeleteAccount() {
  document.getElementById('deleteModal').classList.add('open');
}

function deleteAccount() {
  const confirm = document.getElementById('deleteConfirm').value;
  if (confirm === 'SUPPRIMER') {
    sessionStorage.clear();
    localStorage.clear();
    alert('Compte supprimé. Vous allez être redirigé.');
    window.location.href = 'Index.html';
  } else {
    alert('Veuillez taper "SUPPRIMER" pour confirmer.');
  }
}

function revokeSession() {
  if (confirm('Révoquer cette session ?')) {
    alert('Session révoquée.');
  }
}

// ===== MODAL CLOSE ON OVERLAY =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

function showError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideError(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}