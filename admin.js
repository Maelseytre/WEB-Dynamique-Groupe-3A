// ===== NAVBAR =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => mobileMenu.classList.toggle('open'));
document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

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

// ===== ACCOUNTS APPROVAL =====
function approveAccount(id) {
  const row = event.target.closest('tr');
  const name = row.querySelector('.fw-semibold').textContent;
  row.style.opacity = '0.5';
  row.style.transition = 'opacity 0.3s';
  setTimeout(() => row.remove(), 300);
  showToast(`✅ Compte de ${name} approuvé. Un email de confirmation a été envoyé.`, 'success');
  updatePendingCount(-1);
}

function rejectAccount(id) {
  const row = event.target.closest('tr');
  const name = row.querySelector('.fw-semibold').textContent;

  document.getElementById('actionModalTitle').textContent = 'Refuser le compte';
  document.getElementById('actionModalMessage').textContent = `Êtes-vous sûr de vouloir refuser le compte organisateur de ${name} ?`;
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').classList.remove('btn-primary');
  document.getElementById('actionModalConfirm').classList.add('btn-danger');
  document.getElementById('actionModal').classList.add('open');

  document.getElementById('actionModalConfirm').onclick = () => {
    document.getElementById('actionModal').classList.remove('open');
    row.style.opacity = '0.5';
    setTimeout(() => row.remove(), 300);
    showToast(`❌ Compte de ${name} refusé.`, 'warning');
    updatePendingCount(-1);
  };
}

function updatePendingCount(delta) {
  const badge = document.querySelector('[data-tab="comptes"] .badge');
  if (badge) {
    const current = parseInt(badge.textContent) || 0;
    badge.textContent = Math.max(0, current + delta);
  }
}

// ===== USERS =====
function viewUser(id) {
  showToast('Ouverture du profil utilisateur...', 'success');
}

function suspendUser(id) {
  if (confirm('Suspendre cet utilisateur ? Il ne pourra plus se connecter.')) {
    showToast('⏸ Utilisateur suspendu.', 'warning');
  }
}

function deleteUser(id) {
  document.getElementById('actionModalTitle').textContent = 'Supprimer l\'utilisateur';
  document.getElementById('actionModalMessage').textContent = 'Cette action est irréversible. Toutes les données de l\'utilisateur seront supprimées.';
  document.getElementById('actionReasonGroup').style.display = 'none';
  document.getElementById('actionModalConfirm').className = 'btn btn-danger';
  document.getElementById('actionModal').classList.add('open');
  document.getElementById('actionModalConfirm').onclick = () => {
    document.getElementById('actionModal').classList.remove('open');
    showToast('🗑 Utilisateur supprimé.', 'danger');
  };
}

// ===== EVENTS =====
function viewEvent(id) {
  window.location.href = 'evenement-detail.html';
}

function removeEvent(id) {
  document.getElementById('actionModalTitle').textContent = 'Retirer l\'événement';
  document.getElementById('actionModalMessage').textContent = 'L\'événement sera retiré de la plateforme. L\'organisateur et les inscrits seront notifiés.';
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').className = 'btn btn-danger';
  document.getElementById('actionModal').classList.add('open');
  document.getElementById('actionModalConfirm').onclick = () => {
    document.getElementById('actionModal').classList.remove('open');
    showToast('🚫 Événement retiré de la plateforme.', 'danger');
  };
}

// ===== SIGNALEMENTS =====
function warnUser(id) {
  showToast('⚠️ Avertissement envoyé à l\'organisateur.', 'warning');
}

function contactOrganizer(id) {
  showToast('📧 Email envoyé à l\'organisateur.', 'success');
}

function dismissSignal(id) {
  const card = event.target.closest('.card');
  card.style.opacity = '0.5';
  setTimeout(() => card.remove(), 300);
  showToast('Signalement ignoré.', 'success');
}

// ===== SEARCH FILTER =====
const userSearch = document.getElementById('userSearch');
if (userSearch) {
  userSearch.addEventListener('input', () => {
    const query = userSearch.value.toLowerCase();
    document.querySelectorAll('#tab-utilisateurs tbody tr').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });
}

// ===== MODAL CLOSE =====
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

function executeAction() {
  document.getElementById('actionModal').classList.remove('open');
}

// ===== TOAST =====
function showToast(message, type = 'success') {
  const colors = { success: 'alert-success', warning: 'alert-warning', danger: 'alert-danger' };
  const toast = document.createElement('div');
  toast.className = `alert ${colors[type] || 'alert-success'}`;
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--shadow-lg);min-width:300px;';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}