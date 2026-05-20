<?php
declare(strict_types=1);

function qr_code_svg(string $texte, int $taille = 196): string
{
    $version = 6;
    $tailleModules = 17 + 4 * $version;
    $donneesParBloc = 68;
    $nbBlocs = 2;
    $correctionParBloc = 18;
    $capaciteDonnees = $donneesParBloc * $nbBlocs;

    $octets = array_values(unpack('C*', $texte));
    if (count($octets) > 134) {
        throw new InvalidArgumentException('Le texte du QR code est trop long.');
    }

    $donnees = qr_encoder_donnees($octets, $capaciteDonnees);
    $blocsDonnees = array_chunk($donnees, $donneesParBloc);
    $blocsCorrection = [];
    foreach ($blocsDonnees as $bloc) {
        $blocsCorrection[] = qr_reed_solomon_reste($bloc, $correctionParBloc);
    }

    $mots = [];
    for ($i = 0; $i < $donneesParBloc; $i++) {
        for ($bloc = 0; $bloc < $nbBlocs; $bloc++) {
            $mots[] = $blocsDonnees[$bloc][$i];
        }
    }
    for ($i = 0; $i < $correctionParBloc; $i++) {
        for ($bloc = 0; $bloc < $nbBlocs; $bloc++) {
            $mots[] = $blocsCorrection[$bloc][$i];
        }
    }

    [$modules, $fonctions] = qr_creer_matrice($tailleModules);
    qr_appliquer_format($modules, $fonctions);
    qr_placer_donnees($modules, $fonctions, $mots);
    qr_appliquer_format($modules, $fonctions);

    return qr_svg_depuis_matrice($modules, $taille);
}

function qr_encoder_donnees(array $octets, int $capacite): array
{
    $bits = [];
    qr_ajouter_bits($bits, 0b0100, 4);
    qr_ajouter_bits($bits, count($octets), 8);
    foreach ($octets as $octet) {
        qr_ajouter_bits($bits, $octet, 8);
    }

    $capaciteBits = $capacite * 8;
    qr_ajouter_bits($bits, 0, min(4, $capaciteBits - count($bits)));
    while (count($bits) % 8 !== 0) {
        $bits[] = 0;
    }

    $donnees = [];
    for ($i = 0; $i < count($bits); $i += 8) {
        $octet = 0;
        for ($j = 0; $j < 8; $j++) {
            $octet = ($octet << 1) | $bits[$i + $j];
        }
        $donnees[] = $octet;
    }

    for ($pad = 0; count($donnees) < $capacite; $pad++) {
        $donnees[] = $pad % 2 === 0 ? 0xEC : 0x11;
    }
    return $donnees;
}

function qr_ajouter_bits(array &$bits, int $valeur, int $longueur): void
{
    for ($i = $longueur - 1; $i >= 0; $i--) {
        $bits[] = ($valeur >> $i) & 1;
    }
}

function qr_creer_matrice(int $taille): array
{
    $modules = array_fill(0, $taille, array_fill(0, $taille, false));
    $fonctions = array_fill(0, $taille, array_fill(0, $taille, false));

    qr_dessiner_finder($modules, $fonctions, 3, 3);
    qr_dessiner_finder($modules, $fonctions, $taille - 4, 3);
    qr_dessiner_finder($modules, $fonctions, 3, $taille - 4);
    qr_dessiner_alignement($modules, $fonctions, $taille - 7, $taille - 7);

    for ($i = 0; $i < $taille; $i++) {
        if (!$fonctions[6][$i]) {
            qr_definir_module($modules, $fonctions, $i, 6, $i % 2 === 0);
        }
        if (!$fonctions[$i][6]) {
            qr_definir_module($modules, $fonctions, 6, $i, $i % 2 === 0);
        }
    }

    qr_definir_module($modules, $fonctions, 8, $taille - 8, true);
    return [$modules, $fonctions];
}

function qr_dessiner_finder(array &$modules, array &$fonctions, int $centreX, int $centreY): void
{
    $taille = count($modules);
    for ($dy = -4; $dy <= 4; $dy++) {
        for ($dx = -4; $dx <= 4; $dx++) {
            $x = $centreX + $dx;
            $y = $centreY + $dy;
            if ($x < 0 || $y < 0 || $x >= $taille || $y >= $taille) {
                continue;
            }
            $distance = max(abs($dx), abs($dy));
            qr_definir_module($modules, $fonctions, $x, $y, $distance === 3 || $distance <= 1);
        }
    }
}

function qr_dessiner_alignement(array &$modules, array &$fonctions, int $centreX, int $centreY): void
{
    for ($dy = -2; $dy <= 2; $dy++) {
        for ($dx = -2; $dx <= 2; $dx++) {
            $distance = max(abs($dx), abs($dy));
            qr_definir_module($modules, $fonctions, $centreX + $dx, $centreY + $dy, $distance !== 1);
        }
    }
}

function qr_definir_module(array &$modules, array &$fonctions, int $x, int $y, bool $noir): void
{
    $modules[$y][$x] = $noir;
    $fonctions[$y][$x] = true;
}

