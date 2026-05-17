<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        redirect($user['role'] === 'admin' ? 'admin.php' : ($user['role'] === 'organisateur' ? 'tableau-de-bord.php' : 'Index.php'));
    }
    $error = "Email, mot de passe ou compte non valide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OmnesEvent - Connexion</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
  <div class="auth-card">
    <div class="auth-logo"><a href="Index.php" class="brand">OmnesEvent</a><p>La plateforme evenementielle Omnes</p></div>
    <h2 class="auth-title">Connexion</h2>
    <p class="auth-subtitle">Content de te revoir ! Entre tes identifiants.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label class="form-label" for="email">Adresse email</label>
        <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Mot de passe</label>
        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg">Se connecter</button>
    </form>
    <div class="divider">ou</div>
    <div class="text-center auth-link-text">Pas encore de compte ? <a href="inscription.php" class="auth-link">S'inscrire</a></div>
    <div class="separator"></div>
    <div class="demo-accounts">
      <p class="fw-semibold mb-1">Comptes de demonstration :</p>
      <p class="text-muted">Participant : participant@demo.fr / demo123</p>
      <p class="text-muted">Organisateur : organisateur@demo.fr / demo123</p>
      <p class="text-muted">Admin : admin@demo.fr / demo123</p>
    </div>
  </div>
</body>
</html>
