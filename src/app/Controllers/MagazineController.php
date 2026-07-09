<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ArticleRepository;

/**
 * Magazine bien-être : liste des articles publiés et page de lecture.
 * Fonctionnalité publique entièrement côté serveur (aucun JS requis).
 */
final class MagazineController extends Controller
{
    /** Liste des articles publiés (grille de cartes). */
    public function index(): void
    {
        $articles = (new ArticleRepository())->findPublished();

        $this->render('magazine/index', [
            'title'    => 'Magazine bien-être',
            'articles' => $articles,
            'seo'      => [
                'title'       => 'Magazine bien-être — ' . env('APP_NAME', 'ZenSpace'),
                'description' => 'Conseils et rituels bien-être : massage, sommeil, respiration anti-stress. Le magazine de notre institut.',
            ],
        ]);
    }

    /** Page de lecture d'un article (ou 404 si introuvable / non publié). */
    public function show(string $slug): void
    {
        $article = (new ArticleRepository())->findBySlug($slug);

        if ($article === null) {
            $this->notFound('Article introuvable.');
        }

        // Description SEO : on privilégie l'extrait, sinon un repli sur le corps
        // débarrassé de tout balisage et tronqué (155 caractères ~ longueur idéale).
        $description = $article['excerpt'] !== null && $article['excerpt'] !== ''
            ? (string) $article['excerpt']
            : mb_substr(strip_tags((string) $article['body']), 0, 155);

        // Données structurées JSON-LD de type Article (rich snippets Google).
        $jsonld = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => (string) $article['title'],
            'datePublished' => date('c', strtotime((string) $article['published_at'])),
            'author'        => [
                '@type' => 'Organization',
                'name'  => env('APP_NAME', 'ZenSpace'),
            ],
        ];

        $this->render('magazine/show', [
            'title'   => $article['title'],
            'article' => $article,
            'seo'     => [
                'title'       => $article['title'] . ' — ' . env('APP_NAME', 'ZenSpace'),
                'description' => $description,
                'type'        => 'article',
                'canonical'   => rtrim((string) env('APP_URL', ''), '/') . '/magazine/' . $article['slug'],
                'jsonld'      => $jsonld,
            ],
        ]);
    }
}
