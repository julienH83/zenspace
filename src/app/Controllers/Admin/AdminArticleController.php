<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Str;
use App\Core\Validator;
use App\Repositories\ArticleRepository;

/**
 * Back-office du magazine bien-être : rédaction, édition, suppression et
 * publication des articles (employés + administrateurs).
 *
 * Construit en miroir d'AdminServiceController (même conventions : requireRole,
 * validation CSRF, Validator, redirection + flash), afin de garder un espace de
 * gestion homogène.
 */
final class AdminArticleController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['employe', 'admin']);
        $this->render('admin/articles/index', [
            'title'        => 'Gérer le magazine',
            'articles'     => (new ArticleRepository())->findAllForAdmin(),
            'layout_admin' => true,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole(['employe', 'admin']);
        $this->render('admin/articles/form', [
            'title'        => 'Nouvel article',
            'layout_admin' => true,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();

        $repo   = new ArticleRepository();
        $errors = $this->validate($_POST);

        if ($errors !== []) {
            $this->render('admin/articles/form', [
                'title'        => 'Nouvel article',
                'errors'       => $errors,
                'article'      => $_POST,
                'layout_admin' => true,
            ]);
            return;
        }

        $slug = $this->uniqueSlug($repo, (string) $_POST['title']);
        $user = Auth::user();

        $repo->create([
            'author_id'    => isset($user['id']) ? (int) $user['id'] : null,
            'title'        => trim((string) $_POST['title']),
            'slug'         => $slug,
            'excerpt'      => $this->nullableTrim($_POST['excerpt'] ?? ''),
            'body'         => trim((string) $_POST['body']),
            'cover_image'  => $this->nullableTrim($_POST['cover_image'] ?? ''),
            'published_at' => isset($_POST['published']) ? date('Y-m-d H:i:s') : null,
        ]);

        Flash::success('Article enregistré.');
        $this->redirect('/admin/magazine');
    }

    public function editForm(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        $article = (new ArticleRepository())->findByIdAdmin((int) $id);
        if ($article === null) {
            $this->notFound('Article introuvable.');
        }
        $this->render('admin/articles/form', [
            'title'        => 'Modifier — ' . $article['title'],
            'article'      => $article,
            'layout_admin' => true,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();

        $repo    = new ArticleRepository();
        $article = $repo->findByIdAdmin((int) $id);
        if ($article === null) {
            $this->notFound('Article introuvable.');
        }

        $errors = $this->validate($_POST);
        if ($errors !== []) {
            $this->render('admin/articles/form', [
                'title'        => 'Modifier — ' . $article['title'],
                'article'      => array_merge($article, $_POST),
                'errors'       => $errors,
                'layout_admin' => true,
            ]);
            return;
        }

        // Publication : si « publié » est coché on conserve la date d'origine
        // (ou on la fixe à maintenant pour une première publication) ; sinon on
        // repasse l'article en brouillon (published_at = NULL).
        $publishedAt = null;
        if (isset($_POST['published'])) {
            $publishedAt = $article['published_at'] ?? date('Y-m-d H:i:s');
        }

        $repo->update((int) $id, [
            'title'        => trim((string) $_POST['title']),
            'slug'         => $article['slug'], // on garde le slug existant (URL stable)
            'excerpt'      => $this->nullableTrim($_POST['excerpt'] ?? ''),
            'body'         => trim((string) $_POST['body']),
            'cover_image'  => $this->nullableTrim($_POST['cover_image'] ?? ''),
            'published_at' => $publishedAt,
        ]);

        Flash::success('Article mis à jour.');
        $this->redirect('/admin/magazine');
    }

    public function delete(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();
        (new ArticleRepository())->delete((int) $id);
        AuditLog::record('article.delete', $id);
        Flash::success('Article supprimé.');
        $this->redirect('/admin/magazine');
    }

    /** @return string[] */
    private function validate(array $data): array
    {
        $v = new Validator($data);
        $v->required('title', 'Titre')
          ->required('body', 'Contenu');
        return array_values($v->errors());
    }

    /** Génère un slug unique à partir du titre (suffixe court en cas de collision). */
    private function uniqueSlug(ArticleRepository $repo, string $title): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : 'article';
        if ($repo->slugExists($slug)) {
            $slug .= '-' . substr(uniqid(), -4);
        }
        return $slug;
    }

    /** Chaîne vide -> null (colonnes optionnelles), sinon valeur nettoyée. */
    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }
}
