<?php
require_once __DIR__ . '/includes/bootstrap.php';
$utilisateur = exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $requete = bdd()->prepare('UPDATE reservations SET status = "cancelled" WHERE id = ? AND user_id = ?');
    $requete->execute([(int) $_POST['reservation_id'], $utilisateur['id']]);
    message_flash('success', 'Reservation annulee.');
    rediriger('mes-billets.php');
}

$requete = bdd()->prepare('SELECT r.*, e.titre, e.categorie, e.date_debut, e.heure_debut, e.lieu FROM reservations r JOIN events e ON e.id = r.event_id WHERE r.user_id = ? ORDER BY e.date_debut ASC');
$requete->execute([$utilisateur['id']]);
$reservations = $requete->fetchAll();
$aVenir = array_filter($reservations, fn($r) => $r['status'] === 'confirmed' && $r['date_debut'] >= date('Y-m-d'));
$passes = array_filter($reservations, fn($r) => $r['status'] === 'confirmed' && $r['date_debut'] < date('Y-m-d'));
$listeAttente = array_filter($reservations, fn($r) => $r['status'] === 'waitlist');
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Mes Billets</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('billets'); ?>
<div class="entete-page"><div class="conteneur"><h1>Mes Billets</h1><p>Retrouve tous tes evenements a venir et passes.</p></div></div>
<section class="section"><div class="conteneur">
  <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?></div><?php endif; ?>
  <div class="grille grille-3 grille-3-fixe mb-4"><div class="carte-stat"><div class="icone-stat icone-stat-primaire"></div><div><div class="valeur-stat"><?= count($aVenir) ?></div><div class="etiquette-stat">Billets actifs</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-succes"></div><div><div class="valeur-stat"><?= count($passes) ?></div><div class="etiquette-stat">Evenements passes</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-avertissement"></div><div><div class="valeur-stat"><?= count($listeAttente) ?></div><div class="etiquette-stat">Liste d'attente</div></div></div></div>
  <div class="onglets"><button class="bouton-onglet actif" data-tab="upcoming">A venir <span class="pastille pastille-primaire pastille-onglet"><?= count($aVenir) ?></span></button><button class="bouton-onglet" data-tab="past">Passes</button><button class="bouton-onglet" data-tab="waitlist">Liste d'attente</button></div>
  <?php foreach (['upcoming' => $aVenir, 'past' => $passes, 'waitlist' => $listeAttente] as $onglet => $elements): ?>
    <div class="panneau-onglet <?= $onglet === 'upcoming' ? 'actif' : '' ?>" id="tab-<?= echapper($onglet) ?>">
      <div class="liste-billets">
        <?php foreach ($elements as $r): ?>
          <div class="carte-billet <?= $r['status'] === 'waitlist' ? 'carte-billet--avertissement' : '' ?>">
            <div class="billet-corps">
              <div class="flex espacer centrer mb-1 retour-ligne ecart-1"><span class="carte-evenement-categorie category-<?= echapper($r['categorie']) ?>"><?= echapper(libelle_categorie($r['categorie'])) ?></span><span class="pastille <?= $r['status'] === 'waitlist' ? 'pastille-avertissement' : 'pastille-succes' ?>"><?= echapper($r['status'] === 'waitlist' ? 'Liste attente' : 'Confirme') ?></span></div>
              <h3 class="billet-titre"><?= echapper($r['titre']) ?></h3>
              <div class="billet-meta"><span class="texte-petit texte-discret"><?= echapper(formater_date($r['date_debut'])) ?> a <?= echapper(substr($r['heure_debut'], 0, 5)) ?></span><span class="texte-petit texte-discret"><?= echapper($r['lieu']) ?> - <?= (int) $r['nb_places'] ?> place(s)</span></div>
              <?php if ($r['status'] !== 'cancelled' && $onglet !== 'past'): ?><form method="post" data-confirm="Annuler cette reservation ?"><input type="hidden" name="action" value="cancel"><input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>"><button class="bouton bouton-danger bouton-petit">Annuler</button></form><?php endif; ?>
            </div>
            <div class="billet-cote <?= $r['status'] === 'confirmed' ? '' : 'billet-cote--simple' ?>">
              <?php if ($r['status'] === 'confirmed'): ?>
                <img class="billet-qr" src="qr-code.php?code=<?= echapper(rawurlencode($r['ticket_code'])) ?>" alt="QR code du billet <?= echapper($r['ticket_code']) ?>">
              <?php endif; ?>
              <div class="billet-code"><?= echapper($r['ticket_code']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (!$elements): ?><div class="onglet-vide"><p class="gras">Aucun billet</p><p class="texte-discret texte-petit mt-1">Les reservations correspondantes apparaitront ici.</p><a href="Index.php" class="bouton bouton-primaire mt-2">Explorer les evenements</a></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>
