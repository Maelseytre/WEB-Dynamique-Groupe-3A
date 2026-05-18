// ===== NAVBAR HAMBURGER =====
var hamburger = document.getElementById('hamburger');
var mobileMenu = document.getElementById('mobileMenu');

if (hamburger && mobileMenu) {
  hamburger.addEventListener('click', function() {
    mobileMenu.classList.toggle('open');
  });

  document.addEventListener('click', function(event) {
    if (!hamburger.contains(event.target) && !mobileMenu.contains(event.target)) {
      mobileMenu.classList.remove('open');
    }
  });
}

// ===== TABS =====
document.querySelectorAll('.tab-btn').forEach(function(button) {
  button.addEventListener('click', function() {
    var tab = button.getAttribute('data-tab');
    button.parentElement.querySelectorAll('.tab-btn').forEach(function(item) {
      item.classList.remove('active');
    });
    button.classList.add('active');
    document.querySelectorAll('.tab-panel').forEach(function(panel) {
      panel.classList.remove('active');
    });
    var panel = document.getElementById('tab-' + tab);
    if (panel) panel.classList.add('active');
  });
});

// ===== CONFIRMATION AVANT SOUMISSION =====
document.querySelectorAll('[data-confirm]').forEach(function(form) {
  form.addEventListener('submit', function(event) {
    if (!confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });
});