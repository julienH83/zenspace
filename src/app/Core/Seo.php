<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Construit les balises SEO d'une page : <title>, meta description, Open Graph,
 * Twitter Card, canonical, et données structurées JSON-LD.
 *
 * Chaque contrôleur passe un tableau $seo à la vue ; le layout appelle
 * Seo::tags($seo). Des valeurs par défaut couvrent les pages qui n'en passent pas.
 */
final class Seo
{
    /**
     * @param array{title?:string,description?:string,image?:string,canonical?:string,type?:string,jsonld?:array} $seo
     */
    public static function tags(array $seo = []): string
    {
        $appName  = env('APP_NAME', 'ZenSpace');
        $appUrl   = rtrim(env('APP_URL', '') ?? '', '/');
        $title    = $seo['title']       ?? $appName;
        $desc     = $seo['description'] ?? 'Institut de bien-être : massages, soins du visage, spa. Réservez en ligne à Bordeaux.';
        $image    = $seo['image']       ?? ($appUrl . '/assets/images/hero-spa.jpg');
        $canon    = $seo['canonical']   ?? ($appUrl . ($_SERVER['REQUEST_URI'] ?? '/'));
        $type     = $seo['type']        ?? 'website';
        $e        = static fn(?string $v): string => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');

        $out  = "<title>{$e($title)}</title>\n";
        $out .= "    <meta name=\"description\" content=\"{$e($desc)}\">\n";
        $out .= "    <link rel=\"canonical\" href=\"{$e($canon)}\">\n";
        $out .= "    <meta property=\"og:site_name\" content=\"{$e($appName)}\">\n";
        $out .= "    <meta property=\"og:title\" content=\"{$e($title)}\">\n";
        $out .= "    <meta property=\"og:description\" content=\"{$e($desc)}\">\n";
        $out .= "    <meta property=\"og:type\" content=\"{$e($type)}\">\n";
        $out .= "    <meta property=\"og:url\" content=\"{$e($canon)}\">\n";
        $out .= "    <meta property=\"og:image\" content=\"{$e($image)}\">\n";
        $out .= "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
        $out .= "    <meta name=\"twitter:title\" content=\"{$e($title)}\">\n";
        $out .= "    <meta name=\"twitter:description\" content=\"{$e($desc)}\">\n";
        $out .= "    <meta name=\"twitter:image\" content=\"{$e($image)}\">";

        if (!empty($seo['jsonld']) && is_array($seo['jsonld'])) {
            // Plusieurs blocs JSON-LD possibles (Service + BreadcrumbList…).
            $blocks = isset($seo['jsonld'][0]) ? $seo['jsonld'] : [$seo['jsonld']];
            foreach ($blocks as $block) {
                $json = json_encode(self::clean($block), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $out .= "\n    <script type=\"application/ld+json\">{$json}</script>";
            }
        }

        return $out;
    }

    /** Construit un bloc JSON-LD Organization (page d'accueil). */
    public static function organization(): array
    {
        $appUrl = rtrim(env('APP_URL', '') ?? '', '/');
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'HealthAndBeautyBusiness',
            'name'     => env('APP_NAME', 'ZenSpace'),
            'url'      => $appUrl,
            'image'    => $appUrl . '/assets/images/hero-spa.jpg',
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => '1 rue du Spa',
                'postalCode'      => '33000',
                'addressLocality' => 'Bordeaux',
                'addressCountry'  => 'FR',
            ],
            'openingHours' => ['Mo-Fr 09:00-18:00', 'Sa 09:00-13:00'],
        ];
    }

    /** Construit un bloc JSON-LD Service pour une fiche prestation. */
    public static function service(array $service, ?float $ratingAvg = null, int $ratingCount = 0): array
    {
        $block = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'name'        => $service['title'],
            'description' => mb_substr(strip_tags((string) $service['description']), 0, 300),
            'provider'    => ['@type' => 'HealthAndBeautyBusiness', 'name' => env('APP_NAME', 'ZenSpace')],
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => number_format((float) $service['price'], 2, '.', ''),
                'priceCurrency' => 'EUR',
            ],
        ];
        if ($ratingAvg !== null && $ratingCount > 0) {
            $block['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round($ratingAvg, 1),
                'reviewCount' => $ratingCount,
            ];
        }
        return $block;
    }

    /** Retire récursivement les valeurs nulles d'un tableau JSON-LD. */
    private static function clean(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = self::clean($v);
            }
            if ($data[$k] === null || $data[$k] === '') {
                unset($data[$k]);
            }
        }
        return $data;
    }
}
