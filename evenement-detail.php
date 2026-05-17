<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? $_POST['event_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reserve') {
    $user = require_login();
    $eventId = (int) $_POST['event_id'];
    $eventStmt = db()->prepare('SELECT * FROM events WHERE id = ? AND status = "published"');
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch();
    if (!$event) redirect('Index.php');

    $nbPlaces = max(1, min(3, (int) ($_POST['nb_places'] ?? 1)));
    $taken = reservation_count($eventId);
    $status = $taken + $nbPlaces <= (int) $event['capacite'] ? 'confirmed' : 'waitlist';
    if ($status === 'waitlist' && !(int) $event['waitlist_enabled']) {
        flash('error', 'Cet evenement est complet.');
        redirect('evenement-detail.php?id=' . $eventId);
    }

    try {
        $code = 'OE-' . date('Y') . '-' . random_int(1000, 9999);
        $stmt = db()->prepare('INSERT INTO reservations (event_id, user_id, nb_places, commentaire, ticket_code, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$eventId, $user['id'], $nbPlaces, trim($_POST['commentaire'] ?? ''), $code, $status]);
        flash('success', $status === 'confirmed' ? 'Reservation confirmee ! Ton billet est disponible.' : 'Evenement complet : tu es place en liste d attente.');
    } catch (PDOException $e) {
        flash('error', 'Tu es deja inscrit a cet evenement.');
    }
    redirect('evenement-detail.php?id=' . $eventId);
}

$stmt = db()->prepare('SELECT e.*, u.prenom, u.nom, u.email FROM events e JOIN users u ON u.id = e.organizer_id WHERE e.id = ?');
$stmt->execute([$id]);
$event = $stmt->fetch();
if (!$event) redirect('Index.php');
$taken = reservation_count((int) $event['id']);
$waitlist = reservation_count((int) $event['id'], 'waitlist');
$remaining = max(0, (int) $event['capacite'] - $taken);
$fill = min(100, (int) round($taken * 100 / max(1, (int) $event['capacite'])));
$user = current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= h($event['titre']) ?> - OmnesEvent</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav(); ?>
<div class="breadcrumb-bar"><div class="container"><nav class="breadcrumb-nav"><a href="Index.php" class="breadcrumb-link">Accueil</a><span class="breadcrumb-separator">></span><span class="breadcrumb-current"><?= h($event['titre']) ?></span></nav></div></div>
<div class="event-banner event-banner--<?= h($event['categorie']) ?>" style="<?= $event['affiche'] ? 'background-image:url(' . h($event['affiche']) . ')' : '' ?>"></div>
<section class="section">
  <div class="container">
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?> <a href="mes-billets.php">Voir mes billets</a></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= h($msg) ?></div><?php endif; ?>
    <div class="event-detail-grid">
      <div>
        <div class="event-header"><div class="event-header-main"><span class="event-card-category category-<?= h($event['categorie']) ?> mb-2"><?= h(category_label($event['categorie'])) ?></span><h1 class="event-title"><?= h($event['titre']) ?></h1><p class="event-organizer">Organise par <strong><?= h($event['association']) ?></strong></p></div><span class="badge <?= $remaining > 0 ? 'badge-success' : 'badge-danger' ?> event-status-badge"><?= $remaining > 0 ? 'Places disponibles' : 'Complet' ?></span></div>
        <div class="card mb-3"><div class="card-header"><h3>Description</h3></div><div class="card-body event-description"><p><?= nl2br(h($event['description'])) ?></p></div></div>
        <div class="card mb-3"><div class="card-header"><h3>Infos pratiques</h3></div><div class="card-body"><ul class="info-list"><li><div><div class="info-label">Date & Heure</div><div class="info-value"><?= h(format_date_fr($event['date_debut'])) ?>, <?= h(substr($event['heure_debut'], 0, 5)) ?><?= $event['heure_fin'] ? ' - ' . h(substr($event['heure_fin'], 0, 5)) : '' ?></div></div></li><li><div><div class="info-label">Lieu</div><div class="info-value"><?= h($event['lieu']) ?><br><span class="address-detail"><?= h($event['adresse']) ?></span></div></div></li><li><div><div class="info-label">Prix</div><div class="info-value"><?= (float) $event['prix'] > 0 ? h(number_format((float) $event['prix'], 2, ',', ' ')) . ' EUR' : 'Gratuit' ?></div></div></li></ul></div></div>
      </div>
      <div>
        <div class="card card--sticky"><div class="card-body">
          <h3 class="reserve-title">Reserver ma place</h3>
          <div class="capacity-section"><div class="d-flex justify-between mb-1"><span class="fs-sm text-muted">Places disponibles</span><span class="fs-sm fw-bold"><?= $remaining ?> / <?= (int) $event['capacite'] ?></span></div><div class="capacity-bar capacity-bar--md"><div class="capacity-fill" style="--fill: <?= $fill ?>%;"></div></div><p class="fs-xs text-muted mt-1"><?= $taken ?> places deja reservees, <?= $waitlist ?> en attente</p></div>
          <?php if ($user): ?>
            <form method="post"><input type="hidden" name="action" value="reserve"><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><div class="form-group"><label class="form-label">Nombre de places</label><select class="form-control" name="nb_places"><option value="1">1 place</option><option value="2">2 places</option><option value="3">3 places</option></select></div><div class="form-group"><label class="form-label">Commentaire</label><textarea class="form-control" name="commentaire" rows="2"></textarea></div><button class="btn btn-primary btn-full btn-lg"><?= $remaining > 0 ? 'Reserver ma place' : 'Rejoindre la liste d attente' ?></button></form>
          <?php else: ?>
            <a class="btn btn-primary btn-full btn-lg" href="connexion.php">Se connecter pour reserver</a>
          <?php endif; ?>
        </div></div>
      </div>
    </div>
  </div>
</section>
<?php footer_html(); ?>
</body>
</html>
