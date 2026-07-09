<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Conteneur d'injection de dépendances minimaliste.
 *
 * Permet d'enregistrer des fabriques (closures) et de résoudre des services,
 * en singleton (bind partagé) ou à chaque appel (factory). Découple les classes
 * de l'obtention de leurs dépendances (PDO, Redis, Mailer…) et facilite les tests
 * (on peut substituer une implémentation par un double de test).
 *
 * Usage :
 *   $c = new Container();
 *   $c->singleton(RateLimiter::class, fn($c) => new RateLimiter());
 *   $rl = $c->get(RateLimiter::class);
 */
final class Container
{
    /** @var array<string, callable> */
    private array $factories = [];
    /** @var array<string, callable> */
    private array $singletons = [];
    /** @var array<string, mixed> */
    private array $instances = [];

    /** Enregistre une fabrique appelée à CHAQUE résolution. */
    public function bind(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /** Enregistre une fabrique dont le résultat est mis en cache (singleton). */
    public function singleton(string $id, callable $factory): void
    {
        $this->singletons[$id] = $factory;
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->singletons[$id]) || isset($this->instances[$id]);
    }

    /** Résout un service par son identifiant (souvent un nom de classe). */
    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (isset($this->singletons[$id])) {
            return $this->instances[$id] = ($this->singletons[$id])($this);
        }
        if (isset($this->factories[$id])) {
            return ($this->factories[$id])($this);
        }
        // Repli : instanciation directe si la classe existe et n'a pas de dépendance requise.
        if (class_exists($id)) {
            return new $id();
        }
        throw new \RuntimeException("Service non enregistré dans le conteneur : {$id}");
    }
}
