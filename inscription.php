<?php
require_once __DIR__ . '/includes/bootstrap.php';

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['password'] ?? '';
    $confirmation = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] === 'organisateur' ? 'organisateur' : 'participant';
    $association = trim($_POST['association'] ?? '');

    if ($prenom === '' || $nom === '') $erreurs[] = 'Le nom et le prenom sont requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'Email invalide.';
    if (strlen($motDePasse) < 8) $erreurs[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
    if ($motDePasse !== $confirmation) $erreurs[] = 'Les mots de passe ne correspondent pas.';
    if ($role === 'organisateur' && $association === '') $erreurs[] = 'Une association est requise pour les organisateurs.';

    if (!$erreurs) {
        try {
            $statut = $role === 'organisateur' ? 'pending' : 'active';
            $requete = bdd()->prepare('INSERT INTO users (prenom, nom, email, password_hash, role, association, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $requete->execute([$prenom, $nom, $email, password_hash($motDePasse, PASSWORD_DEFAULT), $role, $association ?: null, $statut]);
            message_flash('success', $role === 'organisateur' ? 'Compte cree. Un administrateur doit maintenant valider ton role organisateur.' : 'Compte cree avec succes. Tu peux te connecter.');
            rediriger('connexion.php');
        } catch (PDOException $e) {
            $erreurs[] = 'Un compte existe deja avec cet email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Inscription</title><link rel="stylesheet" href="style.css"></head>
<body class="page-auth">
  <div class="carte-auth carte-auth--large">
    <div class="logo-auth"><a href="Index.php" class="marque">OmnesEvent</a><p>La plateforme evenementielle Omnes</p></div>
    <h2 class="titre-auth">Creer un compte</h2>
    <?php foreach ($erreurs as $erreur): ?><div class="alerte alerte-danger"><?= echapper($erreur) ?></div><?php endforeach; ?>
    <form method="post">
      <div class="groupe-champ">
        <label class="etiquette-champ">Je suis...</label>
        <div class="selecteur-role">
          <div class="option-role"><input type="radio" name="role" id="roleParticipant" value="participant" checked><label class="etiquette-role" for="roleParticipant"><span class="nom-role">Participant</span><span class="description-role">Etudiant / Personnel</span></label></div>
          <div class="option-role"><input type="radio" name="role" id="roleOrganisateur" value="organisateur"><label class="etiquette-role" for="roleOrganisateur"><span class="nom-role">Organisateur</span><span class="description-role">Membre d'association</span></label></div>
        </div>
      </div>
      <div class="rangee-champ rangee-champ-2">
        <div class="groupe-champ mb-0"><label class="etiquette-champ" for="prenom">Prenom</label><input type="text" id="prenom" name="prenom" class="champ" required></div>
        <div class="groupe-champ mb-0"><label class="etiquette-champ" for="nom">Nom</label><input type="text" id="nom" name="nom" class="champ" required></div>
      </div>
      <div class="groupe-champ mt-2"><label class="etiquette-champ" for="email">Adresse email</label><input type="email" id="email" name="email" class="champ" required></div>
      <div class="groupe-champ"><label class="etiquette-champ" for="association">Association / BDE</label><select id="association" name="association" class="champ"><option value="">Aucune / participant</option><option>BDE Omnes</option><option>BDS Omnes</option><option>Junior Entreprise</option><option>Club Culturel</option><option>Club Cinema</option><option>Asso Bien-etre</option></select><p class="indication-champ">Les comptes organisateurs sont valides par l'administrateur.</p></div>
      <div class="groupe-champ"><label class="etiquette-champ" for="password">Mot de passe</label><input type="password" id="password" name="password" class="champ" required minlength="8"></div>
      <div class="groupe-champ"><label class="etiquette-champ" for="confirmPassword">Confirmer le mot de passe</label><input type="password" id="confirmPassword" name="confirmPassword" class="champ" required minlength="8"></div>
      <button type="submit" class="bouton bouton-primaire bouton-large bouton-grand mt-2">Creer mon compte</button>
    </form>
    <div class="diviseur">ou</div><div class="texte-centre texte-lien-auth">Deja un compte ? <a href="connexion.php" class="lien-auth">Se connecter</a></div>
  </div>
</body>
</html>