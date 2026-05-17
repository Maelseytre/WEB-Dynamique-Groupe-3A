<?php
declare(strict_types=1);

$sessionPath = dirname(__DIR__) . '/tmp';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);
session_start();

const DB_HOST = '127.0.0.1';
const DB_NAME = 'omnesevent';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $server = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    init_database($pdo);

    return $pdo;
}

function init_database(PDO $pdo): void
{
    static $done = false;
    if ($done) {
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

    seed_database($pdo);
    $done = true;
}

function seed_database(PDO $pdo): void
{
    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
        return;
    }

    $insertUser = $pdo->prepare('
        INSERT INTO users (prenom, nom, email, password_hash, role, association, status, promo, campus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $hash = password_hash('demo123', PASSWORD_DEFAULT);
    $insertUser->execute(['Demo', 'Participant', 'participant@demo.fr', $hash, 'participant', null, 'active', 'ING2', 'Paris Etoile']);
    $insertUser->execute(['Demo', 'Organisateur', 'organisateur@demo.fr', $hash, 'organisateur', 'BDE Omnes', 'active', null, 'Paris Etoile']);
    $insertUser->execute(['Demo', 'Admin', 'admin@demo.fr', $hash, 'admin', null, 'active', null, 'Paris Etoile']);
    $insertUser->execute(['Alice', 'BDS', 'alice.bds@omnes.fr', $hash, 'organisateur', 'BDS Omnes', 'pending', null, 'Paris Etoile']);

    $orgId = (int) $pdo->query("SELECT id FROM users WHERE email = 'organisateur@demo.fr'")->fetchColumn();
    $events = [
        ['Soiree d Integration BDE 2025', 'soiree', 'BDE Omnes', 'La grande soiree d integration organisee par le BDE Omnes. DJ sets, animations, bar, photobooth et surprises pour toute la communaute Omnes.', '2026-06-20', '20:00:00', '02:00:00', 'Salle des fetes, Campus Paris', '12 Rue de Presbourg, 75008 Paris', 100, 0, 'images/illlustration_soirée_1.jpg'],
        ['Tournoi de Football Inter-Ecoles', 'sport', 'BDS Omnes', 'Tournoi amical ouvert aux etudiants Omnes avec phases de poules, finales et remise de prix.', '2026-06-21', '10:00:00', '18:00:00', 'Terrain de sport Nord', '', 60, 0, 'images/illustration_foot_1.jpg'],
        ['Visite du Musee d Orsay', 'culture', 'Club Culturel', 'Sortie culturelle encadree au Musee d Orsay avec parcours libre et temps d echange.', '2026-06-25', '14:00:00', '17:00:00', 'Musee d Orsay, Paris', '', 30, 0, 'images/illustration_musee_1.jpeg'],
        ['Conference : IA et Monde du Travail', 'conference', 'Junior Entreprise', 'Conference sur les usages de l intelligence artificielle dans les entreprises, avec questions reponses.', '2026-06-30', '18:00:00', '20:00:00', 'Amphitheatre A, Campus Lyon', '', 200, 0, 'images/illustration_conferences_1.jpg'],
    ];
    $insertEvent = $pdo->prepare('
        INSERT INTO events (organizer_id, titre, categorie, association, description, date_debut, heure_debut, heure_fin, lieu, adresse, capacite, prix, affiche)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($events as $event) {
        $insertEvent->execute(array_merge([$orgId], $event));
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('connexion.php');
    }
    return $user;
}

function require_role(array $roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        redirect('Index.php');
    }
    return $user;
}

function flash(?string $key = null, ?string $message = null): ?string
{
    if ($key !== null && $message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if ($key === null) {
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function reservation_count(int $eventId, ?string $status = 'confirmed'): int
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(nb_places), 0) FROM reservations WHERE event_id = ? AND status = ?');
    $stmt->execute([$eventId, $status]);
    return (int) $stmt->fetchColumn();
}

function format_date_fr(string $date): string
{
    $dt = new DateTime($date);
    $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois = ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];
    return $jours[(int) $dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int) $dt->format('n') - 1] . ' ' . $dt->format('Y');
}

function category_label(string $category): string
{
    return [
        'soiree' => 'Soiree',
        'sport' => 'Sport',
        'culture' => 'Culture',
        'conference' => 'Conference',
    ][$category] ?? ucfirst($category);
}

function nav(string $active = ''): void
{
    $user = current_user();
    $logout = $user ? '<a href="logout.php" class="btn btn-outline btn-sm">Deconnexion</a>' : '<a href="connexion.php" class="btn btn-primary btn-sm">Connexion</a>';
    echo '<nav class="menu"><div class="nav-menu">';
    echo '<a href="Index.php" class="navbar-brand">OmnesEvent</a><ul class="navbar-nav">';
    echo '<li><a href="Index.php" class="' . ($active === 'home' ? 'active' : '') . '">Accueil</a></li>';
    echo '<li><a href="mes-billets.php" class="' . ($active === 'tickets' ? 'active' : '') . '">Mes Billets</a></li>';
    if ($user && in_array($user['role'], ['organisateur', 'admin'], true)) {
        echo '<li><a href="creer-evenement.php" class="' . ($active === 'create' ? 'active' : '') . '">Creer un evenement</a></li>';
        echo '<li><a href="tableau-de-bord.php" class="' . ($active === 'dashboard' ? 'active' : '') . '">Dashboard</a></li>';
    }
    if ($user && $user['role'] === 'admin') {
        echo '<li><a href="admin.php" class="' . ($active === 'admin' ? 'active' : '') . '">Admin</a></li>';
    }
    echo '<li><a href="profil.php" class="' . ($active === 'profile' ? 'active' : '') . '">Mon Profil</a></li>';
    echo '<li>' . $logout . '</li></ul>';
    echo '<img src="images/omneseducation_logo.jpeg" alt="Logo Omnes Education" class="navbar-logo">';
    echo '<button class="hamburger" id="hamburger"><span></span><span></span><span></span></button></div></nav>';
    echo '<div class="mobile-menu" id="mobileMenu"><a href="Index.php">Accueil</a><a href="mes-billets.php">Mes Billets</a><a href="profil.php">Mon Profil</a>' . ($user ? '<a href="logout.php">Deconnexion</a>' : '<a href="connexion.php">Connexion</a>') . '</div>';
}

function footer_html(): void
{
    echo '<footer class="footer"><div class="footer-inner"><div class="footer-brand">OmnesEvent</div><div class="footer-bottom"><p>&copy; 2026 OmnesEvent - Projet Web Dynamique ING2</p></div></div></footer>';
    echo '<script src="ui.js"></script>';
}
