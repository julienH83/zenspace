<?php

declare(strict_types=1);

/**
 * Amorçage des tests : charge l'autoloader Composer (PSR-4 App\ + helpers globaux).
 * Définit quelques variables d'environnement neutres pour les tests unitaires.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$_ENV['APP_ENV']  = 'test';
$_ENV['APP_NAME'] = 'ZenSpace';
$_ENV['APP_URL']  = 'http://localhost';
$_ENV['RATE_LIMIT_DRIVER'] = 'file';
