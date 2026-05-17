<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_role(['organisateur', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'publish') {
        $stmt = db()->prepare('UPDATE events SET status="published" WHERE id=? AND organizer_id=?');
        $stmt->execute([(int) $_POST['event_id'], $user['id']]);
        flash('success', 'Evenement publie.');
    }
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM events WHERE id=? AND organizer_id=?');
        $stmt->execute([(int) $_POST['event_id'], $user['id']]);
        flash('success', 'Evenement supprime.');
    }
    if ($action === 'presence') {
        $stmt = db()->prepare('UPDATE reservations r JOIN events e ON e.id = r.event_id SET r.presence_validated=1 WHERE r.id=? AND e.organizer_id=?');
        $stmt->execute([(int) $_POST['reservation_id'], $user['id']]);
        flash('success', 'Presence validee.');
    }
    redirect('tableau-de-bord.php');
}

$stmt = db()->prepare('SELECT e.*, COALESCE(SUM(CASE WHEN r.status="confirmed" THEN r.nb_places ELSE 0 END),0) AS inscrits, COALESCE(SUM(CASE WHEN r.status="waitlist" THEN r.nb_places ELSE 0 END),0) AS attente FROM events e LEFT JOIN reservations r ON r.event_id=e.id WHERE e.organizer_id=? GROUP BY e.id ORDER BY e.date_debut DESC');
$stmt->execute([$user['id']]);
$events = $stmt->fetchAll();
$participantsStmt = db()->prepare('SELECT r.*, e.titre, u.prenom, u.nom, u.email FROM reservations r JOIN events e ON e.id=r.event_id JOIN users u ON u.id=r.user_id WHERE e.organizer_id=? ORDER BY r.created_at DESC');
$participantsStmt->execute([$user['id']]);
$participants = $participantsStmt->fetchAll();
$active = count(array_filter($events, fn($e) => $e['status'] === 'published' && $e['date_debut'] >= date('Y-m-d')));
$totalInscrits = array_sum(array_map(fn($e) => (int) $e['inscrits'], $events));
$totalAttente = array_sum(array_map(fn($e) => (int) $e['attente'], $events));
$past = count(array_filter($events, fn($e) => $e['date_debut'] < date('Y-m-d')));
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('dashboard'); ?>
<div class="page-header"><div class="container"><div class="d-flex justify-between align-center flex-wrap gap-2"><div><h1>Tableau de bord</h1><p>Bienvenue <?= h($user['prenom']) ?></p></div><a href="creer-evenement.php" class="btn btn-primary">Nouvel evenement</a></div></div></div>
<section class="section"><div class="container">
  <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <div class="grid grid-4 mb-4"><div class="stat-card"><div class="stat-icon stat-icon-primary"></div><div><div class="stat-value"><?= $active ?></div><div class="stat-label">Evenements actifs</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-success"></div><div><div class="stat-value"><?= $totalInscrits ?></div><div class="stat-label">Inscrits total</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-warning"></div><div><div class="stat-value"><?= $totalAttente ?></div><div class="stat-label">Liste d'attente</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-danger"></div><div><div class="stat-value"><?= $past ?></div><div class="stat-label">Evenements passes</div></div></div></div>
  <div class="tabs mb-3"><button class="tab-btn active" data-tab="mesEvenements">Mes evenements</button><button class="tab-btn" data-tab="inscrits">Inscrits</button><button class="tab-btn" data-tab="statistiques">Statistiques</button></div>
  <div class="tab-panel active" id="tab-mesEvenements"><div class="table-container"><table class="table"><thead><tr><th>Evenement</th><th>Date</th><th>Inscrits</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($events as $event): $fill = min(100, round((int) $event['inscrits'] * 100 / max(1, (int) $event['capacite']))); ?>
      <tr><td><div class="d-flex align-center gap-2"><div class="event-icon event-icon--<?= h($event['categorie']) ?>"></div><div><p class="fw-semibold fs-sm"><?= h($event['titre']) ?></p><p class="fs-xs text-muted"><?= h(category_label($event['categorie'])) ?> - <?= h($event['association']) ?></p></div></div></td><td class="fs-sm"><?= h(format_date_fr($event['date_debut'])) ?></td><td><span class="fw-semibold"><?= (int) $event['inscrits'] ?></span><span class="text-muted fs-sm">/<?= (int) $event['capacite'] ?></span><div class="capacity-bar capacity-bar--mini mt-1"><div class="capacity-fill" style="--fill: <?= $fill ?>%;"></div></div></td><td><span class="badge <?= $event['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>"><?= h($event['status']) ?></span></td><td><div class="d-flex gap-1"><a class="btn btn-outline btn-sm" href="evenement-detail.php?id=<?= (int) $event['id'] ?>">Voir</a><?php if ($event['status'] === 'draft'): ?><form method="post"><input type="hidden" name="action" value="publish"><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><button class="btn btn-primary btn-sm">Publier</button></form><?php endif; ?><form method="post" data-confirm="Supprimer cet evenement ?"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><button class="btn btn-danger btn-sm">Supprimer</button></form></div></td></tr>
    <?php endforeach; ?>
  </tbody></table></div></div>
  <div class="tab-panel" id="tab-inscrits"><div class="card"><div class="card-header"><h3>Liste des inscrits</h3></div><div class="card-body p-0"><div class="table-container table-container--flat"><table class="table"><thead><tr><th>Participant</th><th>Evenement</th><th>Date d'inscription</th><th>Statut</th><th>Presence</th></tr></thead><tbody><?php foreach ($participants as $p): ?><tr><td><div class="d-flex align-center gap-2"><div class="avatar avatar-sm"><?= h(strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1))) ?></div><div><p class="fw-semibold fs-sm"><?= h($p['prenom'].' '.$p['nom']) ?></p><p class="fs-xs text-muted"><?= h($p['email']) ?></p></div></div></td><td class="fs-sm"><?= h($p['titre']) ?></td><td class="fs-sm text-muted"><?= h(substr($p['created_at'],0,10)) ?></td><td><span class="badge <?= $p['status'] === 'confirmed' ? 'badge-success' : 'badge-warning' ?>"><?= h($p['status']) ?></span></td><td><?php if ($p['presence_validated']): ?><span class="badge badge-success">Present(e)</span><?php else: ?><form method="post"><input type="hidden" name="action" value="presence"><input type="hidden" name="reservation_id" value="<?= (int) $p['id'] ?>"><button class="btn btn-success btn-sm">Valider</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
  <div class="tab-panel" id="tab-statistiques"><div class="grid grid-2 mb-3"><div class="card"><div class="card-header"><h3>Taux de remplissage</h3></div><div class="card-body"><div class="stats-fill-list"><?php foreach ($events as $e): $pct = min(100, round((int)$e['inscrits']*100/max(1,(int)$e['capacite']))); ?><div><div class="d-flex justify-between mb-1"><span class="fs-sm"><?= h($e['titre']) ?></span><span class="fs-sm fw-bold"><?= $pct ?>%</span></div><div class="capacity-bar capacity-bar--lg"><div class="capacity-fill" style="--fill: <?= $pct ?>%;"></div></div></div><?php endforeach; ?></div></div></div></div></div>
</div></section>
<?php footer_html(); ?>
</body>
</html>
