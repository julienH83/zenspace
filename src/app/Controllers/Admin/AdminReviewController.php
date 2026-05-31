<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Repositories\ReviewRepository;

/**
 * Modération des avis : un employé/admin valide ou refuse les avis avant
 * leur publication sur le site.
 */
final class AdminReviewController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['employe', 'admin']);
        $this->render('admin/reviews/index', [
            'title'        => 'Modérer les avis',
            'reviews'      => (new ReviewRepository())->findPending(),
            'layout_admin' => true,
        ]);
    }

    public function validate(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();
        (new ReviewRepository())->validate((int) $id);
        Flash::success('Avis validé et publié.');
        $this->redirect('/admin/avis');
    }

    public function reject(string $id): void
    {
        $this->requireRole(['employe', 'admin']);
        Csrf::validate();
        (new ReviewRepository())->delete((int) $id);
        Flash::success('Avis refusé.');
        $this->redirect('/admin/avis');
    }
}
