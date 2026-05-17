<?php
require_once __DIR__ . '/includes/bootstrap.php';

$q = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'date';
$where = ["e.status = 'published'", "e.date_debut >= CURDATE()"];
$params = [];
if ($q !== '') {
    $where[] = '(e.titre LIKE ? OR e.association LIKE ? OR e.lieu LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
}
if ($category !== 'all' && $category !== '') {
    $where[] = 'e.categorie = ?';
    $params[] = $category;
}
$order = $sort === 'name' ? 'e.titre ASC' : 'e.date_debut ASC, e.heure_debut ASC';
$stmt = db()->prepare('SELECT e.*, u.prenom, u.nom FROM events e JOIN users u ON u.id = e.organizer_id WHERE ' . implode(' AND ', $where) . " ORDER BY $order");
$stmt->execute($params);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Accueil</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('home'); ?>
<section class="hero">
  <div class="hero-bg"></div><div class="hero-overlay"></div>
  <div class="container hero-inner">
    <div class="hero-eyebrow">OmnesEvent - Saison 2026</div>
    <h1>Tous les evenements Omnes,<br><em>au meme endroit</em></h1>
    <p>Soirees, competitions sportives, conferences, sorties culturelles... Decouvrez et reservez vos places en quelques clics.</p>
    <form class="search-box" method="get">
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Rechercher un evenement, une association..." autocomplete="off">
      <button class="btn btn-primary">Rechercher</button>
    </form>
  </div>
</section>
<div class="container">
  <div class="filter-pills">
    <?php foreach (['all' => 'Tous', 'soiree' => 'Soiree', 'sport' => 'Sport', 'culture' => 'Culture', 'conference' => 'Conference'] as $key => $label): ?>
      <a class="pill <?= $category === $key ? 'active' : '' ?>" href="Index.php?category=<?= h($key) ?>&q=<?= urlencode($q) ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<section class="section">
  <div class="container">
    <div class="d-flex align-center justify-between mb-3 section-header">
      <div><h2 class="section-title">Evenements a venir</h2><p class="text-muted fs-sm"><?= count($events) ?> evenement(s) trouve(s)</p></div>
      <form method="get"><input type="hidden" name="q" value="<?= h($q) ?>"><input type="hidden" name="category" value="<?= h($category) ?>"><select class="form-control select-compact" name="sort" onchange="this.form.submit()"><option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Trier par date</option><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Trier par nom</option></select></form>
    </div>
    <div class="grid grid-3">
      <?php foreach ($events as $event): $taken = reservation_count((int) $event['id']); $fill = min(100, (int) round($taken * 100 / max(1, (int) $event['capacite']))); ?>
        <a href="evenement-detail.php?id=<?= (int) $event['id'] ?>" class="event-card" data-category="<?= h($event['categorie']) ?>">
          <div class="event-card-image"><img src="<?= h($event['affiche'] ?: 'images/omneseducation_logo.jpeg') ?>" alt="<?= h($event['titre']) ?>"></div>
          <div class="event-card-body">
            <span class="event-card-category category-<?= h($event['categorie']) ?>"><?= h(category_label($event['categorie'])) ?></span>
            <h3 class="event-card-title"><?= h($event['titre']) ?></h3>
            <div class="event-card-meta"><span><?= h(format_date_fr($event['date_debut'])) ?></span><span><?= h($event['lieu']) ?></span><span><?= h($event['association']) ?></span></div>
            <div class="event-card-footer"><div class="capacity-bar"><div class="capacity-fill <?= $taken >= (int) $event['capacite'] ? 'capacity-fill--danger' : '' ?>" style="--fill: <?= $fill ?>%;"></div></div><span class="capacity-text"><?= $taken ?>/<?= (int) $event['capacite'] ?> places</span></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$events): ?><div class="empty-state"><h3 class="empty-title">Aucun evenement trouve</h3><p class="empty-desc">Essaie avec d'autres mots-cles ou modifie les filtres.</p></div><?php endif; ?>
  </div>
</section>
<?php footer_html(); ?>
</body>
</html>
