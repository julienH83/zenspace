<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Limiteur de débit (anti-bruteforce / anti-spam).
 *
 * Deux pilotes :
 *  - Redis (production, recommandé) : compteur atomique avec expiration native.
 *  - Fichier (par défaut, sans dépendance) : un compteur par clé, fenêtre
 *    glissante simple. Suffisant pour un projet pédagogique et un mono-serveur.
 *
 * Le pilote est choisi via RATE_LIMIT_DRIVER (redis|file). Par défaut « file »
 * pour que l'application fonctionne immédiatement, sans Redis ni extension.
 *
 * Usage typique (cf. AuthController) : limiter par IP ET par compte.
 */
final class RateLimiter
{
    private string $driver;
    private string $dir;
    private mixed $redis = null;

    public function __construct()
    {
        $this->driver = env('RATE_LIMIT_DRIVER', 'file') ?? 'file';
        $this->dir = dirname(__DIR__, 2) . '/storage/ratelimit';

        if ($this->driver === 'redis' && class_exists(\Redis::class)) {
            try {
                $this->redis = new \Redis();
                $dsn = parse_url(env('REDIS_URL', 'tcp://redis:6379') ?? 'tcp://redis:6379');
                $this->redis->connect($dsn['host'] ?? 'redis', (int) ($dsn['port'] ?? 6379), 1.0);
            } catch (\Throwable $e) {
                error_log('[RateLimiter] Redis indisponible, repli fichier : ' . $e->getMessage());
                $this->driver = 'file';
            }
        } else {
            $this->driver = 'file';
        }
    }

    /**
     * Enregistre une tentative pour la clé donnée.
     *
     * @return bool true si la tentative est autorisée, false si le quota est dépassé.
     */
    public function attempt(string $key, int $max, int $windowSec): bool
    {
        return $this->driver === 'redis'
            ? $this->attemptRedis($key, $max, $windowSec)
            : $this->attemptFile($key, $max, $windowSec);
    }

    /** Réinitialise un compteur (ex. après une connexion réussie). */
    public function clear(string $key): void
    {
        if ($this->driver === 'redis' && $this->redis) {
            $this->redis->del('rl:' . $key);
            return;
        }
        $file = $this->path($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    // ---------------------------------------------------------------- Redis
    private function attemptRedis(string $key, int $max, int $windowSec): bool
    {
        $k = 'rl:' . $key;
        $count = (int) $this->redis->incr($k);
        if ($count === 1) {
            $this->redis->expire($k, $windowSec);
        }
        return $count <= $max;
    }

    // -------------------------------------------------------------- Fichier
    private function attemptFile(string $key, int $max, int $windowSec): bool
    {
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0775, true);
        }
        $file = $this->path($key);
        $now = time();

        $hits = [];
        if (is_file($file)) {
            $hits = array_filter(
                (array) json_decode((string) @file_get_contents($file), true),
                static fn($ts) => is_int($ts) && $ts > $now - $windowSec
            );
        }
        $hits[] = $now;
        @file_put_contents($file, json_encode(array_values($hits)), LOCK_EX);

        return count($hits) <= $max;
    }

    private function path(string $key): string
    {
        return $this->dir . '/' . sha1($key) . '.json';
    }
}
