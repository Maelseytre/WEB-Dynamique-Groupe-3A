<?php
require_once __DIR__ . '/includes/bootstrap.php';
$admin = require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $stmt = db()->prepare('UPDATE users SET status="active" WHERE id=? AND role="organisateur"');
        $stmt->execute([(int) $_POST['user_id']]);
        flash('success', 'Compte organisateur approuve.');
    }
    if ($action === 'reject' || $action === 'delete_user') {
        $stmt = db()->prepare('DELETE FROM users WHERE id=? AND role <> "admin"');
        $stmt->execute([(int) $_POST['user_id']]);
        flash('success', 'Compte supprime.');
    }
    if ($action === 'suspend') {
        $stmt = db()->prepare('UPDATE users SET status=IF(status="suspended","active","suspended") WHERE id=? AND role <> "admin"');
        $stmt->execute([(int) $_POST['user_id']]);
        flash('success', 'Statut utilisateur modifie.');
    }
    if ($action === 'delete_event') {
        $stmt = db()->prepare('DELETE FROM events WHERE id=?');
        $stmt->execute([(int) $_POST['event_id']]);
        flash('success', 'Evenement supprime.');
    }
    redirect('admin.php');
}

$pending = db()->query('SELECT * FROM users WHERE role="organisateur" AND status="pending" ORDER BY created_at DESC')->fetchAll();
$users = db()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$events = db()->query('SELECT e.*, u.prenom, u.nom, COALESCE(SUM(CASE WHEN r.status="confirmed" THEN r.nb_places ELSE 0 END),0) AS inscrits FROM events e JOIN users u ON u.id=e.organizer_id LEFT JOIN reservations r ON r.event_id=e.id GROUP BY e.id ORDER BY e.date_debut DESC')->fetchAll();
$totalUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalEvents = (int) db()->query('SELECT COUNT(*) FROM events WHERE status="published"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Administration</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('admin'); ?>
<div class="admin-ribbon"><p class="fs-sm"><strong>Mode Administrateur</strong> - Omnes Education</p></div>
<div class="page-header"><div class="container"><h1>Panneau d'administration</h1><p>Gestion des utilisateurs, des evenements et de la plateforme.</p></div></div>
<section class="section"><div class="container">
  <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <div class="grid grid-4 mb-4"><div class="stat-card"><div class="stat-icon stat-icon-primary"></div><div><div class="stat-value"><?= $totalUsers ?></div><div class="stat-label">Utilisateurs inscrits</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-success"></div><div><div class="stat-value"><?= $totalEvents ?></div><div class="stat-label">Evenements actifs</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-warning"></div><div><div class="stat-value"><?= count($pending) ?></div><div class="stat-label">Comptes en attente</div></div></div><div class="stat-card"><div class="stat-icon stat-icon-danger"></div><div><div class="stat-value">0</div><div class="stat-label">Signalements</div></div></div></div>
  <div class="tabs mb-3"><button class="tab-btn active" data-tab="comptes">Comptes en attente <span class="badge badge-warning tab-badge"><?= count($pending) ?></span></button><button class="tab-btn" data-tab="utilisateurs">Utilisateurs</button><button class="tab-btn" data-tab="evenements">Evenements</button></div>
  <div class="tab-panel active" id="tab-comptes"><div class="alert alert-info mb-3">Ces comptes organisateurs attendent votre validation.</div><div class="table-container"><table class="table"><thead><tr><th>Utilisateur</th><th>Association</th><th>Date</th><th>Actions</th></tr></thead><tbody><?php foreach ($pending as $u): ?><tr><td><div class="d-flex align-center gap-2"><div class="avatar avatar-sm"><?= h(strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1))) ?></div><div><p class="fw-semibold fs-sm"><?= h($u['prenom'].' '.$u['nom']) ?></p><p class="fs-xs text-muted"><?= h($u['email']) ?></p></div></div></td><td><span class="badge badge-primary"><?= h($u['association']) ?></span></td><td class="fs-sm text-muted"><?= h(substr($u['created_at'],0,10)) ?></td><td><div class="d-flex gap-1"><form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-success btn-sm">Approuver</button></form><form method="post" data-confirm="Refuser ce compte ?"><input type="hidden" name="action" value="reject"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-danger btn-sm">Refuser</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
  <div class="tab-panel" id="tab-utilisateurs"><div class="card"><div class="card-header"><h3>Tous les utilisateurs</h3></div><div class="card-body p-0"><div class="table-container table-container--flat"><table class="table"><thead><tr><th>Utilisateur</th><th>Role</th><th>Inscription</th><th>Statut</th><th>Actions</th></tr></thead><tbody><?php foreach ($users as $u): ?><tr><td><div class="d-flex align-center gap-2"><div class="avatar avatar-sm"><?= h(strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1))) ?></div><div><p class="fw-semibold fs-sm"><?= h($u['prenom'].' '.$u['nom']) ?></p><p class="fs-xs text-muted"><?= h($u['email']) ?></p></div></div></td><td><span class="badge badge-gray"><?= h($u['role']) ?></span></td><td class="fs-sm text-muted"><?= h(substr($u['created_at'],0,10)) ?></td><td><span class="badge <?= $u['status']==='active'?'badge-success':'badge-warning' ?>"><?= h($u['status']) ?></span></td><td><?php if ($u['role'] !== 'admin'): ?><div class="d-flex gap-1"><form method="post"><input type="hidden" name="action" value="suspend"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-outline btn-sm"><?= $u['status']==='suspended'?'Activer':'Suspendre' ?></button></form><form method="post" data-confirm="Supprimer ce compte ?"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="btn btn-danger btn-sm">Supprimer</button></form></div><?php else: ?><span class="text-muted fs-sm">Admin</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
  <div class="tab-panel" id="tab-evenements"><div class="table-container"><table class="table"><thead><tr><th>Evenement</th><th>Organisateur</th><th>Date</th><th>Inscrits</th><th>Statut</th><th>Actions</th></tr></thead><tbody><?php foreach ($events as $e): ?><tr><td><p class="fw-semibold fs-sm"><?= h($e['titre']) ?></p><span class="badge badge-gray fs-xs"><?= h(category_label($e['categorie'])) ?></span></td><td class="fs-sm"><?= h($e['prenom'].' '.$e['nom']) ?></td><td class="fs-sm text-muted"><?= h(format_date_fr($e['date_debut'])) ?></td><td class="fs-sm"><span class="fw-semibold"><?= (int)$e['inscrits'] ?></span>/<?= (int)$e['capacite'] ?></td><td><span class="badge badge-success"><?= h($e['status']) ?></span></td><td><div class="d-flex gap-1"><a class="btn btn-outline btn-sm" href="evenement-detail.php?id=<?= (int)$e['id'] ?>">Voir</a><form method="post" data-confirm="Supprimer cet evenement ?"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="event_id" value="<?= (int)$e['id'] ?>"><button class="btn btn-danger btn-sm">Retirer</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
</div></section>
<?php footer_html(); ?>
</body>
</html>
