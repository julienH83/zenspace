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

    /**
     * Les 3 derniers articles publiés (teaser page d'accueil).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findLatestPublished(int $limit = 3): array
    {
        $limit = max(1, min(12, $limit)); // borné puis interpolé en entier (jamais de saisie utilisateur)
        return $this->fetchRows(
            'SELECT id, title, slug, excerpt, cover_image, published_at
             FROM article
             WHERE published_at IS NOT NULL AND published_at <= NOW()
             ORDER BY published_at DESC
             LIMIT ' . $limit
        );
    }

    // -----------------------------------------------------------------
    //  Back-office (employé/admin) : accès sans filtre de publication,
    //  pour gérer aussi les brouillons.
    // -----------------------------------------------------------------

    /**
     * Tous les articles (publiés ET brouillons), pour la liste de gestion.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAllForAdmin(): array
    {
        return $this->fetchRows(
            'SELECT id, title, slug, excerpt, published_at, created_at
             FROM article
             ORDER BY COALESCE(published_at, created_at) DESC'
        );
    }

    /** Un article par son id, sans filtre de publication (pour l'édition). */
    public function findByIdAdmin(int $id): ?array
    {
        return $this->fetchRow(
            'SELECT id, author_id, title, slug, excerpt, body, cover_image, published_at, created_at
             FROM article WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /** Vrai si un slug est déjà utilisé (par un autre article que $exceptId). */
    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return $this->count(
            'SELECT COUNT(*) FROM article WHERE slug = :slug AND id <> :id',
            ['slug' => $slug, 'id' => $exceptId]
        ) > 0;
    }

    /**
     * Crée un article (requête préparée). Renvoie l'id inséré.
     *
     * @param array{author_id:?int,title:string,slug:string,excerpt:?string,body:string,cover_image:?string,published_at:?string} $d
     */
    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO article (author_id, title, slug, excerpt, body, cover_image, published_at)
             VALUES (:author_id, :title, :slug, :excerpt, :body, :cover_image, :published_at)'
        );
        $stmt->execute([
            'author_id'    => $d['author_id'],
            'title'        => $d['title'],
            'slug'         => $d['slug'],
            'excerpt'      => $d['excerpt'],
            'body'         => $d['body'],
            'cover_image'  => $d['cover_image'],
            'published_at' => $d['published_at'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un article existant (requête préparée).
     *
     * @param array{title:string,slug:string,excerpt:?string,body:string,cover_image:?string,published_at:?string} $d
     */
    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE article
             SET title = :title, slug = :slug, excerpt = :excerpt, body = :body,
                 cover_image = COALESCE(:cover_image, cover_image), published_at = :published_at
             WHERE id = :id'
        );
        $stmt->execute([
            'id'           => $id,
            'title'        => $d['title'],
            'slug'         => $d['slug'],
            'excerpt'      => $d['excerpt'],
            'body'         => $d['body'],
            'cover_image'  => $d['cover_image'],
            'published_at' => $d['published_at'],
        ]);
    }

    /** Supprime définitivement un article. */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM article WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
