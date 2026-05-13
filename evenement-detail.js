// ===== NAVBAR =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => mobileMenu.classList.toggle('open'));
document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

// ===== MODAL RÉSERVATION =====
function showReservationModal() {
  const user = localStorage.getItem('user');
  if (!user) {
    document.getElementById('loginAlert').style.display = 'flex';
    document.getElementById('reservationForm').style.display = 'none';
  } else {
    document.getElementById('loginAlert').style.display = 'none';
    document.getElementById('reservationForm').style.display = 'block';
  }
  document.getElementById('reservationModal').classList.add('open');
}

function closeModal() {
  document.getElementById('reservationModal').classList.remove('open');
}

function confirmReservation() {
  const user = localStorage.getItem('user');
  if (!user) {
    window.location.href = 'connexion.html';
    return;
  }

  const num = Math.floor(1000 + Math.random() * 9000);
  const year = new Date().getFullYear();
  const code = `OE-${year}-${num}`;
  const shortCode = `OE-${num}`;

  const billet = {
    code,
    shortCode,
    eventName: "Soirée d'Intégration BDE 2025",
    date: "Vendredi 20 Juin 2025 · 20h00",
    lieu: "Salle des fêtes, Campus Paris",
    category: "soiree",
    categoryLabel: "Soirée",
    nbPlaces: parseInt(document.getElementById('nbPlaces').value) || 1,
    status: "upcoming"
  };

  const billets = JSON.parse(localStorage.getItem('billets') || '[]');
  billets.push(billet);
  localStorage.setItem('billets', JSON.stringify(billets));

  const codeEl = document.querySelector('#successModal strong');
  if (codeEl) codeEl.textContent = code;

  closeModal();
  setTimeout(() => {
    document.getElementById('successModal').classList.add('open');
  }, 200);
}

function closeSuccessModal() {
  document.getElementById('successModal').classList.remove('open');
}

// Fermer les modals en cliquant sur le fond
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.classList.remove('open');
    }
  });
});

// ===== PARTAGER =====
function shareEvent() {
  if (navigator.share) {
    navigator.share({
      title: 'Soirée d\'Intégration BDE 2025',
      text: 'Rejoins-moi à cet événement sur OmnesEvent !',
      url: window.location.href,
    });
  } else {
    navigator.clipboard.writeText(window.location.href).then(() => {
      alert('Lien copié dans le presse-papier !');
    });
  }
}

// ===== SAUVEGARDER =====
let saved = false;
function saveEvent() {
  const btn = document.getElementById('saveBtn');
  saved = !saved;
  btn.textContent = saved ? '🔖 Sauvegardé !' : '🔖 Sauvegarder';
  btn.style.color = saved ? 'var(--primary)' : '';
}