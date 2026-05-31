<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Validator;
use App\Repositories\ContactRepository;

/**
 * Formulaire de contact : le message est enregistré (et envoyé par e-mail en prod).
 */
final class ContactController extends Controller
{
    public function index(): void
    {
        $this->render('contact/index', ['title' => 'Contact']);
    }

    public function submit(): void
    {
        Csrf::validate();

        $v = new Validator($_POST);
        $v->required('name', 'Nom')
          ->required('email', 'E-mail')->email('email')
          ->required('subject', 'Sujet')
          ->required('message', 'Message');

        if (!$v->isValid()) {
            $this->render('contact/index', [
                'title'  => 'Contact',
                'errors' => $v->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        (new ContactRepository())->create([
            'name'    => trim($_POST['name']),
            'email'   => trim($_POST['email']),
            'subject' => trim($_POST['subject']),
            'message' => trim($_POST['message']),
        ]);

        // En production : mail() ou un service comme Resend pour notifier l'institut.
        Flash::success('Votre message a bien été envoyé. Nous vous répondrons rapidement.');
        $this->redirect('/contact');
    }
}
