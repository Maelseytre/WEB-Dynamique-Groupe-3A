<?php
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] === 'organisateur' ? 'organisateur' : 'participant';
    $association = trim($_POST['association'] ?? '');

    if ($prenom === '' || $nom === '') $errors[] = 'Le nom et le prenom sont requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';
    if ($role === 'organisateur' && $association === '') $errors[] = 'Une association est requise pour les organisateurs.';

    if (!$errors) {
        try {
            $status = $role === 'organisateur' ? 'pending' : 'active';
            $stmt = db()->prepare('INSERT INTO users (prenom, nom, email, password_hash, role, association, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$prenom, $nom, $email, password_hash($password, PASSWORD_DEFAULT), $role, $association ?: null, $status]);
            flash('success', $role === 'organisateur' ? 'Compte cree. Un administrateur doit maintenant valider ton role organisateur.' : 'Compte cree avec succes. Tu peux te connecter.');
            redirect('connexion.php');
        } catch (PDOException $e) {
            $errors[] = 'Un compte existe deja avec cet email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Inscription</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-page">
  <div class="auth-card auth-card--wide">
    <div class="auth-logo"><a href="Index.php" class="brand">OmnesEvent</a><p>La plateforme evenementielle Omnes</p></div>
    <h2 class="auth-title">Creer un compte</h2>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endforeach; ?>
    <form method="post">
      <div class="form-group">
        <label class="form-label">Je suis...</label>
        <div class="role-selector">
          <div class="role-option"><input type="radio" name="role" id="roleParticipant" value="participant" checked><label class="role-label" for="roleParticipant"><span class="role-name">Participant</span><span class="role-desc">Etudiant / Personnel</span></label></div>
          <div class="role-option"><input type="radio" name="role" id="roleOrganisateur" value="organisateur"><label class="role-label" for="roleOrganisateur"><span class="role-name">Organisateur</span><span class="role-desc">Membre d'association</span></label></div>
        </div>
      </div>
      <div class="form-row form-row-2">
        <div class="form-group mb-0"><label class="form-label" for="prenom">Prenom</label><input type="text" id="prenom" name="prenom" class="form-control" required></div>
        <div class="form-group mb-0"><label class="form-label" for="nom">Nom</label><input type="text" id="nom" name="nom" class="form-control" required></div>
      </div>
      <div class="form-group mt-2"><label class="form-label" for="email">Adresse email</label><input type="email" id="email" name="email" class="form-control" required></div>
      <div class="form-group"><label class="form-label" for="association">Association / BDE</label><select id="association" name="association" class="form-control"><option value="">Aucune / participant</option><option>BDE Omnes</option><option>BDS Omnes</option><option>Junior Entreprise</option><option>Club Culturel</option><option>Club Cinema</option><option>Asso Bien-etre</option></select><p class="form-hint">Les comptes organisateurs sont valides par l'administrateur.</p></div>
      <div class="form-group"><label class="form-label" for="password">Mot de passe</label><input type="password" id="password" name="password" class="form-control" required minlength="8"></div>
      <div class="form-group"><label class="form-label" for="confirmPassword">Confirmer le mot de passe</label><input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required minlength="8"></div>
      <button type="submit" class="btn btn-primary btn-full btn-lg mt-2">Creer mon compte</button>
    </form>
    <div class="divider">ou</div><div class="text-center auth-link-text">Deja un compte ? <a href="connexion.php" class="auth-link">Se connecter</a></div>
  </div>
</body>
</html>
