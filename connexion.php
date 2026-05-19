<?php
require_once __DIR__ . '/includes/bootstrap.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['password'] ?? '';
    $requete = bdd()->prepare('SELECT * FROM users WHERE email = ?');
    $requete->execute([$email]);
    $utilisateur = $requete->fetch();

    if ($utilisateur && $utilisateur['status'] === 'active' && password_verify($motDePasse, $utilisateur['password_hash'])) {
        $_SESSION['user_id'] = (int) $utilisateur['id'];
        rediriger($utilisateur['role'] === 'admin' ? 'admin.php' : ($utilisateur['role'] === 'organisateur' ? 'tableau-de-bord.php' : 'Index.php'));
    }
    $erreur = "Email, mot de passe ou compte non valide.";
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
<body class="page-auth">
  <div class="carte-auth">
    <div class="logo-auth"><a href="Index.php" class="marque">OmnesEvent</a><p>La plateforme evenementielle Omnes</p></div>
    <h2 class="titre-auth">Connexion</h2>
    <p class="sous-titre-auth">Content de te revoir ! Entre tes identifiants.</p>
    <?php if ($erreur): ?><div class="alerte alerte-danger"><?= echapper($erreur) ?></div><?php endif; ?>
    <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?></div><?php endif; ?>
    <form method="post">
      <div class="groupe-champ">
        <label class="etiquette-champ" for="email">Adresse email</label>
        <input type="email" id="email" name="email" class="champ" required autocomplete="email">
      </div>
      <div class="groupe-champ">
        <label class="etiquette-champ" for="password">Mot de passe</label>
        <input type="password" id="password" name="password" class="champ" required autocomplete="current-password">
      </div>
      <button type="submit" class="bouton bouton-primaire bouton-large bouton-grand">Se connecter</button>
    </form>
    <div class="diviseur">ou</div>
    <div class="texte-centre texte-lien-auth">Pas encore de compte ? <a href="inscription.php" class="lien-auth">S'inscrire</a></div>
    <div class="separateur"></div>
    <div class="comptes-demo">
      <p class="semi-gras mb-1">Comptes de demonstration :</p>
      <p class="texte-discret">Participant : participant@demo.fr / demo123</p>
      <p class="texte-discret">Organisateur : organisateur@demo.fr / demo123</p>
      <p class="texte-discret">Admin : admin@demo.fr / demo123</p>
    </div>
  </div>
</body>
</html>