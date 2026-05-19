<?php
require_once __DIR__ . '/includes/bootstrap.php';
$administrateur = exiger_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $requete = bdd()->prepare('UPDATE users SET status="active" WHERE id=? AND role="organisateur"');
        $requete->execute([(int) $_POST['user_id']]);
        message_flash('success', 'Compte organisateur approuve.');
    }
    if ($action === 'reject' || $action === 'delete_user') {
        $requete = bdd()->prepare('DELETE FROM users WHERE id=? AND role <> "admin"');
        $requete->execute([(int) $_POST['user_id']]);
        message_flash('success', 'Compte supprime.');
    }
    if ($action === 'suspend') {
        $requete = bdd()->prepare('UPDATE users SET status=IF(status="suspended","active","suspended") WHERE id=? AND role <> "admin"');
        $requete->execute([(int) $_POST['user_id']]);
        message_flash('success', 'Statut utilisateur modifie.');
    }
    if ($action === 'delete_event') {
        $requete = bdd()->prepare('DELETE FROM events WHERE id=?');
        $requete->execute([(int) $_POST['event_id']]);
        message_flash('success', 'Evenement supprime.');
    }
    rediriger('admin.php');
}

$enAttente = bdd()->query('SELECT * FROM users WHERE role="organisateur" AND status="pending" ORDER BY created_at DESC')->fetchAll();
$utilisateurs = bdd()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$evenements = bdd()->query('SELECT e.*, u.prenom, u.nom, COALESCE(SUM(CASE WHEN r.status="confirmed" THEN r.nb_places ELSE 0 END),0) AS inscrits FROM events e JOIN users u ON u.id=e.organizer_id LEFT JOIN reservations r ON r.event_id=e.id GROUP BY e.id ORDER BY e.date_debut DESC')->fetchAll();
$totalUtilisateurs = (int) bdd()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalEvenements = (int) bdd()->query('SELECT COUNT(*) FROM events WHERE status="published"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Administration</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('admin'); ?>
<div class="bandeau-admin"><p class="texte-petit"><strong>Mode Administrateur</strong> - Omnes Education</p></div>
<div class="entete-page"><div class="conteneur"><h1>Panneau d'administration</h1><p>Gestion des utilisateurs, des evenements et de la plateforme.</p></div></div>
<section class="section"><div class="conteneur">
  <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?></div><?php endif; ?>
  <div class="grille grille-4 mb-4"><div class="carte-stat"><div class="icone-stat icone-stat-primaire"></div><div><div class="valeur-stat"><?= $totalUtilisateurs ?></div><div class="etiquette-stat">Utilisateurs inscrits</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-succes"></div><div><div class="valeur-stat"><?= $totalEvenements ?></div><div class="etiquette-stat">Evenements actifs</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-avertissement"></div><div><div class="valeur-stat"><?= count($enAttente) ?></div><div class="etiquette-stat">Comptes en attente</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-danger"></div><div><div class="valeur-stat">0</div><div class="etiquette-stat">Signalements</div></div></div></div>
  <div class="onglets mb-3"><button class="bouton-onglet actif" data-tab="comptes">Comptes en attente <span class="pastille pastille-avertissement pastille-onglet"><?= count($enAttente) ?></span></button><button class="bouton-onglet" data-tab="utilisateurs">Utilisateurs</button><button class="bouton-onglet" data-tab="evenements">Evenements</button></div>
  <div class="panneau-onglet actif" id="tab-comptes"><div class="alerte alerte-info mb-3">Ces comptes organisateurs attendent votre validation.</div><div class="conteneur-tableau"><table class="tableau"><thead><tr><th>Utilisateur</th><th>Association</th><th>Date</th><th>Actions</th></tr></thead><tbody><?php foreach ($enAttente as $u): ?><tr><td><div class="flex centrer ecart-2"><div class="avatar avatar-petit"><?= echapper(strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1))) ?></div><div><p class="semi-gras texte-petit"><?= echapper($u['prenom'].' '.$u['nom']) ?></p><p class="texte-tres-petit texte-discret"><?= echapper($u['email']) ?></p></div></div></td><td><span class="pastille pastille-primaire"><?= echapper($u['association']) ?></span></td><td class="texte-petit texte-discret"><?= echapper(substr($u['created_at'],0,10)) ?></td><td><div class="flex ecart-1"><form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="bouton bouton-succes bouton-petit">Approuver</button></form><form method="post" data-confirm="Refuser ce compte ?"><input type="hidden" name="action" value="reject"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="bouton bouton-danger bouton-petit">Refuser</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
  <div class="panneau-onglet" id="tab-utilisateurs"><div class="carte"><div class="carte-entete"><h3>Tous les utilisateurs</h3></div><div class="carte-corps p-0"><div class="conteneur-tableau conteneur-tableau--plat"><table class="tableau"><thead><tr><th>Utilisateur</th><th>Role</th><th>Inscription</th><th>Statut</th><th>Actions</th></tr></thead><tbody><?php foreach ($utilisateurs as $u): ?><tr><td><div class="flex centrer ecart-2"><div class="avatar avatar-petit"><?= echapper(strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1))) ?></div><div><p class="semi-gras texte-petit"><?= echapper($u['prenom'].' '.$u['nom']) ?></p><p class="texte-tres-petit texte-discret"><?= echapper($u['email']) ?></p></div></div></td><td><span class="pastille pastille-grise"><?= echapper($u['role']) ?></span></td><td class="texte-petit texte-discret"><?= echapper(substr($u['created_at'],0,10)) ?></td><td><span class="pastille <?= $u['status']==='active'?'pastille-succes':'pastille-avertissement' ?>"><?= echapper($u['status']) ?></span></td><td><?php if ($u['role'] !== 'admin'): ?><div class="flex ecart-1"><form method="post"><input type="hidden" name="action" value="suspend"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="bouton bouton-contour bouton-petit"><?= $u['status']==='suspended'?'Activer':'Suspendre' ?></button></form><form method="post" data-confirm="Supprimer ce compte ?"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><button class="bouton bouton-danger bouton-petit">Supprimer</button></form></div><?php else: ?><span class="texte-discret texte-petit">Admin</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
  <div class="panneau-onglet" id="tab-evenements"><div class="conteneur-tableau"><table class="tableau"><thead><tr><th>Evenement</th><th>Organisateur</th><th>Date</th><th>Inscrits</th><th>Statut</th><th>Actions</th></tr></thead><tbody><?php foreach ($evenements as $e): ?><tr><td><p class="semi-gras texte-petit"><?= echapper($e['titre']) ?></p><span class="pastille pastille-grise texte-tres-petit"><?= echapper(libelle_categorie($e['categorie'])) ?></span></td><td class="texte-petit"><?= echapper($e['prenom'].' '.$e['nom']) ?></td><td class="texte-petit texte-discret"><?= echapper(formater_date($e['date_debut'])) ?></td><td class="texte-petit"><span class="gras"><?= (int)$e['inscrits'] ?></span>/<?= (int)$e['capacite'] ?></td><td><span class="pastille pastille-succes"><?= echapper($e['status']) ?></span></td><td><div class="flex ecart-1"><a class="bouton bouton-contour bouton-petit" href="evenement-detail.php?id=<?= (int)$e['id'] ?>">Voir</a><form method="post" data-confirm="Supprimer cet evenement ?"><input type="hidden" name="action" value="delete_event"><input type="hidden" name="event_id" value="<?= (int)$e['id'] ?>"><button class="bouton bouton-danger bouton-petit">Retirer</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></div>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>