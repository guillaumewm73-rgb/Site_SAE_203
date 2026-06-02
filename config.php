<?php
$serveurActuel = $_SERVER['SERVER_NAME'] ?? 'localhost';
$estEnLocal = in_array($serveurActuel, ['localhost', '127.0.0.1', '::1'], true);

if ($estEnLocal) {
    define('NOM_BD', 'sae203_bdd');
    define('SERVEUR_BD', 'localhost');
    define('LOGIN_BD', 'root');
    define('PASSE_BD', '');
} else {
    define('NOM_BD', 'ijtebowcompte13');
    define('SERVEUR_BD', 'ijtebowcompte13.mysql.db');
    define('PORT_BD', '');
    define('LOGIN_BD', 'ijtebowcompte13');
    define('PASSE_BD', 'v8ng67SF2026');
}
?>
