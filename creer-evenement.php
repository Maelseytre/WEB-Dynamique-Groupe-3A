<?php
require_once __DIR__ . '/includes/bootstrap.php';
$utilisateur = exiger_role(['organisateur', 'admin']);
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $association = trim($_POST['association'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dateDebut = $_POST['dateDebut'] ?? '';
    $dateFin = $_POST['dateFin'] ?: null;
    $heureDebut = $_POST['heureDebut'] ?? '';
    $heureFin = $_POST['heureFin'] ?: null;
    $lieu = trim($_POST['lieu'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $capacite = (int) ($_POST['capacite'] ?? 0);
    $prix = (float) ($_POST['prix'] ?? 0);
    $statut = ($_POST['mode'] ?? 'publish') === 'draft' ? 'draft' : 'published';

    if ($titre === '' || $categorie === '' || $association === '' || strlen($description) < 20 || $dateDebut === '' || $heureDebut === '' || $lieu === '' || $capacite < 1) {
        $erreurs[] = 'Merci de remplir tous les champs obligatoires.';
    }

    $affiche = null;
    if (!$erreurs && !empty($_FILES['affiche']['name']) && is_uploaded_file($_FILES['affiche']['tmp_name'])) {
        $typesPermis = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif'];
        $typeMime = mime_content_type($_FILES['affiche']['tmp_name']);
        if (!isset($typesPermis[$typeMime]) || $_FILES['affiche']['size'] > 5 * 1024 * 1024) {
            $erreurs[] = 'Affiche invalide: JPG, PNG, WEBP ou AVIF, max 5 Mo.';
        } else {
            $nomFichier = 'uploads/events/event-' . time() . '-' . random_int(1000, 9999) . '.' . $typesPermis[$typeMime];
            move_uploaded_file($_FILES['affiche']['tmp_name'], __DIR__ . '/' . $nomFichier);
            $affiche = $nomFichier;
        }
    }

    if (!$erreurs) {
        $requete = bdd()->prepare('
            INSERT INTO events (organizer_id, titre, categorie, association, description, date_debut, date_fin, heure_debut, heure_fin, lieu, adresse, capacite, prix, affiche, waitlist_enabled, manual_validation, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $requete->execute([$utilisateur['id'], $titre, $categorie, $association, $description, $dateDebut, $dateFin, $heureDebut, $heureFin, $lieu, $adresse, $capacite, $prix, $affiche, isset($_POST['listeAttente']) ? 1 : 0, isset($_POST['validation']) ? 1 : 0, $statut]);
        message_flash('success', $statut === 'draft' ? 'Brouillon sauvegarde.' : 'Evenement publie avec succes.');
        rediriger('tableau-de-bord.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Creer un evenement</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('creer'); ?>
<div class="entete-page"><div class="conteneur"><h1>Creer un evenement</h1><p>Publie un nouvel evenement pour la communaute Omnes.</p></div></div>
<section class="section"><div class="conteneur conteneur-etroit">
  <?php foreach ($erreurs as $erreur): ?><div class="alerte alerte-danger"><?= echapper($erreur) ?></div><?php endforeach; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="carte mb-3"><div class="carte-entete"><h3>Informations generales</h3></div><div class="carte-corps">
      <div class="groupe-champ"><label class="etiquette-champ" for="titre">Titre de l'evenement *</label><input type="text" id="titre" name="titre" class="champ" maxlength="100" required></div>
      <div class="rangee-champ rangee-champ-2"><div class="groupe-champ mb-0"><label class="etiquette-champ" for="categorie">Categorie *</label><select id="categorie" name="categorie" class="champ" required><option value="">Selectionner...</option><option value="soiree">Soiree</option><option value="sport">Sport</option><option value="culture">Culture</option><option value="conference">Conference</option></select></div><div class="groupe-champ mb-0"><label class="etiquette-champ" for="association">Association *</label><select id="association" name="association" class="champ" required><option value="<?= echapper($utilisateur['association'] ?: '') ?>"><?= echapper($utilisateur['association'] ?: 'Selectionner...') ?></option><option>BDE Omnes</option><option>BDS Omnes</option><option>Junior Entreprise</option><option>Club Culturel</option><option>Club Cinema</option><option>Asso Bien-etre</option></select></div></div>
      <div class="groupe-champ mt-2"><label class="etiquette-champ" for="description">Description *</label><textarea id="description" name="description" class="champ" rows="5" maxlength="1000" required></textarea></div>
    </div></div>
    <div class="carte mb-3"><div class="carte-entete"><h3>Date, heure & lieu</h3></div><div class="carte-corps">
      <div class="rangee-champ rangee-champ-2"><div class="groupe-champ mb-0"><label class="etiquette-champ" for="dateDebut">Date de debut *</label><input type="date" id="dateDebut" name="dateDebut" class="champ" required></div><div class="groupe-champ mb-0"><label class="etiquette-champ" for="dateFin">Date de fin</label><input type="date" id="dateFin" name="dateFin" class="champ"></div></div>
      <div class="rangee-champ rangee-champ-2 mt-2"><div class="groupe-champ mb-0"><label class="etiquette-champ" for="heureDebut">Heure de debut *</label><input type="time" id="heureDebut" name="heureDebut" class="champ" value="18:00" required></div><div class="groupe-champ mb-0"><label class="etiquette-champ" for="heureFin">Heure de fin</label><input type="time" id="heureFin" name="heureFin" class="champ" value="23:00"></div></div>
      <div class="groupe-champ mt-2"><label class="etiquette-champ" for="lieu">Lieu / Adresse *</label><input type="text" id="lieu" name="lieu" class="champ" required></div>
      <div class="groupe-champ"><label class="etiquette-champ" for="adresse">Adresse complete</label><input type="text" id="adresse" name="adresse" class="champ"></div>
    </div></div>
    <div class="carte mb-3"><div class="carte-entete"><h3>Capacite & Tarif</h3></div><div class="carte-corps"><div class="rangee-champ rangee-champ-2"><div class="groupe-champ mb-0"><label class="etiquette-champ" for="capacite">Capacite maximale *</label><input type="number" id="capacite" name="capacite" class="champ" min="1" max="10000" required></div><div class="groupe-champ mb-0"><label class="etiquette-champ" for="prix">Prix (EUR)</label><input type="number" id="prix" name="prix" class="champ" min="0" step="0.01" value="0"></div></div></div></div>
    <div class="carte mb-3"><div class="carte-entete"><h3>Affiche de l'evenement</h3></div><div class="carte-corps"><input type="file" name="affiche" accept="image/*" class="champ"></div></div>
    <div class="carte mb-4"><div class="carte-entete"><h3>Options</h3></div><div class="carte-corps"><div class="groupe-champ"><input type="checkbox" id="listeAttente" name="listeAttente" class="case-inline" checked> <label for="listeAttente">Activer la liste d'attente</label></div><div class="groupe-champ mb-0"><input type="checkbox" id="validation" name="validation" class="case-inline"> <label for="validation">Validation manuelle des inscriptions</label></div></div></div>
    <div class="flex ecart-2 actions-formulaire"><button type="submit" name="mode" value="draft" class="bouton bouton-fantome bouton-grand">Sauvegarder le brouillon</button><button type="submit" name="mode" value="publish" class="bouton bouton-primaire bouton-grand">Publier l'evenement</button></div>
  </form>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>