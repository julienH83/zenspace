<?php

declare(strict_types=1);

/**
 * Front controller : point d'entrée UNIQUE de l'application.
 * Toutes les URLs passent par ici (voir public/.htaccess).
 */

require dirname(__DIR__) . '/app/Core/App.php';

use App\Core\App;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\ServiceController;
use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\ReviewController;
use App\Controllers\ContactController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AdminServiceController;
use App\Controllers\Admin\AdminBookingController;
use App\Controllers\Admin\AdminReviewController;
use App\Controllers\Admin\EmployeeController;
use App\Controllers\Admin\StatsController;

App::boot();

$router = new Router();

// --- Pages publiques ---
$router->get('/', [HomeController::class, 'index']);
$router->get('/prestations', [ServiceController::class, 'index']);
$router->get('/api/prestations', [ServiceController::class, 'apiList']); // filtres dynamiques (JSON)
$router->get('/prestation/{slug}', [ServiceController::class, 'show']);
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/contact', [ContactController::class, 'submit']);

// --- Authentification ---
$router->get('/connexion', [AuthController::class, 'loginForm']);
$router->post('/connexion', [AuthController::class, 'login']);
$router->get('/inscription', [AuthController::class, 'registerForm']);
$router->post('/inscription', [AuthController::class, 'register']);
$router->post('/deconnexion', [AuthController::class, 'logout']);
$router->get('/mot-de-passe-oublie', [AuthController::class, 'forgotForm']);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'forgot']);
$router->get('/reinitialiser/{token}', [AuthController::class, 'resetForm']);
$router->post('/reinitialiser/{token}', [AuthController::class, 'reset']);

// --- Espace client : réservations & avis ---
$router->get('/reserver/{id}', [BookingController::class, 'form']);
$router->post('/reserver/{id}', [BookingController::class, 'create']);
$router->get('/mon-compte', [BookingController::class, 'myBookings']);
$router->get('/reservation/{id}', [BookingController::class, 'show']);
$router->post('/reservation/{id}/annuler', [BookingController::class, 'cancel']);
$router->post('/avis', [ReviewController::class, 'create']);

// --- Espace employé / administrateur ---
$router->get('/admin', [DashboardController::class, 'index']);
$router->get('/admin/prestations', [AdminServiceController::class, 'index']);
$router->get('/admin/prestations/nouvelle', [AdminServiceController::class, 'createForm']);
$router->post('/admin/prestations/nouvelle', [AdminServiceController::class, 'create']);
$router->get('/admin/prestations/{id}/editer', [AdminServiceController::class, 'editForm']);
$router->post('/admin/prestations/{id}/editer', [AdminServiceController::class, 'update']);
$router->post('/admin/prestations/{id}/supprimer', [AdminServiceController::class, 'delete']);
$router->get('/admin/reservations', [AdminBookingController::class, 'index']);
$router->post('/admin/reservations/{id}/statut', [AdminBookingController::class, 'updateStatus']);
$router->get('/admin/avis', [AdminReviewController::class, 'index']);
$router->post('/admin/avis/{id}/valider', [AdminReviewController::class, 'validate']);
$router->post('/admin/avis/{id}/refuser', [AdminReviewController::class, 'reject']);

// --- Réservé à l'administrateur ---
$router->get('/admin/employes', [EmployeeController::class, 'index']);
$router->get('/admin/employes/nouveau', [EmployeeController::class, 'createForm']);
$router->post('/admin/employes/nouveau', [EmployeeController::class, 'create']);
$router->post('/admin/employes/{id}/desactiver', [EmployeeController::class, 'deactivate']);
$router->get('/admin/statistiques', [StatsController::class, 'index']); // base NoSQL

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
