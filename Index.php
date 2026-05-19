<?php
require_once __DIR__ . '/includes/bootstrap.php';

$recherche = trim($_GET['q'] ?? '');
$categorieFiltree = $_GET['category'] ?? 'all';
$tri = $_GET['sort'] ?? 'date';
$conditions = ["e.status = 'published'", "e.date_debut >= CURDATE()"];
$parametres = [];
if ($recherche !== '') {
    $conditions[] = '(e.titre LIKE ? OR e.association LIKE ? OR e.lieu LIKE ?)';
    $parametres[] = "%$recherche%"; $parametres[] = "%$recherche%"; $parametres[] = "%$recherche%";
}
if ($categorieFiltree !== 'all' && $categorieFiltree !== '') {
    $conditions[] = 'e.categorie = ?';
    $parametres[] = $categorieFiltree;
}
$ordre = $tri === 'name' ? 'e.titre ASC' : 'e.date_debut ASC, e.heure_debut ASC';
$requete = bdd()->prepare('SELECT e.*, u.prenom, u.nom FROM events e JOIN users u ON u.id = e.organizer_id WHERE ' . implode(' AND ', $conditions) . " ORDER BY $ordre");
$requete->execute($parametres);
$evenements = $requete->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Accueil</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('accueil'); ?>
<section class="banniere">
  <div class="banniere-fond"></div><div class="banniere-voile"></div>
  <div class="conteneur banniere-interieur">
    <div class="banniere-accroche">OmnesEvent - Saison 2026</div>
    <h1>Tous les evenements Omnes,<br><em>au meme endroit</em></h1>
    <p>Soirees, competitions sportives, conferences, sorties culturelles... Decouvrez et reservez vos places en quelques clics.</p>
    <form class="boite-recherche" method="get">
      <input type="text" name="q" value="<?= echapper($recherche) ?>" placeholder="Rechercher un evenement, une association..." autocomplete="off">
      <button class="bouton bouton-primaire">Rechercher</button>
    </form>
  </div>
</section>
<div class="conteneur">
  <div class="filtres">
    <?php foreach (['all' => 'Tous', 'soiree' => 'Soiree', 'sport' => 'Sport', 'culture' => 'Culture', 'conference' => 'Conference'] as $cle => $libelle): ?>
      <a class="filtre <?= $categorieFiltree === $cle ? 'actif' : '' ?>" href="Index.php?category=<?= echapper($cle) ?>&q=<?= urlencode($recherche) ?>"><?= echapper($libelle) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<section class="section">
  <div class="conteneur">
    <div class="flex centrer espacer mb-3 entete-section">
      <div><h2 class="titre-section">Evenements a venir</h2><p class="texte-discret texte-petit"><?= count($evenements) ?> evenement(s) trouve(s)</p></div>
      <form method="get"><input type="hidden" name="q" value="<?= echapper($recherche) ?>"><input type="hidden" name="category" value="<?= echapper($categorieFiltree) ?>"><select class="champ select-compact" name="sort" onchange="this.form.submit()"><option value="date" <?= $tri === 'date' ? 'selected' : '' ?>>Trier par date</option><option value="name" <?= $tri === 'name' ? 'selected' : '' ?>>Trier par nom</option></select></form>
    </div>
    <div class="grille grille-3">
      <?php foreach ($evenements as $evenement): $placesPrises = compter_reservations((int) $evenement['id']); $remplissage = min(100, (int) round($placesPrises * 100 / max(1, (int) $evenement['capacite']))); ?>
        <a href="evenement-detail.php?id=<?= (int) $evenement['id'] ?>" class="carte-evenement" data-category="<?= echapper($evenement['categorie']) ?>">
          <div class="carte-evenement-image"><img src="<?= echapper($evenement['affiche'] ?: 'images/omneseducation_logo.jpeg') ?>" alt="<?= echapper($evenement['titre']) ?>"></div>
          <div class="carte-evenement-corps">
            <span class="carte-evenement-categorie category-<?= echapper($evenement['categorie']) ?>"><?= echapper(libelle_categorie($evenement['categorie'])) ?></span>
            <h3 class="carte-evenement-titre"><?= echapper($evenement['titre']) ?></h3>
            <div class="carte-evenement-meta"><span><?= echapper(formater_date($evenement['date_debut'])) ?></span><span><?= echapper($evenement['lieu']) ?></span><span><?= echapper($evenement['association']) ?></span></div>
            <div class="carte-evenement-pied"><div class="barre-capacite"><div class="remplissage-capacite <?= $placesPrises >= (int) $evenement['capacite'] ? 'remplissage-capacite--danger' : '' ?>" style="--fill: <?= $remplissage ?>%;"></div></div><span class="texte-capacite"><?= $placesPrises ?>/<?= (int) $evenement['capacite'] ?> places</span></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$evenements): ?><div class="etat-vide"><h3 class="titre-etat-vide">Aucun evenement trouve</h3><p class="description-etat-vide">Essaie avec d'autres mots-cles ou modifie les filtres.</p></div><?php endif; ?>
  </div>
</section>
<?php afficher_pied_de_page(); ?>
</body>
</html>