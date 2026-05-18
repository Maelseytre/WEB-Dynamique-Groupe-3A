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

// ===== ACTIONS SUR LES ÉVÉNEMENTS =====
var eventToDelete = null;

function viewParticipants(evtId) {
  for (var i = 0; i < tabBtns.length; i++) tabBtns[i].classList.remove('active');
  for (var i = 0; i < tabPanels.length; i++) tabPanels[i].classList.remove('active');
  var inscritsBtn = document.querySelector('[data-tab="inscrits"]');
  var inscritsPanel = document.getElementById('tab-inscrits');
  if (inscritsBtn) inscritsBtn.classList.add('active');
  if (inscritsPanel) inscritsPanel.classList.add('active');
}

function deleteEvent(evtId) {
  eventToDelete = evtId;
  var modal = document.getElementById('deleteModal');
  if (modal) modal.classList.add('open');
}

function confirmDelete() {
  var modal = document.getElementById('deleteModal');
  if (modal) modal.classList.remove('open');
  if (eventToDelete) {
    window.location.href = 'supprimer-evenement.php?id=' + eventToDelete;
  }
}

function publishEvent(evtId) {
  showToast('Événement publié avec succès !', 'success');
}

// ===== PARTICIPANTS =====
function validatePresence(participantId) {
  var btn = event.target;
  if (btn) btn.outerHTML = '<span class="badge badge-success">Présent(e)</span>';
  showToast('Présence validée !', 'success');
}

function exportCSV() {
  window.location.href = 'export-inscrits.php';
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

// ===== TOAST =====
function showToast(message, type) {
  if (!type) type = 'success';
  var toast = document.createElement('div');
  toast.className = 'alert alert-' + type;
  toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:300;box-shadow:var(--shadow-lg);min-width:280px;';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(function() {
    if (toast.parentNode) toast.parentNode.removeChild(toast);
  }, 3500);
}