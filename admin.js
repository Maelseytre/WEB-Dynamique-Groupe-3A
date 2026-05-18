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

// ===== VALIDATION DES COMPTES =====
var pendingActionRow = null;
var pendingActionName = '';

function approveAccount(id) {
  var btn = event.target;
  var row = btn.parentNode.parentNode;
  var nameEl = row.querySelector('.fw-semibold');
  var name = nameEl ? nameEl.textContent : '';
  row.style.opacity = '0.5';
  row.style.transition = 'opacity 0.3s';
  setTimeout(function() {
    if (row.parentNode) row.parentNode.removeChild(row);
  }, 300);
  showToast('Compte de ' + name + ' approuvé. Un email de confirmation a été envoyé.', 'success');
  updatePendingCount(-1);
}

function rejectAccount(id) {
  var btn = event.target;
  var row = btn.parentNode.parentNode;
  var nameEl = row.querySelector('.fw-semibold');
  pendingActionName = nameEl ? nameEl.textContent : '';
  pendingActionRow = row;

  document.getElementById('actionModalTitle').textContent = 'Refuser le compte';
  document.getElementById('actionModalMessage').textContent = 'Êtes-vous sûr de vouloir refuser le compte organisateur de ' + pendingActionName + ' ?';
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').classList.remove('btn-primary');
  document.getElementById('actionModalConfirm').classList.add('btn-danger');
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'confirmRejectAccount()');
  document.getElementById('actionModal').classList.add('open');
}

function confirmRejectAccount() {
  document.getElementById('actionModal').classList.remove('open');
  if (pendingActionRow) {
    pendingActionRow.style.opacity = '0.5';
    var rowRef = pendingActionRow;
    setTimeout(function() {
      if (rowRef.parentNode) rowRef.parentNode.removeChild(rowRef);
    }, 300);
  }
  showToast('Compte de ' + pendingActionName + ' refusé.', 'warning');
  updatePendingCount(-1);
  pendingActionRow = null;
  pendingActionName = '';
}

function updatePendingCount(delta) {
  var badge = document.querySelector('[data-tab="comptes"] .badge');
  if (badge) {
    var current = parseInt(badge.textContent) || 0;
    badge.textContent = Math.max(0, current + delta);
  }
}

// ===== UTILISATEURS =====
function viewUser(id) {
  showToast('Ouverture du profil utilisateur...', 'success');
}

function suspendUser(id) {
  if (confirm('Suspendre cet utilisateur ? Il ne pourra plus se connecter.')) {
    showToast('Utilisateur suspendu.', 'warning');
  }
}

function deleteUser(id) {
  document.getElementById('actionModalTitle').textContent = 'Supprimer l\'utilisateur';
  document.getElementById('actionModalMessage').textContent = 'Cette action est irréversible. Toutes les données de l\'utilisateur seront supprimées.';
  document.getElementById('actionReasonGroup').style.display = 'none';
  document.getElementById('actionModalConfirm').className = 'btn btn-danger';
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'executeAction()');
  document.getElementById('actionModal').classList.add('open');
}

// ===== ÉVÉNEMENTS =====
function viewEvent(id) {
  window.location.href = 'evenement-detail.php?id=' + id;
}

function removeEvent(id) {
  document.getElementById('actionModalTitle').textContent = 'Retirer l\'événement';
  document.getElementById('actionModalMessage').textContent = 'L\'événement sera retiré de la plateforme. L\'organisateur et les inscrits seront notifiés.';
  document.getElementById('actionReasonGroup').style.display = 'block';
  document.getElementById('actionModalConfirm').className = 'btn btn-danger';
  document.getElementById('actionModalConfirm').setAttribute('onclick', 'executeAction()');
  document.getElementById('actionModal').classList.add('open');
}

// ===== SIGNALEMENTS =====
function warnUser(id) {
  showToast('Avertissement envoyé à l\'organisateur.', 'warning');
}

function contactOrganizer(id) {
  showToast('Email envoyé à l\'organisateur.', 'success');
}

function dismissSignal(id) {
  var btn = event.target;
  var card = btn.parentNode;
  while (card && !card.classList.contains('card')) {
    card = card.parentNode;
  }
  if (card) {
    card.style.opacity = '0.5';
    setTimeout(function() {
      if (card.parentNode) card.parentNode.removeChild(card);
    }, 300);
  }
  showToast('Signalement ignoré.', 'success');
}

// ===== RECHERCHE UTILISATEURS =====
var userSearch = document.getElementById('userSearch');
if (userSearch) {
  userSearch.addEventListener('input', function() {
    var query = userSearch.value.toLowerCase();
    var rows = document.querySelectorAll('#tab-utilisateurs tbody tr');
    for (var i = 0; i < rows.length; i++) {
      var text = rows[i].textContent.toLowerCase();
      rows[i].style.display = text.indexOf(query) !== -1 ? '' : 'none';
    }
  });
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

function executeAction() {
  document.getElementById('actionModal').classList.remove('open');
}

// ===== TOAST =====
function showToast(message, type) {
  if (!type) type = 'success';
  var colors = { success: 'alert-success', warning: 'alert-warning', danger: 'alert-danger' };
  var toast = document.createElement('div');
  toast.className = 'alert ' + (colors[type] || 'alert-success');
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--shadow-lg);min-width:300px;';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(function() {
    if (toast.parentNode) toast.parentNode.removeChild(toast);
  }, 3500);
}