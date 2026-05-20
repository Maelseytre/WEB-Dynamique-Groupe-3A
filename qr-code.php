<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/qrcode.php';

$code = trim($_GET['code'] ?? '');
$utilisateur = utilisateur_actuel();

if ($code === '' || !$utilisateur) {
    http_response_code(403);
    exit;
}

$requete = bdd()->prepare('
    SELECT r.*, e.organizer_id
    FROM reservations r
    JOIN events e ON e.id = r.event_id
    WHERE r.ticket_code = ? AND r.status = "confirmed"
');
$requete->execute([$code]);
$reservation = $requete->fetch();

$autorise = $reservation && (
    (int) $reservation['user_id'] === (int) $utilisateur['id']
    || (int) $reservation['organizer_id'] === (int) $utilisateur['id']
    || $utilisateur['role'] === 'admin'
);

if (!$autorise) {
    http_response_code(403);
    exit;
}

header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: private, max-age=300');
echo qr_code_svg(lien_validation_billet($code));
