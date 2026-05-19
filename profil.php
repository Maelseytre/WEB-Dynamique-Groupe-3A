<?php
require_once __DIR__ . '/includes/bootstrap.php';
$utilisateur = exiger_connexion();
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';
    if ($action === 'profile') {
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($prenom === '' || $nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'Informations invalides.';
        } else {
            $requete = bdd()->prepare('UPDATE users SET prenom=?, nom=?, email=?, telephone=?, promo=?, campus=?, bio=? WHERE id=?');
            try {
                $requete->execute([$prenom, $nom, $email, trim($_POST['telephone'] ?? ''), trim($_POST['promo'] ?? ''), trim($_POST['campus'] ?? ''), trim($_POST['bio'] ?? ''), $utilisateur['id']]);
                message_flash('success', 'Profil mis a jour.');
                rediriger('profil.php');
            } catch (PDOException $e) {
                $erreurs[] = 'Cet email est deja utilise.';
            }
        }
    }
    if ($action === 'password') {
        if (!password_verify($_POST['currentPassword'] ?? '', $utilisateur['password_hash'])) {
            $erreurs[] = 'Mot de passe actuel incorrect.';
        } elseif (strlen($_POST['newPassword'] ?? '') < 8 || $_POST['newPassword'] !== ($_POST['confirmNewPassword'] ?? '')) {
            $erreurs[] = 'Nouveau mot de passe invalide.';
        } else {
            $requete = bdd()->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $requete->execute([password_hash($_POST['newPassword'], PASSWORD_DEFAULT), $utilisateur['id']]);
            message_flash('success', 'Mot de passe mis a jour.');
            rediriger('profil.php');
        }
    }
}
$utilisateur = utilisateur_actuel();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>OmnesEvent - Mon Profil</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php afficher_navigation('profil'); ?>
<div class="entete-page"><div class="conteneur"><h1>Mon Profil</h1><p>Gerez vos informations personnelles et vos preferences.</p></div></div>
<section class="section"><div class="conteneur conteneur-etroit">
  <?php foreach ($erreurs as $erreur): ?><div class="alerte alerte-danger"><?= echapper($erreur) ?></div><?php endforeach; ?>
  <?php if ($messageFlash = message_flash('success')): ?><div class="alerte alerte-succes"><?= echapper($messageFlash) ?></div><?php endif; ?>
  <div class="entete-profil mb-3"><div class="avatar avatar-tres-grand"><?= echapper(strtoupper(substr($utilisateur['prenom'], 0, 1) . substr($utilisateur['nom'], 0, 1))) ?></div><div class="info-profil"><h2><?= echapper($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></h2><p><?= echapper($utilisateur['email']) ?></p><div class="flex ecart-1 mt-1 retour-ligne"><span class="pastille pastille-primaire"><?= echapper(ucfirst($utilisateur['role'])) ?></span><span class="pastille pastille-succes"><?= echapper($utilisateur['status']) ?></span></div></div></div>
  <div class="onglets"><button class="bouton-onglet actif" data-tab="infos">Informations</button><button class="bouton-onglet" data-tab="securite">Securite</button><button class="bouton-onglet" data-tab="compte">Mon compte</button></div>
  <div class="panneau-onglet actif" id="tab-infos"><div class="carte"><div class="carte-entete"><h3>Informations personnelles</h3></div><div class="carte-corps"><form method="post"><input type="hidden" name="action" value="profile"><div class="rangee-champ rangee-champ-2"><div class="groupe-champ mb-0"><label class="etiquette-champ">Prenom</label><input name="prenom" class="champ" value="<?= echapper($utilisateur['prenom']) ?>" required></div><div class="groupe-champ mb-0"><label class="etiquette-champ">Nom</label><input name="nom" class="champ" value="<?= echapper($utilisateur['nom']) ?>" required></div></div><div class="groupe-champ mt-2"><label class="etiquette-champ">Adresse email</label><input type="email" name="email" class="champ" value="<?= echapper($utilisateur['email']) ?>" required></div><div class="rangee-champ rangee-champ-2"><div class="groupe-champ mb-0"><label class="etiquette-champ">Telephone</label><input name="telephone" class="champ" value="<?= echapper($utilisateur['telephone']) ?>"></div><div class="groupe-champ mb-0"><label class="etiquette-champ">Promotion</label><input name="promo" class="champ" value="<?= echapper($utilisateur['promo']) ?>"></div></div><div class="groupe-champ"><label class="etiquette-champ">Campus</label><input name="campus" class="champ" value="<?= echapper($utilisateur['campus']) ?>"></div><div class="groupe-champ"><label class="etiquette-champ">Bio</label><textarea name="bio" class="champ" rows="3"><?= echapper($utilisateur['bio']) ?></textarea></div><button class="bouton bouton-primaire">Enregistrer les modifications</button></form></div></div></div>
  <div class="panneau-onglet" id="tab-securite"><div class="carte"><div class="carte-entete"><h3>Changer le mot de passe</h3></div><div class="carte-corps"><form method="post"><input type="hidden" name="action" value="password"><div class="groupe-champ"><label class="etiquette-champ">Mot de passe actuel</label><input type="password" name="currentPassword" class="champ" required></div><div class="groupe-champ"><label class="etiquette-champ">Nouveau mot de passe</label><input type="password" name="newPassword" class="champ" required minlength="8"></div><div class="groupe-champ"><label class="etiquette-champ">Confirmer</label><input type="password" name="confirmNewPassword" class="champ" required minlength="8"></div><button class="bouton bouton-primaire">Mettre a jour</button></form></div></div></div>
  <div class="panneau-onglet" id="tab-compte"><div class="carte"><div class="carte-entete"><h3>Informations du compte</h3></div><div class="carte-corps"><div class="info-compte"><div class="flex espacer centrer"><span class="texte-discret texte-petit">Role</span><span class="pastille pastille-primaire"><?= echapper($utilisateur['role']) ?></span></div><div class="separateur mb-0 mt-0"></div><div class="flex espacer centrer"><span class="texte-discret texte-petit">Statut</span><span class="pastille pastille-succes"><?= echapper($utilisateur['status']) ?></span></div><div class="separateur mb-0 mt-0"></div><div class="flex espacer centrer"><span class="texte-discret texte-petit">Membre depuis</span><span class="semi-gras texte-petit"><?= echapper(substr($utilisateur['created_at'], 0, 10)) ?></span></div></div></div></div></div>
</div></section>
<?php afficher_pied_de_page(); ?>
</body>
</html>