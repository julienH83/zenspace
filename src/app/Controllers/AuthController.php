<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Repositories\PasswordResetRepository;

/**
 * Inscription, connexion, déconnexion et réinitialisation de mot de passe.
 */
final class AuthController extends Controller
{
    private const ROLE_CLIENT = 1;

    // ----------------------------------------------------------------- Connexion
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->render('auth/login', ['title' => 'Connexion']);
    }

    public function login(): void
    {
        Csrf::validate();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $repo = new UserRepository();
        $user = $repo->findByEmail($email);

        // Message volontairement générique (ne révèle pas si l'e-mail existe).
        if ($user === null || !Auth::verifyPassword($password, $user['password_hash'])) {
            Flash::error('Identifiants incorrects.');
            $this->render('auth/login', ['title' => 'Connexion', 'email' => $email]);
            return;
        }

        if ((int) $user['is_active'] === 0) {
            Flash::error('Ce compte est désactivé.');
            $this->render('auth/login', ['title' => 'Connexion']);
            return;
        }

        Auth::login($user);
        Flash::success('Bienvenue, ' . $user['first_name'] . ' !');

        // Les employés/admins atterrissent sur leur espace de gestion.
        $this->redirect(in_array($user['role'], ['employe', 'admin'], true) ? '/admin' : '/mon-compte');
    }

    public function logout(): void
    {
        Csrf::validate();
        Auth::logout();
        $this->redirect('/');
    }

    // --------------------------------------------------------------- Inscription
    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->render('auth/register', ['title' => 'Créer un compte']);
    }

    public function register(): void
    {
        Csrf::validate();

        $v = new Validator($_POST);
        $v->required('first_name', 'Prénom')
          ->required('last_name', 'Nom')
          ->required('email', 'E-mail')->email('email')
          ->required('phone', 'Téléphone')
          ->strongPassword('password')
          ->matches('password_confirm', 'password', 'Mot de passe');

        if (empty($_POST['rgpd'])) {
            $v->required('rgpd', 'Consentement RGPD');
        }

        $repo = new UserRepository();
        if (!isset($v->errors()['email']) && $repo->emailExists(trim($_POST['email']))) {
            Flash::error('Un compte existe déjà avec cette adresse e-mail.');
            $this->render('auth/register', ['title' => 'Créer un compte', 'old' => $_POST]);
            return;
        }

        if (!$v->isValid()) {
            $this->render('auth/register', [
                'title'  => 'Créer un compte',
                'errors' => $v->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        $repo->create([
            'role_id'       => self::ROLE_CLIENT,
            'first_name'    => trim($_POST['first_name']),
            'last_name'     => trim($_POST['last_name']),
            'email'         => trim($_POST['email']),
            'password_hash' => Auth::hashPassword($_POST['password']),
            'phone'         => trim($_POST['phone']),
            'address'       => trim($_POST['address'] ?? ''),
            'rgpd_consent'  => 1,
        ]);

        // (Dans une vraie application : envoi d'un e-mail de bienvenue ici.)
        Flash::success('Votre compte a été créé. Vous pouvez vous connecter.');
        $this->redirect('/connexion');
    }

    // ---------------------------------------------------- Mot de passe oublié
    public function forgotForm(): void
    {
        $this->render('auth/forgot', ['title' => 'Mot de passe oublié']);
    }

    public function forgot(): void
    {
        Csrf::validate();
        $email = trim($_POST['email'] ?? '');

        $users  = new UserRepository();
        $resets = new PasswordResetRepository();
        $user   = $users->findByEmail($email);

        if ($user !== null) {
            // Jeton aléatoire envoyé par e-mail ; on stocke seulement son hash.
            $token = bin2hex(random_bytes(32));
            $resets->create(
                (int) $user['id'],
                hash('sha256', $token),
                (new \DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s')
            );
            // En production : envoyer le lien par e-mail. Ici, on l'affiche pour la démo.
            $link = ($_ENV['APP_URL'] ?? '') . '/reinitialiser/' . $token;
            Flash::success('Lien de réinitialisation (démo) : ' . $link);
        } else {
            // Réponse identique même si l'e-mail n'existe pas (anti-énumération).
            Flash::success('Si un compte existe, un e-mail de réinitialisation a été envoyé.');
        }

        $this->redirect('/mot-de-passe-oublie');
    }

    public function resetForm(string $token): void
    {
        $this->render('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token]);
    }

    public function reset(string $token): void
    {
        Csrf::validate();

        $v = new Validator($_POST);
        $v->strongPassword('password')->matches('password_confirm', 'password', 'Mot de passe');
        if (!$v->isValid()) {
            $this->render('auth/reset', [
                'title'  => 'Nouveau mot de passe',
                'token'  => $token,
                'errors' => $v->errors(),
            ]);
            return;
        }

        $resets = new PasswordResetRepository();
        $record = $resets->findValid(hash('sha256', $token));

        if ($record === null) {
            Flash::error('Lien invalide ou expiré.');
            $this->redirect('/mot-de-passe-oublie');
            return;
        }

        $users = new UserRepository();
        $users->updatePassword((int) $record['user_id'], Auth::hashPassword($_POST['password']));
        $resets->markUsed((int) $record['id']);

        Flash::success('Votre mot de passe a été réinitialisé.');
        $this->redirect('/connexion');
    }
}
