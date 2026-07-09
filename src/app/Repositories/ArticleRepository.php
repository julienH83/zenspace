<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Accès aux données du magazine bien-être (table `article`).
 *
 * Hérite de BaseRepository : la connexion PDO et les helpers de requêtes
 * préparées (fetchRows, fetchRow) sont fournis par la classe abstraite.
 */
final class ArticleRepository extends BaseRepository
{
    /**
     * Articles publiés : date de publication renseignée ET déjà passée
     * (les articles programmés dans le futur restent invisibles).
     * Triés du plus récent au plus ancien.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findPublished(): array
    {
        return $this->fetchRows(
            'SELECT id, author_id, title, slug, excerpt, body, cover_image, published_at, created_at
             FROM article
             WHERE published_at IS NOT NULL AND published_at <= NOW()
             ORDER BY published_at DESC'
        );
    }

    /**
     * Récupère un article publié par son slug (ou null si absent / non publié).
     * On filtre aussi sur la publication ici : un brouillon n'est pas accessible
     * via son URL publique.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->fetchRow(
            'SELECT id, author_id, title, slug, excerpt, body, cover_image, published_at, created_at
             FROM article
             WHERE slug = :slug AND published_at IS NOT NULL AND published_at <= NOW()
             LIMIT 1',
            ['slug' => $slug]
        );
    }
}
