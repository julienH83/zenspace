<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Validator;
use App\Repositories\UserRepository;

/**
 * Gestion des comptes employés — RÉSERVÉ à l'administrateur.
 * Il est impossible de créer un compte administrateur depuis l'application
 * (les rôles disponibles à la création sont limités à « employe »).
 */
final class EmployeeController extends Controller
{
    private const ROLE_EMPLOYEE = 2;

    public function index(): void
    {
        $this->requireRole(['admin']);
        $this->render('admin/employees/index', [
            'title'        => 'Gérer les employés',
            'employees'    => (new UserRepository())->findEmployees(),
            'layout_admin' => true,
        ]);
    }

    public function createForm(): void
    {
        $this->requireRole(['admin']);
        $this->render('admin/employees/form', [
            'title'        => 'Nouvel employé',
            'layout_admin' => true,
        ]);
    }

    public function create(): void
    {
        $this->requireRole(['admin']);
        Csrf::validate();

        $v = new Validator($_POST);
        $v->required('first_name', 'Prénom')
          ->required('last_name', 'Nom')
          ->required('email', 'E-mail')->email('email')
          ->strongPassword('password');

        $repo = new UserRepository();
        if (!isset($v->errors()['email']) && $repo->emailExists(trim($_POST['email']))) {
            Flash::error('Cet e-mail est déjà utilisé.');
            $this->render('admin/employees/form', ['title' => 'Nouvel employé', 'old' => $_POST, 'layout_admin' => true]);
            return;
        }

        if (!$v->isValid()) {
            $this->render('admin/employees/form', [
                'title'        => 'Nouvel employé',
                'errors'       => $v->errors(),
                'old'          => $_POST,
                'layout_admin' => true,
            ]);
            return;
        }

        $repo->create([
            'role_id'       => self::ROLE_EMPLOYEE, // jamais admin depuis l'app
            'first_name'    => trim($_POST['first_name']),
            'last_name'     => trim($_POST['last_name']),
            'email'         => trim($_POST['email']),
            'password_hash' => Auth::hashPassword($_POST['password']),
            'rgpd_consent'  => 1,
        ]);

        // En production : e-mail à l'employé l'informant de la création (sans le mot de passe).
        Flash::success('Compte employé créé.');
        $this->redirect('/admin/employes');
    }

    public function deactivate(string $id): void
    {
        $this->requireRole(['admin']);
        Csrf::validate();
        (new UserRepository())->setActive((int) $id, false);
        AuditLog::record('employee.deactivate', $id);
        Flash::success('Compte employé désactivé.');
        $this->redirect('/admin/employes');
    }
}
