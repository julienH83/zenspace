<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Initialisation de l'application :
 *  - chargement des variables d'environnement (.env)
 *  - autoload des classes (sans Composer)
 *  - démarrage de la session
 *  - affichage des erreurs selon l'environnement
 */
final class App
{
    public static function boot(): void
    {
        self::loadEnv();
        self::registerAutoload();
        require __DIR__ . '/helpers.php';
        self::configureErrors();
        self::startSession();
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
    }

    private static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_httponly' => true,   // cookie inaccessible au JavaScript (anti-XSS)
                'cookie_samesite' => 'Lax',  // limite l'envoi inter-sites (anti-CSRF)
            ]);
        }
    }
}
