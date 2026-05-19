// ===== MENU HAMBURGER =====
var boutonHamburger = document.getElementById('boutonHamburger');
var menuMobile = document.getElementById('menuMobile');

if (boutonHamburger && menuMobile) {
  boutonHamburger.addEventListener('click', function() {
    menuMobile.classList.toggle('ouvert');
  });

  document.addEventListener('click', function(evenement) {
    if (!boutonHamburger.contains(evenement.target) && !menuMobile.contains(evenement.target)) {
      menuMobile.classList.remove('ouvert');
    }
  });
}

// ===== ONGLETS =====
document.querySelectorAll('.bouton-onglet').forEach(function(bouton) {
  bouton.addEventListener('click', function() {
    var onglet = bouton.getAttribute('data-tab');
    bouton.parentElement.querySelectorAll('.bouton-onglet').forEach(function(element) {
      element.classList.remove('actif');
    });
    bouton.classList.add('actif');
    document.querySelectorAll('.panneau-onglet').forEach(function(panneau) {
      panneau.classList.remove('actif');
    });
    var panneau = document.getElementById('tab-' + onglet);
    if (panneau) panneau.classList.add('actif');
  });
});

// ===== CONFIRMATION AVANT SOUMISSION =====
document.querySelectorAll('[data-confirm]').forEach(function(formulaire) {
  formulaire.addEventListener('submit', function(evenement) {
    if (!confirm(formulaire.dataset.confirm)) {
      evenement.preventDefault();
    }
  });
});