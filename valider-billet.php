<?php
require_once __DIR__ . '/includes/bootstrap.php';

$utilisateur = exiger_role(['organisateur', 'admin']);
$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$reservation = null;
$messageSucces = null;
$messageErreur = null;
$jourEvenement = false;
$estJourEvenement = static function (array $reservation): bool {
    $aujourdhui = date('Y-m-d');
    $finEvenement = $reservation['date_fin'] ?: $reservation['date_debut'];
    return $reservation['date_debut'] <= $aujourdhui && $finEvenement >= $aujourdhui;
};

if ($code === '') {
    $messageErreur = 'QR code invalide.';
} else {
    $requete = bdd()->prepare('
        SELECT r.*, e.titre, e.date_debut, e.date_fin, e.heure_debut, e.lieu, e.organizer_id, u.prenom, u.nom, u.email
        FROM reservations r
        JOIN events e ON e.id = r.event_id
        JOIN users u ON u.id = r.user_id
        WHERE r.ticket_code = ?
    ');
    $requete->execute([$code]);
    $reservation = $requete->fetch();

    if (!$reservation) {
        $messageErreur = 'Billet introuvable.';
    } elseif ($utilisateur['role'] !== 'admin' && (int) $reservation['organizer_id'] !== (int) $utilisateur['id']) {
        $messageErreur = 'Ce billet ne correspond pas a l un de vos evenements.';
        $reservation = null;
    } else {
        $jourEvenement = $estJourEvenement($reservation);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'validate') {
            if ($reservation['status'] !== 'confirmed') {
                $messageErreur = 'Ce billet n est pas confirme.';
            } elseif (!$jourEvenement) {
                $messageErreur = 'La validation est possible uniquement le jour de l evenement.';
            } else {
                $requeteValidation = bdd()->prepare('UPDATE reservations SET presence_validated = 1 WHERE id = ?');
                $requeteValidation->execute([(int) $reservation['id']]);
                $reservation['presence_validated'] = 1;
                $messageSucces = 'Presence validee.';
            }
        }
    }
}

$peutValider = $reservation
    && $reservation['status'] === 'confirmed'
    && $jourEvenement
    && !(int) $reservation['presence_validated'];
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Validation billet</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('tableau'); ?>
<div class="entete-page"><div class="conteneur"><h1>Validation billet</h1><p>Controle par QR code pour les organisateurs.</p></div></div>
<section class="section"><div class="conteneur conteneur-validation-billet">
  <?php if ($messageSucces): ?><div class="alerte alerte-succes"><?= echapper($messageSucces) ?></div><?php endif; ?>
  <?php if ($messageErreur): ?><div class="alerte alerte-danger"><?= echapper($messageErreur) ?></div><?php endif; ?>
  <?php if ($reservation): ?>
    <div class="carte">
      <div class="carte-entete"><h3><?= echapper($reservation['titre']) ?></h3></div>
      <div class="carte-corps">
        <div class="validation-billet-statut">
          <?php if ((int) $reservation['presence_validated']): ?>
            <span class="pastille pastille-succes">Present(e)</span>
          <?php elseif ($reservation['status'] !== 'confirmed'): ?>
            <span class="pastille pastille-avertissement"><?= echapper($reservation['status']) ?></span>
          <?php elseif (!$jourEvenement): ?>
            <span class="pastille pastille-avertissement">Hors date</span>
          <?php else: ?>
            <span class="pastille pastille-primaire">Billet valide</span>
          <?php endif; ?>
        </div>
        <div class="validation-billet-details">
          <p><strong>Participant :</strong> <?= echapper($reservation['prenom'] . ' ' . $reservation['nom']) ?> - <?= echapper($reservation['email']) ?></p>
          <p><strong>Date :</strong> <?= echapper(formater_date($reservation['date_debut'])) ?> a <?= echapper(substr($reservation['heure_debut'], 0, 5)) ?></p>
          <p><strong>Lieu :</strong> <?= echapper($reservation['lieu']) ?></p>
          <p><strong>Places :</strong> <?= (int) $reservation['nb_places'] ?></p>
          <p><strong>Code billet :</strong> <span class="billet-code-affichage"><?= echapper($reservation['ticket_code']) ?></span></p>
        </div>
        <form method="post" class="mt-3">
          <input type="hidden" name="action" value="validate">
          <input type="hidden" name="code" value="<?= echapper($reservation['ticket_code']) ?>">
          <button class="bouton bouton-succes bouton-grand" <?= $peutValider ? '' : 'disabled' ?>>Valider la presence</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <a href="tableau-de-bord.php" class="bouton bouton-primaire">Retour au tableau de bord</a>
  <?php endif; ?>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>
