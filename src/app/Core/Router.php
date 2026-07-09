<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimaliste.
 *
 * Associe une méthode HTTP + un chemin (ex: GET /prestation/{slug}) à une
 * action de contrôleur (ex: [ServiceController::class, 'show']).
 *
 * Les segments entre accolades sont des paramètres dynamiques :
 *   {id}            → capture « tout sauf / »
 *   {id:\d+}        → capture contrainte par une expression régulière
 *   {slug:[a-z0-9\-]+}
 *
 * Le routeur distingue :
 *   - 404 : aucun chemin ne correspond
 *   - 405 : le chemin existe mais pas pour cette méthode HTTP (+ en-tête Allow)
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, path:string, handler:array}> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $this->compile($path),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Transforme un chemin avec paramètres en expression régulière.
     * Gère {nom} (défaut [^/]+) et {nom:regex} (contrainte personnalisée).
     */
    private function compile(string $path): string
    {
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([^}]+))?\}#',
            static fn(array $m): string => '(' . ($m[2] ?? '[^/]+') . ')',
            $path
        );
        return '#^' . $regex . '$#';
    }

    /**
     * Trouve la route correspondant à la requête courante et exécute l'action.
     * Lève une HttpException (404 ou 405) traitée par le handler global.
     */
    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
        if ($path === '') {
            $path = '/';
        }

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowedMethods[$route['method']] = true;   // chemin connu, autre méthode
                continue;
            }

            array_shift($matches); // retire la correspondance complète
            $matches = array_map('urldecode', $matches);
            [$class, $action] = $route['handler'];
            (new $class())->$action(...$matches);
            return;
        }

        // Le chemin existe mais la méthode n'est pas autorisée → 405.
        if ($allowedMethods !== []) {
            throw new HttpException(405, 'Méthode non autorisée.', [
                'Allow' => implode(', ', array_keys($allowedMethods)),
            ]);
        }

        // Aucun chemin ne correspond → 404.
        throw new HttpException(404, 'Page introuvable.');
    }
}
