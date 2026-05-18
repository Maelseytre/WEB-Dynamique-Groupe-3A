// ===== TABS =====
var tabBtns = document.querySelectorAll('.tab-btn');
var tabPanels = document.querySelectorAll('.tab-panel');

for (var i = 0; i < tabBtns.length; i++) {
  tabBtns[i].addEventListener('click', function() {
    for (var j = 0; j < tabBtns.length; j++) tabBtns[j].classList.remove('active');
    for (var j = 0; j < tabPanels.length; j++) tabPanels[j].classList.remove('active');
    this.classList.add('active');
    var panel = document.getElementById('tab-' + this.getAttribute('data-tab'));
    if (panel) panel.classList.add('active');
  });
}

// ===== AFFICHAGE NOM DU FICHIER AVATAR =====
var avatarInput = document.getElementById('avatarInput');
if (avatarInput) {
  avatarInput.addEventListener('change', function() {
    var file = avatarInput.files[0];
    if (!file) return;
    var preview = document.getElementById('avatarPreview');
    if (preview) preview.textContent = file.name;
  });
}

// ===== FORMULAIRE PROFIL =====
var profileForm = document.getElementById('profileForm');

if (profileForm) {
  profileForm.addEventListener('submit', function(e) {
    var prenom = document.getElementById('prenom') ? document.getElementById('prenom').value.trim() : '';
    var nom = document.getElementById('nom') ? document.getElementById('nom').value.trim() : '';
    var email = document.getElementById('email') ? document.getElementById('email').value.trim() : '';
    var valid = true;

    if (!prenom) { showError('prenomError'); valid = false; } else { hideError('prenomError'); }
    if (!nom) { showError('nomError'); valid = false; } else { hideError('nomError'); }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('emailError'); valid = false; } else { hideError('emailError'); }

    if (!valid) e.preventDefault();
  });
}

function resetForm() {
  var form = document.getElementById('profileForm');
  if (form) form.reset();
}

// ===== FORMULAIRE MOT DE PASSE =====
var passwordForm = document.getElementById('passwordForm');

if (passwordForm) {
  passwordForm.addEventListener('submit', function(e) {
    var current = document.getElementById('currentPassword') ? document.getElementById('currentPassword').value : '';
    var newPwd = document.getElementById('newPassword') ? document.getElementById('newPassword').value : '';
    var confirm = document.getElementById('confirmNewPassword') ? document.getElementById('confirmNewPassword').value : '';
    var valid = true;

    if (!current) { showError('currentPasswordError'); valid = false; } else { hideError('currentPasswordError'); }
    if (newPwd.length < 8) { showError('newPasswordError'); valid = false; } else { hideError('newPasswordError'); }
    if (newPwd !== confirm) { showError('confirmNewPasswordError'); valid = false; } else { hideError('confirmNewPasswordError'); }

    if (!valid) e.preventDefault();
  });
}

// ===== NOTIFICATIONS =====
function saveNotifications() {
  alert('Préférences de notifications enregistrées !');
}

// ===== SUPPRESSION DE COMPTE =====
function confirmDeleteAccount() {
  var modal = document.getElementById('deleteModal');
  if (modal) modal.classList.add('open');
}

function deleteAccount() {
  var confirmEl = document.getElementById('deleteConfirm');
  var val = confirmEl ? confirmEl.value : '';
  if (val === 'SUPPRIMER') {
    window.location.href = 'supprimer-compte.php';
  } else {
    alert('Veuillez taper "SUPPRIMER" pour confirmer.');
  }
}

function revokeSession() {
  if (confirm('Révoquer cette session ?')) {
    alert('Session révoquée.');
  }
}

// ===== FERMER MODALS EN CLIQUANT SUR LE FOND =====
var overlays = document.querySelectorAll('.modal-overlay');
for (var i = 0; i < overlays.length; i++) {
  overlays[i].addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
    }
  });
}

function showError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideError(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('show');
}