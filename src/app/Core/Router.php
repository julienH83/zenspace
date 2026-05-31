<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimaliste.
 *
 * Associe une méthode HTTP + un chemin (ex: GET /prestation/{slug}) à une
 * action de contrôleur (ex: [ServiceController::class, 'show']).
 *
 * Les segments entre accolades ({id}, {slug}) sont des paramètres dynamiques
 * transmis à l'action.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:array}> */
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
            'handler' => $handler,
        ];
    }

    /** Transforme "/prestation/{slug}" en expression régulière. */
    private function compile(string $path): string
    {
        $regex = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    /**
     * Trouve la route correspondant à la requête courante et exécute l'action.
     */
    public function dispatch(string $method, string $uri): void
    {
        // On retire la query string (?cat=...) et le slash final.
        $path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches); // on retire la correspondance complète
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...$matches);
                return;
            }
        }

        // Aucune route : page 404.
        http_response_code(404);
        (new \App\Core\Controller())->render('errors/404', ['title' => 'Page introuvable']);
    }
}
