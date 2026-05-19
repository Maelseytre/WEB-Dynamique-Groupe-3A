<?php
declare(strict_types=1);

$cheminSession = dirname(__DIR__) . '/tmp';
if (!is_dir($cheminSession)) {
    mkdir($cheminSession, 0775, true);
}
session_save_path($cheminSession);
session_start();

const DB_HOTE = '127.0.0.1';
const DB_NOM  = 'omnesevent';
const DB_UTIL = 'root';
const DB_PASS = '';

function bdd(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $serveur = new PDO('mysql:host=' . DB_HOTE . ';charset=utf8mb4', DB_UTIL, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $serveur->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NOM . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo = new PDO('mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=utf8mb4', DB_UTIL, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    initialiser_base($pdo);

    return $pdo;
}

function initialiser_base(PDO $pdo): void
{
    static $initialise = false;
    if ($initialise) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prenom VARCHAR(80) NOT NULL,
            nom VARCHAR(80) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('participant','organisateur','admin') NOT NULL DEFAULT 'participant',
            association VARCHAR(120) DEFAULT NULL,
            status ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
            telephone VARCHAR(40) DEFAULT NULL,
            promo VARCHAR(40) DEFAULT NULL,
            campus VARCHAR(80) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            organizer_id INT NOT NULL,
            titre VARCHAR(160) NOT NULL,
            categorie VARCHAR(40) NOT NULL,
            association VARCHAR(120) NOT NULL,
            description TEXT NOT NULL,
            date_debut DATE NOT NULL,
            date_fin DATE DEFAULT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME DEFAULT NULL,
            lieu VARCHAR(190) NOT NULL,
            adresse VARCHAR(255) DEFAULT NULL,
            capacite INT NOT NULL,
            prix DECIMAL(8,2) NOT NULL DEFAULT 0,
            affiche VARCHAR(255) DEFAULT NULL,
            waitlist_enabled TINYINT(1) NOT NULL DEFAULT 1,
            manual_validation TINYINT(1) NOT NULL DEFAULT 0,
            status ENUM('draft','published','cancelled') NOT NULL DEFAULT 'published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            nb_places INT NOT NULL DEFAULT 1,
            commentaire TEXT DEFAULT NULL,
            ticket_code VARCHAR(40) NOT NULL UNIQUE,
            status ENUM('confirmed','waitlist','cancelled') NOT NULL DEFAULT 'confirmed',
            presence_validated TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_event (event_id, user_id),
            FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    peupler_base($pdo);
    $initialise = true;
}

function peupler_base(PDO $pdo): void
{
    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
        return;
    }

    $insererUtilisateur = $pdo->prepare('
        INSERT INTO users (prenom, nom, email, password_hash, role, association, status, promo, campus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $hachage = password_hash('demo123', PASSWORD_DEFAULT);
    $insererUtilisateur->execute(['Demo', 'Participant', 'participant@demo.fr', $hachage, 'participant', null, 'active', 'ING2', 'Paris Etoile']);
    $insererUtilisateur->execute(['Demo', 'Organisateur', 'organisateur@demo.fr', $hachage, 'organisateur', 'BDE Omnes', 'active', null, 'Paris Etoile']);
    $insererUtilisateur->execute(['Demo', 'Admin', 'admin@demo.fr', $hachage, 'admin', null, 'active', null, 'Paris Etoile']);
    $insererUtilisateur->execute(['Alice', 'BDS', 'alice.bds@omnes.fr', $hachage, 'organisateur', 'BDS Omnes', 'pending', null, 'Paris Etoile']);

    $idOrganisateur = (int) $pdo->query("SELECT id FROM users WHERE email = 'organisateur@demo.fr'")->fetchColumn();
    $evenements = [
        ['Soiree d Integration BDE 2025', 'soiree', 'BDE Omnes', 'La grande soiree d integration organisee par le BDE Omnes. DJ sets, animations, bar, photobooth et surprises pour toute la communaute Omnes.', '2026-06-20', '20:00:00', '02:00:00', 'Salle des fetes, Campus Paris', '12 Rue de Presbourg, 75008 Paris', 100, 0, 'images/illlustration_soirée_1.jpg'],
        ['Tournoi de Football Inter-Ecoles', 'sport', 'BDS Omnes', 'Tournoi amical ouvert aux etudiants Omnes avec phases de poules, finales et remise de prix.', '2026-06-21', '10:00:00', '18:00:00', 'Terrain de sport Nord', '', 60, 0, 'images/illustration_foot_1.jpg'],
        ['Visite du Musee d Orsay', 'culture', 'Club Culturel', 'Sortie culturelle encadree au Musee d Orsay avec parcours libre et temps d echange.', '2026-06-25', '14:00:00', '17:00:00', 'Musee d Orsay, Paris', '', 30, 0, 'images/illustration_musee_1.jpeg'],
        ['Conference : IA et Monde du Travail', 'conference', 'Junior Entreprise', 'Conference sur les usages de l intelligence artificielle dans les entreprises, avec questions reponses.', '2026-06-30', '18:00:00', '20:00:00', 'Amphitheatre A, Campus Lyon', '', 200, 0, 'images/illustration_conferences_1.jpg'],
    ];
    $insererEvenement = $pdo->prepare('
        INSERT INTO events (organizer_id, titre, categorie, association, description, date_debut, heure_debut, heure_fin, lieu, adresse, capacite, prix, affiche)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($evenements as $evenement) {
        $insererEvenement->execute(array_merge([$idOrganisateur], $evenement));
    }
}

function echapper(?string $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

function rediriger(string $chemin): never
{
    header('Location: ' . $chemin);
    exit;
}

function url_absolue(string $chemin): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($base === '.' || $base === '/') {
        $base = '';
    }
    return $scheme . '://' . $host . $base . '/' . ltrim($chemin, '/');
}

function lien_validation_billet(string $codeBillet): string
{
    return url_absolue('valider-billet.php?code=' . rawurlencode($codeBillet));
}

function generer_code_billet(): string
{
    for ($tentative = 0; $tentative < 10; $tentative++) {
        $code = 'OE-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $requete = bdd()->prepare('SELECT COUNT(*) FROM reservations WHERE ticket_code = ?');
        $requete->execute([$code]);
        if ((int) $requete->fetchColumn() === 0) {
            return $code;
        }
    }
    throw new RuntimeException('Impossible de generer un code billet unique.');
}

function utilisateur_actuel(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $requete = bdd()->prepare('SELECT * FROM users WHERE id = ?');
    $requete->execute([$_SESSION['user_id']]);
    return $requete->fetch() ?: null;
}

function exiger_connexion(): array
{
    $utilisateur = utilisateur_actuel();
    if (!$utilisateur) {
        rediriger('connexion.php');
    }
    return $utilisateur;
}

function exiger_role(array $roles): array
{
    $utilisateur = exiger_connexion();
    if (!in_array($utilisateur['role'], $roles, true)) {
        rediriger('Index.php');
    }
    return $utilisateur;
}

function message_flash(?string $cle = null, ?string $contenu = null): ?string
{
    if ($cle !== null && $contenu !== null) {
        $_SESSION['flash'][$cle] = $contenu;
        return null;
    }
    if ($cle === null) {
        return null;
    }
    $valeur = $_SESSION['flash'][$cle] ?? null;
    unset($_SESSION['flash'][$cle]);
    return $valeur;
}

function compter_reservations(int $idEvenement, ?string $statut = 'confirmed'): int
{
    $requete = bdd()->prepare('SELECT COALESCE(SUM(nb_places), 0) FROM reservations WHERE event_id = ? AND status = ?');
    $requete->execute([$idEvenement, $statut]);
    return (int) $requete->fetchColumn();
}

function formater_date(string $date): string
{
    $dt = new DateTime($date);
    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois = ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];
    return $jours[(int) $dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int) $dt->format('n') - 1] . ' ' . $dt->format('Y');
}

function libelle_categorie(string $categorie): string
{
    return [
        'soiree' => 'Soiree',
        'sport' => 'Sport',
        'culture' => 'Culture',
        'conference' => 'Conference',
    ][$categorie] ?? ucfirst($categorie);
}

function afficher_navigation(string $actif = ''): void
{
    $utilisateur = utilisateur_actuel();
    $deconnexion = $utilisateur ? '<a href="logout.php" class="bouton bouton-contour bouton-petit">Deconnexion</a>' : '<a href="connexion.php" class="bouton bouton-primaire bouton-petit">Connexion</a>';
    echo '<nav class="barre-nav"><div class="nav-contenu">';
    echo '<a href="Index.php" class="nav-marque">OmnesEvent</a><ul class="nav-liens">';
    echo '<li><a href="Index.php" class="' . ($actif === 'accueil' ? 'actif' : '') . '">Accueil</a></li>';
    echo '<li><a href="mes-billets.php" class="' . ($actif === 'billets' ? 'actif' : '') . '">Mes Billets</a></li>';
    if ($utilisateur && in_array($utilisateur['role'], ['organisateur', 'admin'], true)) {
        echo '<li><a href="creer-evenement.php" class="' . ($actif === 'creer' ? 'actif' : '') . '">Creer un evenement</a></li>';
        echo '<li><a href="tableau-de-bord.php" class="' . ($actif === 'tableau' ? 'actif' : '') . '">Tableau de bord</a></li>';
    }
    if ($utilisateur && $utilisateur['role'] === 'admin') {
        echo '<li><a href="admin.php" class="' . ($actif === 'admin' ? 'actif' : '') . '">Admin</a></li>';
    }
    echo '<li><a href="profil.php" class="' . ($actif === 'profil' ? 'actif' : '') . '">Mon Profil</a></li>';
    echo '<li>' . $deconnexion . '</li></ul>';
    echo '<img src="images/omneseducation_logo.jpeg" alt="Logo Omnes Education" class="nav-logo">';
    echo '<button class="nav-hamburger" id="boutonHamburger"><span></span><span></span><span></span></button></div></nav>';
    echo '<div class="menu-mobile" id="menuMobile"><a href="Index.php">Accueil</a><a href="mes-billets.php">Mes Billets</a><a href="profil.php">Mon Profil</a>' . ($utilisateur ? '<a href="logout.php">Deconnexion</a>' : '<a href="connexion.php">Connexion</a>') . '</div>';
}

function afficher_pied_de_page(): void
{
    echo '<footer class="pied-page"><div class="pied-page-interieur"><div class="pied-page-marque">OmnesEvent</div><div class="pied-page-bas"><p>&copy; 2026 OmnesEvent - Projet Web Dynamique ING2</p></div></div></footer>';
    echo '<script src="ui.js"></script>';
}
