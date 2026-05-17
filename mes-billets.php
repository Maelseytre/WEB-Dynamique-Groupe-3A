<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $stmt = db()->prepare('UPDATE reservations SET status = "cancelled" WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $_POST['reservation_id'], $user['id']]);
    flash('success', 'Reservation annulee.');
    redirect('mes-billets.php');
}

$stmt = db()->prepare('SELECT r.*, e.titre, e.categorie, e.date_debut, e.heure_debut, e.lieu FROM reservations r JOIN events e ON e.id = r.event_id WHERE r.user_id = ? ORDER BY e.date_debut ASC');
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();
$upcoming = array_filter($reservations, fn($r) => $r['status'] === 'confirmed' && $r['date_debut'] >= date('Y-m-d'));
$past = array_filter($reservations, fn($r) => $r['status'] === 'confirmed' && $r['date_debut'] < date('Y-m-d'));
$waitlist = array_filter($reservations, fn($r) => $r['status'] === 'waitlist');
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Mes Billets</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('tickets'); ?>
<div class="page-header"><div class="container"><h1>Mes Billets</h1><p>Retrouve tous tes evenements a venir et passes.</p></div></div>
<section class="section"><div class="container">
  <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <div class="grid grid-3 grid-3-fixed mb-4"><div class="stat-card"><div class="stat-icon stat-icon-primary"></div><div><div class="stat-value"><?= count($upcoming) ?></div><div class="stat-label">Billets actifs</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-success"></div><div><div class="stat-value"><?= count($past) ?></div><div class="stat-label">Evenements passes</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-warning"></div><div><div class="stat-value"><?= count($waitlist) ?></div><div class="stat-label">Liste d'attente</div></div></div></div>
  <div class="tabs"><button class="tab-btn active" data-tab="upcoming">A venir <span class="badge badge-primary tab-badge"><?= count($upcoming) ?></span></button><button class="tab-btn" data-tab="past">Passes</button><button class="tab-btn" data-tab="waitlist">Liste d'attente</button></div>
  <?php foreach (['upcoming' => $upcoming, 'past' => $past, 'waitlist' => $waitlist] as $tab => $items): ?>
    <div class="tab-panel <?= $tab === 'upcoming' ? 'active' : '' ?>" id="tab-<?= h($tab) ?>">
      <div class="tickets-list">
        <?php foreach ($items as $r): ?>
          <div class="ticket-card <?= $r['status'] === 'waitlist' ? 'ticket-card--warning' : '' ?>"><div class="ticket-body"><div class="d-flex justify-between align-center mb-1 flex-wrap gap-1"><span class="event-card-category category-<?= h($r['categorie']) ?>"><?= h(category_label($r['categorie'])) ?></span><span class="badge <?= $r['status'] === 'waitlist' ? 'badge-warning' : 'badge-success' ?>"><?= h($r['status'] === 'waitlist' ? 'Liste attente' : 'Confirme') ?></span></div><h3 class="ticket-title"><?= h($r['titre']) ?></h3><div class="ticket-meta"><span class="fs-sm text-muted"><?= h(format_date_fr($r['date_debut'])) ?> a <?= h(substr($r['heure_debut'], 0, 5)) ?></span><span class="fs-sm text-muted"><?= h($r['lieu']) ?> - <?= (int) $r['nb_places'] ?> place(s)</span></div><?php if ($r['status'] !== 'cancelled' && $tab !== 'past'): ?><form method="post" data-confirm="Annuler cette reservation ?"><input type="hidden" name="action" value="cancel"><input type="hidden" name="reservation_id" value="<?= (int) $r['id'] ?>"><button class="btn btn-danger btn-sm">Annuler</button></form><?php endif; ?></div><div class="ticket-side"><div class="ticket-code"><?= h($r['ticket_code']) ?></div></div></div>
        <?php endforeach; ?>
      </div>
      <?php if (!$items): ?><div class="tab-empty"><p class="fw-bold">Aucun billet</p><p class="text-muted fs-sm mt-1">Les reservations correspondantes apparaitront ici.</p><a href="Index.php" class="btn btn-primary mt-2">Explorer les evenements</a></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div></section>
<?php footer_html(); ?>
</body>
</html>
