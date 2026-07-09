<?php

declare(strict_types=1);

namespace App\Core;

/**
 * En-têtes HTTP de sécurité (défense en profondeur).
 *
 * Émis tôt dans le cycle de vie (App::boot), avant tout rendu. Protègent contre
 * le clickjacking (X-Frame-Options / frame-ancestors), le MIME-sniffing
 * (nosniff), les fuites de référent, et limitent les sources de contenu (CSP).
 */
final class SecurityHeaders
{
    /** Nonce unique par requête, à réutiliser sur les scripts inline légitimes. */
    private static ?string $nonce = null;

    public static function nonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(self), camera=(self), payment=(self)');

        // CSP — autorise explicitement les ressources réellement utilisées :
        //  - polices Google (style + gstatic)
        //  - Three.js via unpkg (module ESM + importmap)
        //  - Stripe (préparé pour le paiement, §4.4)
        // 'unsafe-inline' sur style/script reste nécessaire tant que l'importmap
        // et quelques styles inline existent ; durcissement par nonce en suivi.
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://unpkg.com https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "media-src 'self' blob:",
            "connect-src 'self' https://unpkg.com",
            "frame-src https://js.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
        ]);
        header('Content-Security-Policy: ' . $csp);

        // HSTS uniquement en HTTPS/production (inutile et risqué en local http).
        if (env('APP_ENV') === 'prod' && self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? null) === '443'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null) === 'https';
    }
}
