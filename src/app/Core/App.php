<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Initialisation de l'application :
 *  - chargement des variables d'environnement (.env)
 *  - autoload des classes (sans Composer)
 *  - démarrage de la session (durcie : timeout d'inactivité, rotation d'ID)
 *  - affichage des erreurs selon l'environnement
 *  - en-têtes de sécurité HTTP
 *  - handler d'exception global (pages d'erreur stylisées)
 */
final class App
{
    /** Inactivité maximale avant déconnexion automatique (secondes). */
    private const SESSION_IDLE_TIMEOUT = 1200;   // 20 min
    /** Durée de vie d'un ID de session avant rotation (secondes). */
    private const SESSION_ID_TTL = 1800;         // 30 min

    public static function boot(): void
    {
        self::loadEnv();
        self::registerAutoload();
        require_once __DIR__ . '/helpers.php';
        self::configureErrors();
        self::registerExceptionHandler();
        self::startSession();
        self::enforceSessionLifetime();
        SecurityHeaders::send();
    }

    /** Charge le fichier .env dans $_ENV (parseur simple clé=valeur). */
    private static function loadEnv(): void
    {
        $file = dirname(__DIR__, 2) . '/.env';
        if (!is_file($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $_ENV[trim($key)] = trim($value);
        }
    }

    /** Autoloader PSR-4 maison : App\Xxx\Yyy -> app/Xxx/Yyy.php */
    private static function registerAutoload(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $path = dirname(__DIR__) . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($path)) {
                require $path;
            }
        });
    }

    private static function configureErrors(): void
    {
        $isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
        error_reporting(E_ALL);
        ini_set('display_errors', $isDev ? '1' : '0');
        ini_set('log_errors', '1');
    }

    /**
     * Capture toute exception non rattrapée et rend une page d'erreur propre,
     * sans jamais exposer de trace en production.
     */
    private static function registerExceptionHandler(): void
    {
        set_exception_handler(static function (\Throwable $e): void {
            $status = $e instanceof HttpException ? $e->getStatus() : 500;

            if (!headers_sent()) {
                http_response_code($status);
                if ($e instanceof HttpException) {
                    foreach ($e->getHeaders() as $name => $value) {
                        header($name . ': ' . $value);
                    }
                }
            }

            // On journalise toujours le détail (les 500 surtout), jamais à l'écran en prod.
            if ($status === 500 || ($_ENV['APP_ENV'] ?? 'prod') !== 'prod') {
                error_log('[' . $status . '] ' . $e->getMessage()
                    . ' @ ' . $e->getFile() . ':' . $e->getLine());
            }

            View::renderError($status);
        });
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_start([
            'cookie_httponly' => true,                          // inaccessible au JS (anti-XSS)
            'cookie_samesite' => 'Lax',                         // limite l'envoi inter-sites (anti-CSRF)
            'cookie_secure'   => SecurityHeaders::isHttps(),    // cookie HTTPS-only quand c'est possible
            'use_strict_mode' => true,                          // refuse les ID de session non générés par le serveur
        ]);
    }

    /**
     * Expiration d'inactivité + rotation périodique de l'ID de session.
     * Si l'utilisateur reste inactif trop longtemps, on le déconnecte.
     */
    private static function enforceSessionLifetime(): void
    {
        $now = time();

        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > self::SESSION_IDLE_TIMEOUT) {
            Auth::logout();
            session_start();                       // nouvelle session vierge pour le message flash
            Flash::set('info', 'Votre session a expiré pour cause d\'inactivité. Merci de vous reconnecter.');
        }
        $_SESSION['last_activity'] = $now;

        if (!isset($_SESSION['id_created'])) {
            $_SESSION['id_created'] = $now;
        } elseif (($now - $_SESSION['id_created']) > self::SESSION_ID_TTL) {
            session_regenerate_id(true);
            $_SESSION['id_created'] = $now;
        }
    }
}
