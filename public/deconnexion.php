<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// On vide toutes les variables de session : rôle, id admin, id visiteur, etc.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    // Suppression du cookie de session côté navigateur.
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

// Destruction définitive de la session côté serveur.
session_destroy();

// Après déconnexion, on revient sur la page de connexion.
header('Location: page_connexion.php');
exit;
