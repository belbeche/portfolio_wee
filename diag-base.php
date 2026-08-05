<?php
/**
 * Diagnostic de connexion a la base, sans Symfony et sans Doctrine.
 *
 * A poser a la racine du projet (a cote de composer.json), puis :
 *     php84 diag-base.php
 *
 * Il repond a trois questions, dans l'ordre ou il faut se les poser :
 *   1. quels fichiers d'environnement existent reellement sur le serveur
 *   2. vers quel serveur de base le site essaie de se connecter
 *   3. ce port est-il ouvert, oui ou non
 *
 * Le mot de passe n'est jamais affiche, seulement le fait qu'il soit
 * renseigne ou vide.
 */

$fichiers = ['.env', '.env.local', '.env.local.php', '.env.prod', '.env.prod.local'];

echo "FICHIERS D'ENVIRONNEMENT\n";
foreach ($fichiers as $f) {
    printf("  %-18s %s\n", $f, is_file($f) ? 'present' : 'absent');
}

// Ordre de priorite reel de Symfony : variable d'environnement, puis
// .env.local.php, puis .env.local, puis .env.
$url = getenv('DATABASE_URL') ?: null;
$source = $url ? 'variable d\'environnement du serveur' : null;

if (!$url && is_file('.env.local.php')) {
    $valeurs = include '.env.local.php';
    if (is_array($valeurs) && !empty($valeurs['DATABASE_URL'])) {
        $url = $valeurs['DATABASE_URL'];
        $source = '.env.local.php';
    }
}

foreach (['.env.local', '.env'] as $f) {
    if ($url || !is_file($f)) {
        continue;
    }
    foreach (file($f) as $ligne) {
        if (preg_match('/^\s*DATABASE_URL\s*=\s*(.*)$/', $ligne, $m)) {
            $url = trim(trim($m[1]), "\"'");
            $source = $f;
        }
    }
}

if (!$url) {
    echo "\nAucun DATABASE_URL trouve nulle part. C'est la cause.\n";
    exit(1);
}

$p = parse_url($url);
$hote = $p['host'] ?? '';
$port = (int) ($p['port'] ?? 3306);

echo "\nCIBLE DE CONNEXION\n";
printf("  source        : %s\n", $source);
printf("  hote          : %s\n", $hote !== '' ? $hote : '(vide)');
printf("  port          : %d%s\n", $port, isset($p['port']) ? '' : ' (defaut)');
printf("  base          : %s\n", ltrim($p['path'] ?? '', '/'));
printf("  utilisateur   : %s\n", $p['user'] ?? '(vide)');
printf("  mot de passe  : %s\n", empty($p['pass']) ? 'VIDE' : 'renseigne');

echo "\nTEST DU PORT\n";
$debut = microtime(true);
$socket = @fsockopen($hote, $port, $code, $message, 5);
$ms = (int) round((microtime(true) - $debut) * 1000);

if ($socket) {
    printf("  %s:%d repond en %d ms : PORT OUVERT\n", $hote, $port, $ms);
    fclose($socket);
    echo "\n  Le port est ouvert. Si le site echoue quand meme, ce n'est plus\n";
    echo "  un « Connection refused » mais un probleme d'identifiants ou de\n";
    echo "  base absente : relance le site et regarde le nouveau message.\n";
    exit(0);
}

printf("  %s:%d ne repond pas apres %d ms : PORT FERME (%s)\n", $hote, $port, $ms, $message);

echo "\n  Rien n'ecoute a cette adresse. Ce n'est ni un mot de passe refuse,\n";
echo "  ni une base absente : c'est le serveur MySQL qui est arrete, ou\n";
echo "  l'adresse qui est fausse.\n";

if ('localhost' === $hote || '127.0.0.1' === $hote) {
    echo "\n  ATTENTION : l'hote est « ".$hote." », c'est-a-dire l'interieur de ce\n";
    echo "  conteneur. Si ta base est hebergee ailleurs (onglet Databases du\n";
    echo "  panneau), c'est la cause : il manque un fichier .env.local sur le\n";
    echo "  serveur, et le site retombe sur le .env de developpement.\n";
}

exit(1);
