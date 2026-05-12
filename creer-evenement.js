// ===== NAVBAR =====
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');
hamburger.addEventListener('click', () => mobileMenu.classList.toggle('open'));
document.addEventListener('click', (e) => {
  if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.remove('open');
  }
});

// ===== CHARACTER COUNTERS =====
const titre = document.getElementById('titre');
const titreCount = document.getElementById('titreCount');
titre.addEventListener('input', () => {
  titreCount.textContent = titre.value.length;
});

const description = document.getElementById('description');
const descriptionCount = document.getElementById('descriptionCount');
description.addEventListener('input', () => {
  descriptionCount.textContent = description.value.length;
});

// ===== UPLOAD AFFICHE =====
const afficheInput = document.getElementById('afficheInput');
const uploadPreview = document.getElementById('uploadPreview');
const uploadArea = document.getElementById('uploadArea');

afficheInput.addEventListener('change', () => {
  const file = afficheInput.files[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    alert('Le fichier est trop lourd (max 5 Mo).');
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    uploadPreview.src = e.target.result;
    uploadPreview.style.display = 'block';
    uploadArea.querySelector('.upload-icon').textContent = '✅';
    uploadArea.querySelector('.upload-text').textContent = file.name;
  };
  reader.readAsDataURL(file);
});

// Drag & drop
uploadArea.addEventListener('dragover', (e) => {
  e.preventDefault();
  uploadArea.style.borderColor = 'var(--primary)';
  uploadArea.style.background = 'var(--primary-light)';
});

uploadArea.addEventListener('dragleave', () => {
  uploadArea.style.borderColor = '';
  uploadArea.style.background = '';
});

uploadArea.addEventListener('drop', (e) => {
  e.preventDefault();
  uploadArea.style.borderColor = '';
  uploadArea.style.background = '';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    afficheInput.files = e.dataTransfer.files;
    afficheInput.dispatchEvent(new Event('change'));
  }
});

// ===== SET DATE MIN =====
const today = new Date().toISOString().split('T')[0];
document.getElementById('dateDebut').min = today;
document.getElementById('dateFin').min = today;

document.getElementById('dateDebut').addEventListener('change', () => {
  document.getElementById('dateFin').min = document.getElementById('dateDebut').value;
});

// ===== FORM VALIDATION =====
const createEventForm = document.getElementById('createEventForm');
const alertBox = document.getElementById('alertBox');
const successBox = document.getElementById('successBox');
const submitBtn = document.getElementById('submitBtn');

createEventForm.addEventListener('submit', (e) => {
  e.preventDefault();
  alertBox.classList.add('d-none');

  const fields = {
    titre: { el: document.getElementById('titre'), errId: 'titreError', check: (v) => v.length >= 3 },
    categorie: { el: document.getElementById('categorie'), errId: 'categorieError', check: (v) => v !== '' },
    association: { el: document.getElementById('association'), errId: 'associationError', check: (v) => v !== '' },
    description: { el: document.getElementById('description'), errId: 'descriptionError', check: (v) => v.length >= 50 },
    dateDebut: { el: document.getElementById('dateDebut'), errId: 'dateDebutError', check: (v) => v !== '' },
    heureDebut: { el: document.getElementById('heureDebut'), errId: 'heureDebutError', check: (v) => v !== '' },
    lieu: { el: document.getElementById('lieu'), errId: 'lieuError', check: (v) => v.length >= 3 },
    capacite: { el: document.getElementById('capacite'), errId: 'capaciteError', check: (v) => parseInt(v) > 0 },
  };

  let valid = true;

  for (const [, field] of Object.entries(fields)) {
    if (!field.check(field.el.value.trim())) {
      field.el.classList.add('error');
      document.getElementById(field.errId).classList.add('show');
      valid = false;
    } else {
      field.el.classList.remove('error');
      document.getElementById(field.errId).classList.remove('show');
    }
  }

  if (!valid) {
    alertBox.textContent = '❌ Veuillez corriger les erreurs dans le formulaire.';
    alertBox.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  submitBtn.disabled = true;
  submitBtn.textContent = 'Publication en cours...';

  setTimeout(() => {
    successBox.classList.remove('d-none');
    createEventForm.reset();
    uploadPreview.style.display = 'none';
    uploadArea.querySelector('.upload-icon').textContent = '📸';
    uploadArea.querySelector('.upload-text').textContent = 'Cliquer pour ajouter une affiche';
    titreCount.textContent = '0';
    descriptionCount.textContent = '0';
    submitBtn.disabled = false;
    submitBtn.textContent = '🚀 Publier l\'événement';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, 1000);
});

function saveDraft() {
  const data = {
    titre: document.getElementById('titre').value,
    categorie: document.getElementById('categorie').value,
    description: document.getElementById('description').value,
  };
  localStorage.setItem('eventDraft', JSON.stringify(data));
  alert('Brouillon sauvegardé !');
}

// Restore draft on load
window.addEventListener('load', () => {
  const draft = localStorage.getItem('eventDraft');
  if (draft) {
    const data = JSON.parse(draft);
    if (data.titre) document.getElementById('titre').value = data.titre;
    if (data.categorie) document.getElementById('categorie').value = data.categorie;
    if (data.description) document.getElementById('description').value = data.description;
    titreCount.textContent = (data.titre || '').length;
    descriptionCount.textContent = (data.description || '').length;
  }
});