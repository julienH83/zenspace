<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Str;
use App\Core\Validator;
use App\Repositories\ServiceRepository;

/**
 * Gestion des prestations (CRUD) par les employés et administrateurs.
 */
final class AdminServiceController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['employe', 'admin']);
        $repo = new ServiceRepository();
        $this->render('admin/services/index', [
            'title'        => 'Gérer les prestations',
            'services'     => $repo->findAll(),
            'layout_admin' => true,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole(['employe', 'admin']);
        $this->render('admin/services/form', [
            'title'        => 'Nouvelle prestation',
            'categories'   => (new ServiceRepository())->categories(),
            'layout_admin' => true,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();

        $repo = new ServiceRepository();
        $errors = $this->validate($_POST);

        if ($errors !== []) {
            $this->render('admin/services/form', [
                'title'        => 'Nouvelle prestation',
                'categories'   => $repo->categories(),
                'errors'       => $errors,
                'old'          => $_POST,
                'layout_admin' => true,
            ]);
            return;
        }

        $repo->create([
            'category_id'  => (int) $_POST['category_id'],
            'title'        => trim($_POST['title']),
            'slug'         => Str::slug($_POST['title']) . '-' . substr(uniqid(), -4),
            'description'  => trim($_POST['description']),
            'duration_min' => (int) $_POST['duration_min'],
            'price'        => (float) $_POST['price'],
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ]);

        Flash::success('Prestation créée.');
        $this->redirect('/admin/prestations');
    }

    public function editForm(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        $repo = new ServiceRepository();
        $service = $repo->findById((int) $id);
        if ($service === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Prestation introuvable']);
            return;
        }
        $this->render('admin/services/form', [
            'title'        => 'Modifier — ' . $service['title'],
            'service'      => $service,
            'categories'   => $repo->categories(),
            'layout_admin' => true,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();

        $repo = new ServiceRepository();
        $service = $repo->findById((int) $id);
        if ($service === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Prestation introuvable']);
            return;
        }

        $errors = $this->validate($_POST);
        if ($errors !== []) {
            $this->render('admin/services/form', [
                'title'        => 'Modifier — ' . $service['title'],
                'service'      => array_merge($service, $_POST),
                'categories'   => $repo->categories(),
                'errors'       => $errors,
                'layout_admin' => true,
            ]);
            return;
        }

        $repo->update((int) $id, [
            'category_id'  => (int) $_POST['category_id'],
            'title'        => trim($_POST['title']),
            'slug'         => $service['slug'], // on garde le slug existant
            'description'  => trim($_POST['description']),
            'duration_min' => (int) $_POST['duration_min'],
            'price'        => (float) $_POST['price'],
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ]);

        Flash::success('Prestation mise à jour.');
        $this->redirect('/admin/prestations');
    }

    public function delete(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();
        (new ServiceRepository())->delete((int) $id);
        AuditLog::record('service.delete', $id);
        Flash::success('Prestation désactivée.');
        $this->redirect('/admin/prestations');
    }

    /** @return string[] */
    private function validate(array $data): array
    {
        $v = new Validator($data);
        $v->required('title', 'Titre')
          ->required('description', 'Description')
          ->required('category_id', 'Catégorie')
          ->required('duration_min', 'Durée')
          ->required('price', 'Prix');
        $errors = array_values($v->errors());

        if ((int) ($data['duration_min'] ?? 0) <= 0) {
            $errors[] = 'La durée doit être un nombre de minutes positif.';
        }
        if ((float) ($data['price'] ?? -1) < 0) {
            $errors[] = 'Le prix doit être positif.';
        }
        return $errors;
    }
}
