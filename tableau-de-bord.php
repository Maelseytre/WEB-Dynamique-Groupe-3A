<?php
require_once __DIR__ . '/includes/bootstrap.php';
$utilisateur = exiger_role(['organisateur', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'publish') {
        $requete = bdd()->prepare('UPDATE events SET status="published" WHERE id=? AND organizer_id=?');
        $requete->execute([(int) $_POST['event_id'], $utilisateur['id']]);
        message_flash('success', 'Evenement publie.');
    }
    if ($action === 'delete') {
        $requete = bdd()->prepare('DELETE FROM events WHERE id=? AND organizer_id=?');
        $requete->execute([(int) $_POST['event_id'], $utilisateur['id']]);
        message_flash('success', 'Evenement supprime.');
    }
    if ($action === 'presence') {
        $requete = bdd()->prepare('UPDATE reservations r JOIN events e ON e.id = r.event_id SET r.presence_validated=1 WHERE r.id=? AND e.organizer_id=?');
        $requete->execute([(int) $_POST['reservation_id'], $utilisateur['id']]);
        message_flash('success', 'Presence validee.');
    }
    rediriger('tableau-de-bord.php');
}

$requete = bdd()->prepare('SELECT e.*, COALESCE(SUM(CASE WHEN r.status="confirmed" THEN r.nb_places ELSE 0 END),0) AS inscrits, COALESCE(SUM(CASE WHEN r.status="waitlist" THEN r.nb_places ELSE 0 END),0) AS attente FROM events e LEFT JOIN reservations r ON r.event_id=e.id WHERE e.organizer_id=? GROUP BY e.id ORDER BY e.date_debut DESC');
$requete->execute([$utilisateur['id']]);
$evenements = $requete->fetchAll();
$requeteParticipants = bdd()->prepare('SELECT r.*, e.titre, u.prenom, u.nom, u.email FROM reservations r JOIN events e ON e.id=r.event_id JOIN users u ON u.id=r.user_id WHERE e.organizer_id=? ORDER BY r.created_at DESC');
$requeteParticipants->execute([$utilisateur['id']]);
$participants = $requeteParticipants->fetchAll();
$actifs = count(array_filter($evenements, fn($e) => $e['status'] === 'published' && $e['date_debut'] >= date('Y-m-d')));
$totalInscrits = array_sum(array_map(fn($e) => (int) $e['inscrits'], $evenements));
$totalAttente = array_sum(array_map(fn($e) => (int) $e['attente'], $evenements));
$passes = count(array_filter($evenements, fn($e) => $e['date_debut'] < date('Y-m-d')));
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Tableau de bord</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('tableau'); ?>
<div class="entete-page"><div class="conteneur"><div class="flex espacer centrer retour-ligne ecart-2"><div><h1>Tableau de bord</h1><p>Bienvenue <?= echapper($utilisateur['prenom']) ?></p></div><a href="creer-evenement.php" class="bouton bouton-primaire">Nouvel evenement</a></div></div></div>
<section class="section"><div class="conteneur">
  <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?></div><?php endif; ?>
  <div class="grille grille-4 mb-4"><div class="carte-stat"><div class="icone-stat icone-stat-primaire"></div><div><div class="valeur-stat"><?= $actifs ?></div><div class="etiquette-stat">Evenements actifs</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-succes"></div><div><div class="valeur-stat"><?= $totalInscrits ?></div><div class="etiquette-stat">Inscrits total</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-avertissement"></div><div><div class="valeur-stat"><?= $totalAttente ?></div><div class="etiquette-stat">Liste d'attente</div></div></div><div class="carte-stat"><div class="icone-stat icone-stat-danger"></div><div><div class="valeur-stat"><?= $passes ?></div><div class="etiquette-stat">Evenements passes</div></div></div></div>
  <div class="onglets mb-3"><button class="bouton-onglet actif" data-tab="mesEvenements">Mes evenements</button><button class="bouton-onglet" data-tab="inscrits">Inscrits</button><button class="bouton-onglet" data-tab="statistiques">Statistiques</button></div>
  <div class="panneau-onglet actif" id="tab-mesEvenements"><div class="conteneur-tableau"><table class="tableau"><thead><tr><th>Evenement</th><th>Date</th><th>Inscrits</th><th>Statut</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($evenements as $evenement): $remplissage = min(100, round((int) $evenement['inscrits'] * 100 / max(1, (int) $evenement['capacite']))); ?>
      <tr><td><div class="flex centrer ecart-2"><div class="icone-evenement icone-evenement--<?= echapper($evenement['categorie']) ?>"></div><div><p class="semi-gras texte-petit"><?= echapper($evenement['titre']) ?></p><p class="texte-tres-petit texte-discret"><?= echapper(libelle_categorie($evenement['categorie'])) ?> - <?= echapper($evenement['association']) ?></p></div></div></td><td class="texte-petit"><?= echapper(formater_date($evenement['date_debut'])) ?></td><td><span class="gras"><?= (int) $evenement['inscrits'] ?></span><span class="texte-discret texte-petit">/<?= (int) $evenement['capacite'] ?></span><div class="barre-capacite barre-capacite--mini mt-1"><div class="remplissage-capacite" style="--fill: <?= $remplissage ?>%;"></div></div></td><td><span class="pastille <?= $evenement['status'] === 'published' ? 'pastille-succes' : 'pastille-avertissement' ?>"><?= echapper($evenement['status']) ?></span></td><td><div class="flex ecart-1"><a class="bouton bouton-contour bouton-petit" href="evenement-detail.php?id=<?= (int) $evenement['id'] ?>">Voir</a><?php if ($evenement['status'] === 'draft'): ?><form method="post"><input type="hidden" name="action" value="publish"><input type="hidden" name="event_id" value="<?= (int) $evenement['id'] ?>"><button class="bouton bouton-primaire bouton-petit">Publier</button></form><?php endif; ?><form method="post" data-confirm="Supprimer cet evenement ?"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int) $evenement['id'] ?>"><button class="bouton bouton-danger bouton-petit">Supprimer</button></form></div></td></tr>
    <?php endforeach; ?>
  </tbody></table></div></div>
  <div class="panneau-onglet" id="tab-inscrits"><div class="carte"><div class="carte-entete"><h3>Liste des inscrits</h3></div><div class="carte-corps p-0"><div class="conteneur-tableau conteneur-tableau--plat"><table class="tableau"><thead><tr><th>Participant</th><th>Evenement</th><th>Date d'inscription</th><th>Statut</th><th>Presence</th></tr></thead><tbody><?php foreach ($participants as $p): ?><tr><td><div class="flex centrer ecart-2"><div class="avatar avatar-petit"><?= echapper(strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1))) ?></div><div><p class="semi-gras texte-petit"><?= echapper($p['prenom'].' '.$p['nom']) ?></p><p class="texte-tres-petit texte-discret"><?= echapper($p['email']) ?></p></div></div></td><td class="texte-petit"><?= echapper($p['titre']) ?></td><td class="texte-petit texte-discret"><?= echapper(substr($p['created_at'],0,10)) ?></td><td><span class="pastille <?= $p['status'] === 'confirmed' ? 'pastille-succes' : 'pastille-avertissement' ?>"><?= echapper($p['status']) ?></span></td><td><?php if ($p['presence_validated']): ?><span class="pastille pastille-succes">Present(e)</span><?php else: ?><form method="post"><input type="hidden" name="action" value="presence"><input type="hidden" name="reservation_id" value="<?= (int) $p['id'] ?>"><button class="bouton bouton-succes bouton-petit">Valider</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
  <div class="panneau-onglet" id="tab-statistiques"><div class="grille grille-2 mb-3"><div class="carte"><div class="carte-entete"><h3>Taux de remplissage</h3></div><div class="carte-corps"><div class="liste-stats-remplissage"><?php foreach ($evenements as $e): $pourcentage = min(100, round((int)$e['inscrits']*100/max(1,(int)$e['capacite']))); ?><div><div class="flex espacer mb-1"><span class="texte-petit"><?= echapper($e['titre']) ?></span><span class="texte-petit gras"><?= $pourcentage ?>%</span></div><div class="barre-capacite barre-capacite--grande"><div class="remplissage-capacite" style="--fill: <?= $pourcentage ?>%;"></div></div></div><?php endforeach; ?></div></div></div></div></div>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>