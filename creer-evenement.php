<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_role(['organisateur', 'admin']);
$errors = [];

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
    $status = ($_POST['mode'] ?? 'publish') === 'draft' ? 'draft' : 'published';

    if ($titre === '' || $categorie === '' || $association === '' || strlen($description) < 20 || $dateDebut === '' || $heureDebut === '' || $lieu === '' || $capacite < 1) {
        $errors[] = 'Merci de remplir tous les champs obligatoires.';
    }

    $affiche = null;
    if (!$errors && !empty($_FILES['affiche']['name']) && is_uploaded_file($_FILES['affiche']['tmp_name'])) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif'];
        $mime = mime_content_type($_FILES['affiche']['tmp_name']);
        if (!isset($allowed[$mime]) || $_FILES['affiche']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Affiche invalide: JPG, PNG, WEBP ou AVIF, max 5 Mo.';
        } else {
            $name = 'uploads/events/event-' . time() . '-' . random_int(1000, 9999) . '.' . $allowed[$mime];
            move_uploaded_file($_FILES['affiche']['tmp_name'], __DIR__ . '/' . $name);
            $affiche = $name;
        }
    }

    if (!$errors) {
        $stmt = db()->prepare('
            INSERT INTO events (organizer_id, titre, categorie, association, description, date_debut, date_fin, heure_debut, heure_fin, lieu, adresse, capacite, prix, affiche, waitlist_enabled, manual_validation, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$user['id'], $titre, $categorie, $association, $description, $dateDebut, $dateFin, $heureDebut, $heureFin, $lieu, $adresse, $capacite, $prix, $affiche, isset($_POST['listeAttente']) ? 1 : 0, isset($_POST['validation']) ? 1 : 0, $status]);
        flash('success', $status === 'draft' ? 'Brouillon sauvegarde.' : 'Evenement publie avec succes.');
        redirect('tableau-de-bord.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Creer un evenement</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('create'); ?>
<div class="page-header"><div class="container"><h1>Creer un evenement</h1><p>Publie un nouvel evenement pour la communaute Omnes.</p></div></div>
<section class="section"><div class="container container-narrow">
  <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endforeach; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="card mb-3"><div class="card-header"><h3>Informations generales</h3></div><div class="card-body">
      <div class="form-group"><label class="form-label" for="titre">Titre de l'evenement *</label><input type="text" id="titre" name="titre" class="form-control" maxlength="100" required></div>
      <div class="form-row form-row-2"><div class="form-group mb-0"><label class="form-label" for="categorie">Categorie *</label><select id="categorie" name="categorie" class="form-control" required><option value="">Selectionner...</option><option value="soiree">Soiree</option><option value="sport">Sport</option><option value="culture">Culture</option><option value="conference">Conference</option></select></div><div class="form-group mb-0"><label class="form-label" for="association">Association *</label><select id="association" name="association" class="form-control" required><option value="<?= h($user['association'] ?: '') ?>"><?= h($user['association'] ?: 'Selectionner...') ?></option><option>BDE Omnes</option><option>BDS Omnes</option><option>Junior Entreprise</option><option>Club Culturel</option><option>Club Cinema</option><option>Asso Bien-etre</option></select></div></div>
      <div class="form-group mt-2"><label class="form-label" for="description">Description *</label><textarea id="description" name="description" class="form-control" rows="5" maxlength="1000" required></textarea></div>
    </div></div>
    <div class="card mb-3"><div class="card-header"><h3>Date, heure & lieu</h3></div><div class="card-body">
      <div class="form-row form-row-2"><div class="form-group mb-0"><label class="form-label" for="dateDebut">Date de debut *</label><input type="date" id="dateDebut" name="dateDebut" class="form-control" required></div><div class="form-group mb-0"><label class="form-label" for="dateFin">Date de fin</label><input type="date" id="dateFin" name="dateFin" class="form-control"></div></div>
      <div class="form-row form-row-2 mt-2"><div class="form-group mb-0"><label class="form-label" for="heureDebut">Heure de debut *</label><input type="time" id="heureDebut" name="heureDebut" class="form-control" value="18:00" required></div><div class="form-group mb-0"><label class="form-label" for="heureFin">Heure de fin</label><input type="time" id="heureFin" name="heureFin" class="form-control" value="23:00"></div></div>
      <div class="form-group mt-2"><label class="form-label" for="lieu">Lieu / Adresse *</label><input type="text" id="lieu" name="lieu" class="form-control" required></div>
      <div class="form-group"><label class="form-label" for="adresse">Adresse complete</label><input type="text" id="adresse" name="adresse" class="form-control"></div>
    </div></div>
    <div class="card mb-3"><div class="card-header"><h3>Capacite & Tarif</h3></div><div class="card-body"><div class="form-row form-row-2"><div class="form-group mb-0"><label class="form-label" for="capacite">Capacite maximale *</label><input type="number" id="capacite" name="capacite" class="form-control" min="1" max="10000" required></div><div class="form-group mb-0"><label class="form-label" for="prix">Prix (EUR)</label><input type="number" id="prix" name="prix" class="form-control" min="0" step="0.01" value="0"></div></div></div></div>
    <div class="card mb-3"><div class="card-header"><h3>Affiche de l'evenement</h3></div><div class="card-body"><input type="file" name="affiche" accept="image/*" class="form-control"></div></div>
    <div class="card mb-4"><div class="card-header"><h3>Options</h3></div><div class="card-body"><div class="form-group"><input type="checkbox" id="listeAttente" name="listeAttente" class="checkbox-inline" checked> <label for="listeAttente">Activer la liste d'attente</label></div><div class="form-group mb-0"><input type="checkbox" id="validation" name="validation" class="checkbox-inline"> <label for="validation">Validation manuelle des inscriptions</label></div></div></div>
    <div class="d-flex gap-2 form-actions"><button type="submit" name="mode" value="draft" class="btn btn-ghost btn-lg">Sauvegarder le brouillon</button><button type="submit" name="mode" value="publish" class="btn btn-primary btn-lg">Publier l'evenement</button></div>
  </form>
</div></section>
<?php footer_html(); ?>
</body>
</html>