function qr_placer_donnees(array &$modules, array $fonctions, array $mots): void
{
    $taille = count($modules);
    $bits = [];
    foreach ($mots as $mot) {
        qr_ajouter_bits($bits, $mot, 8);
    }

    $indexBit = 0;
    $versLeHaut = true;
    for ($xDroite = $taille - 1; $xDroite >= 1; $xDroite -= 2) {
        if ($xDroite === 6) {
            $xDroite--;
        }
        for ($vertical = 0; $vertical < $taille; $vertical++) {
            $y = $versLeHaut ? $taille - 1 - $vertical : $vertical;
            for ($colonne = 0; $colonne < 2; $colonne++) {
                $x = $xDroite - $colonne;
                if ($fonctions[$y][$x]) {
                    continue;
                }
                $bit = $indexBit < count($bits) ? (bool) $bits[$indexBit] : false;
                $masque = (($x + $y) % 2) === 0;
                $modules[$y][$x] = $bit !== $masque;
                $indexBit++;
            }
        }
        $versLeHaut = !$versLeHaut;
    }
}

function qr_appliquer_format(array &$modules, array &$fonctions): void
{
    $taille = count($modules);
    $bitsFormat = qr_calculer_bits_format(1, 0);

    for ($i = 0; $i <= 5; $i++) {
        qr_definir_module($modules, $fonctions, 8, $i, (($bitsFormat >> $i) & 1) === 1);
    }
    qr_definir_module($modules, $fonctions, 8, 7, (($bitsFormat >> 6) & 1) === 1);
    qr_definir_module($modules, $fonctions, 8, 8, (($bitsFormat >> 7) & 1) === 1);
    qr_definir_module($modules, $fonctions, 7, 8, (($bitsFormat >> 8) & 1) === 1);
    for ($i = 9; $i < 15; $i++) {
        qr_definir_module($modules, $fonctions, 14 - $i, 8, (($bitsFormat >> $i) & 1) === 1);
    }

    for ($i = 0; $i < 8; $i++) {
        qr_definir_module($modules, $fonctions, $taille - 1 - $i, 8, (($bitsFormat >> $i) & 1) === 1);
    }
    for ($i = 8; $i < 15; $i++) {
        qr_definir_module($modules, $fonctions, 8, $taille - 15 + $i, (($bitsFormat >> $i) & 1) === 1);
    }
}

function qr_calculer_bits_format(int $niveauCorrection, int $masque): int
{
    $donnees = ($niveauCorrection << 3) | $masque;
    $reste = $donnees;
    for ($i = 0; $i < 10; $i++) {
        $reste = ($reste << 1) ^ (($reste >> 9) * 0x537);
    }
    return (($donnees << 10) | $reste) ^ 0x5412;
}

function qr_reed_solomon_reste(array $donnees, int $degre): array
{
    $generateur = qr_reed_solomon_generateur($degre);
    $reste = array_fill(0, $degre, 0);

    foreach ($donnees as $octet) {
        $facteur = $octet ^ $reste[0];
        for ($i = 0; $i < $degre - 1; $i++) {
            $reste[$i] = $reste[$i + 1] ^ qr_gf_multiplie($generateur[$i], $facteur);
        }
        $reste[$degre - 1] = qr_gf_multiplie($generateur[$degre - 1], $facteur);
    }
    return $reste;
}

function qr_reed_solomon_generateur(int $degre): array
{
    $poly = [1];
    $racine = 1;
    for ($i = 0; $i < $degre; $i++) {
        $suivant = array_fill(0, count($poly) + 1, 0);
        foreach ($poly as $j => $coefficient) {
            $suivant[$j] ^= $coefficient;
            $suivant[$j + 1] ^= qr_gf_multiplie($coefficient, $racine);
        }
        $poly = $suivant;
        $racine = qr_gf_multiplie($racine, 0x02);
    }
    array_shift($poly);
    return $poly;
}

function qr_gf_multiplie(int $x, int $y): int
{
    $produit = 0;
    for ($i = 0; $i < 8; $i++) {
        if (($y & 1) !== 0) {
            $produit ^= $x;
        }
        $retenue = ($x & 0x80) !== 0;
        $x = ($x << 1) & 0xFF;
        if ($retenue) {
            $x ^= 0x1D;
        }
        $y >>= 1;
    }
    return $produit;
}

function qr_svg_depuis_matrice(array $modules, int $taille): string
{
    $nbModules = count($modules);
    $marge = 4;
    $dimension = $nbModules + $marge * 2;
    $chemins = [];

    for ($y = 0; $y < $nbModules; $y++) {
        for ($x = 0; $x < $nbModules; $x++) {
            if ($modules[$y][$x]) {
                $chemins[] = 'M' . ($x + $marge) . ',' . ($y + $marge) . 'h1v1h-1z';
            }
        }
    }

    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $taille . '" height="' . $taille . '" viewBox="0 0 ' . $dimension . ' ' . $dimension . '" role="img" aria-label="QR code billet"><rect width="100%" height="100%" fill="#fff"/><path d="' . implode('', $chemins) . '" fill="#000"/></svg>';
}
