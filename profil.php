<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';
    if ($action === 'profile') {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($prenom === '' || $nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informations invalides.';
        } else {
            $stmt = db()->prepare('UPDATE users SET prenom=?, nom=?, email=?, telephone=?, promo=?, campus=?, bio=? WHERE id=?');
            try {
                $stmt->execute([$prenom, $nom, $email, trim($_POST['telephone'] ?? ''), trim($_POST['promo'] ?? ''), trim($_POST['campus'] ?? ''), trim($_POST['bio'] ?? ''), $user['id']]);
                flash('success', 'Profil mis a jour.');
                redirect('profil.php');
            } catch (PDOException $e) {
                $errors[] = 'Cet email est deja utilise.';
            }
        }
    }
    if ($action === 'password') {
        if (!password_verify($_POST['currentPassword'] ?? '', $user['password_hash'])) {
            $errors[] = 'Mot de passe actuel incorrect.';
        } elseif (strlen($_POST['newPassword'] ?? '') < 8 || $_POST['newPassword'] !== ($_POST['confirmNewPassword'] ?? '')) {
            $errors[] = 'Nouveau mot de passe invalide.';
        } else {
            $stmt = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $stmt->execute([password_hash($_POST['newPassword'], PASSWORD_DEFAULT), $user['id']]);
            flash('success', 'Mot de passe mis a jour.');
            redirect('profil.php');
        }
    }
}
$user = current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Mon Profil</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php nav('profile'); ?>
<div class="page-header"><div class="container"><h1>Mon Profil</h1><p>Gerez vos informations personnelles et vos preferences.</p></div></div>
<section class="section"><div class="container container-narrow">
  <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endforeach; ?>
  <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
  <div class="profile-header mb-3"><div class="avatar avatar-xl"><?= h(strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1))) ?></div><div class="profile-info"><h2><?= h($user['prenom'] . ' ' . $user['nom']) ?></h2><p><?= h($user['email']) ?></p><div class="d-flex gap-1 mt-1 flex-wrap"><span class="badge badge-primary"><?= h(ucfirst($user['role'])) ?></span><span class="badge badge-success"><?= h($user['status']) ?></span></div></div></div>
  <div class="tabs"><button class="tab-btn active" data-tab="infos">Informations</button><button class="tab-btn" data-tab="securite">Securite</button><button class="tab-btn" data-tab="compte">Mon compte</button></div>
  <div class="tab-panel active" id="tab-infos"><div class="card"><div class="card-header"><h3>Informations personnelles</h3></div><div class="card-body"><form method="post"><input type="hidden" name="action" value="profile"><div class="form-row form-row-2"><div class="form-group mb-0"><label class="form-label">Prenom</label><input name="prenom" class="form-control" value="<?= h($user['prenom']) ?>" required></div><div class="form-group mb-0"><label class="form-label">Nom</label><input name="nom" class="form-control" value="<?= h($user['nom']) ?>" required></div></div><div class="form-group mt-2"><label class="form-label">Adresse email</label><input type="email" name="email" class="form-control" value="<?= h($user['email']) ?>" required></div><div class="form-row form-row-2"><div class="form-group mb-0"><label class="form-label">Telephone</label><input name="telephone" class="form-control" value="<?= h($user['telephone']) ?>"></div><div class="form-group mb-0"><label class="form-label">Promotion</label><input name="promo" class="form-control" value="<?= h($user['promo']) ?>"></div></div><div class="form-group"><label class="form-label">Campus</label><input name="campus" class="form-control" value="<?= h($user['campus']) ?>"></div><div class="form-group"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"><?= h($user['bio']) ?></textarea></div><button class="btn btn-primary">Enregistrer les modifications</button></form></div></div></div>
  <div class="tab-panel" id="tab-securite"><div class="card"><div class="card-header"><h3>Changer le mot de passe</h3></div><div class="card-body"><form method="post"><input type="hidden" name="action" value="password"><div class="form-group"><label class="form-label">Mot de passe actuel</label><input type="password" name="currentPassword" class="form-control" required></div><div class="form-group"><label class="form-label">Nouveau mot de passe</label><input type="password" name="newPassword" class="form-control" required minlength="8"></div><div class="form-group"><label class="form-label">Confirmer</label><input type="password" name="confirmNewPassword" class="form-control" required minlength="8"></div><button class="btn btn-primary">Mettre a jour</button></form></div></div></div>
  <div class="tab-panel" id="tab-compte"><div class="card"><div class="card-header"><h3>Informations du compte</h3></div><div class="card-body"><div class="account-info"><div class="d-flex justify-between align-center"><span class="text-muted fs-sm">Role</span><span class="badge badge-primary"><?= h($user['role']) ?></span></div><div class="separator mb-0 mt-0"></div><div class="d-flex justify-between align-center"><span class="text-muted fs-sm">Statut</span><span class="badge badge-success"><?= h($user['status']) ?></span></div><div class="separator mb-0 mt-0"></div><div class="d-flex justify-between align-center"><span class="text-muted fs-sm">Membre depuis</span><span class="fw-semibold fs-sm"><?= h(substr($user['created_at'], 0, 10)) ?></span></div></div></div></div></div>
</div></section>
<?php footer_html(); ?>
</body>
</html>
