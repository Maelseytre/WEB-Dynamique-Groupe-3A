<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? $_POST['event_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reserve') {
    $utilisateur = exiger_connexion();
    $idEvenement = (int) $_POST['event_id'];
    $requeteEvt = bdd()->prepare('SELECT * FROM events WHERE id = ? AND status = "published"');
    $requeteEvt->execute([$idEvenement]);
    $evenement = $requeteEvt->fetch();
    if (!$evenement) rediriger('Index.php');

    $nbPlaces = max(1, min(3, (int) ($_POST['nb_places'] ?? 1)));
    $placesPrises = compter_reservations($idEvenement);
    $statut = $placesPrises + $nbPlaces <= (int) $evenement['capacite'] ? 'confirmed' : 'waitlist';
    if ($statut === 'waitlist' && !(int) $evenement['waitlist_enabled']) {
        message_flash('error', 'Cet evenement est complet.');
        rediriger('evenement-detail.php?id=' . $idEvenement);
    }

    try {
        $code = generer_code_billet();
        $requete = bdd()->prepare('INSERT INTO reservations (event_id, user_id, nb_places, commentaire, ticket_code, status) VALUES (?, ?, ?, ?, ?, ?)');
        $requete->execute([$idEvenement, $utilisateur['id'], $nbPlaces, trim($_POST['commentaire'] ?? ''), $code, $statut]);
        message_flash('success', $statut === 'confirmed' ? 'Reservation confirmee ! Ton billet est disponible.' : 'Evenement complet : tu es place en liste d attente.');
    } catch (PDOException $e) {
        message_flash('error', 'Tu es deja inscrit a cet evenement.');
    }
    rediriger('evenement-detail.php?id=' . $idEvenement);
}

$requete = bdd()->prepare('SELECT e.*, u.prenom, u.nom, u.email FROM events e JOIN users u ON u.id = e.organizer_id WHERE e.id = ?');
$requete->execute([$id]);
$evenement = $requete->fetch();
if (!$evenement) rediriger('Index.php');
$placesPrises = compter_reservations((int) $evenement['id']);
$listeAttente = compter_reservations((int) $evenement['id'], 'waitlist');
$placesRestantes = max(0, (int) $evenement['capacite'] - $placesPrises);
$remplissage = min(100, (int) round($placesPrises * 100 / max(1, (int) $evenement['capacite'])));
$utilisateur = utilisateur_actuel();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= echapper($evenement['titre']) ?> - OmnesEvent</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation(); ?>
<div class="barre-fil-ariane"><div class="conteneur"><nav class="nav-fil-ariane"><a href="Index.php" class="lien-fil-ariane">Accueil</a><span class="separateur-fil-ariane">></span><span class="fil-ariane-actuel"><?= echapper($evenement['titre']) ?></span></nav></div></div>
<div class="bandeau-evenement bandeau-evenement--<?= echapper($evenement['categorie']) ?>" style="<?= $evenement['affiche'] ? 'background-image:url(' . echapper($evenement['affiche']) . ')' : '' ?>"></div>
<section class="section">
  <div class="conteneur">
    <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?> <a href="mes-billets.php">Voir mes billets</a></div><?php endif; ?>
    <?php if ($messageFlash = message_flash('error')): ?><div class="alerte alerte-danger"><?= echapper($messageFlash) ?></div><?php endif; ?>
    <div class="grille-detail-evenement">
      <div>
        <div class="entete-evenement"><div class="entete-evenement-principal"><span class="carte-evenement-categorie category-<?= echapper($evenement['categorie']) ?> mb-2"><?= echapper(libelle_categorie($evenement['categorie'])) ?></span><h1 class="titre-evenement"><?= echapper($evenement['titre']) ?></h1><p class="organisateur-evenement">Organise par <strong><?= echapper($evenement['association']) ?></strong></p></div><span class="pastille <?= $placesRestantes > 0 ? 'pastille-succes' : 'pastille-danger' ?> pastille-statut-evenement"><?= $placesRestantes > 0 ? 'Places disponibles' : 'Complet' ?></span></div>
        <div class="carte mb-3"><div class="carte-entete"><h3>Description</h3></div><div class="carte-corps description-evenement"><p><?= nl2br(echapper($evenement['description'])) ?></p></div></div>
        <div class="carte mb-3"><div class="carte-entete"><h3>Infos pratiques</h3></div><div class="carte-corps"><ul class="liste-infos"><li><div><div class="etiquette-info">Date & Heure</div><div class="valeur-info"><?= echapper(formater_date($evenement['date_debut'])) ?>, <?= echapper(substr($evenement['heure_debut'], 0, 5)) ?><?= $evenement['heure_fin'] ? ' - ' . echapper(substr($evenement['heure_fin'], 0, 5)) : '' ?></div></div></li><li><div><div class="etiquette-info">Lieu</div><div class="valeur-info"><?= echapper($evenement['lieu']) ?><br><span class="detail-adresse"><?= echapper($evenement['adresse']) ?></span></div></div></li><li><div><div class="etiquette-info">Prix</div><div class="valeur-info"><?= (float) $evenement['prix'] > 0 ? echapper(number_format((float) $evenement['prix'], 2, ',', ' ')) . ' EUR' : 'Gratuit' ?></div></div></li></ul></div></div>
      </div>
      <div>
        <div class="carte carte--fixe"><div class="carte-corps">
          <h3 class="titre-reservation">Reserver ma place</h3>
          <div class="section-capacite"><div class="flex espacer mb-1"><span class="texte-petit texte-discret">Places disponibles</span><span class="texte-petit gras"><?= $placesRestantes ?> / <?= (int) $evenement['capacite'] ?></span></div><div class="barre-capacite barre-capacite--moyenne"><div class="remplissage-capacite" style="--fill: <?= $remplissage ?>%;"></div></div><p class="texte-tres-petit texte-discret mt-1"><?= $placesPrises ?> places deja reservees, <?= $listeAttente ?> en attente</p></div>
          <?php if ($utilisateur): ?>
            <form method="post"><input type="hidden" name="action" value="reserve"><input type="hidden" name="event_id" value="<?= (int) $evenement['id'] ?>"><div class="groupe-champ"><label class="etiquette-champ">Nombre de places</label><select class="champ" name="nb_places"><option value="1">1 place</option><option value="2">2 places</option><option value="3">3 places</option></select></div><div class="groupe-champ"><label class="etiquette-champ">Commentaire</label><textarea class="champ" name="commentaire" rows="2"></textarea></div><button class="bouton bouton-primaire bouton-large bouton-grand"><?= $placesRestantes > 0 ? 'Reserver ma place' : 'Rejoindre la liste d attente' ?></button></form>
          <?php else: ?>
            <a class="bouton bouton-primaire bouton-large bouton-grand" href="connexion.php">Se connecter pour reserver</a>
          <?php endif; ?>
        </div></div>
      </div>
    </div>
  </div>
</section>
<?php afficher_pied_de_page(); ?>
</body>
</html>
