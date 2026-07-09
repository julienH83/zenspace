<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use App\Core\HttpException;
use PHPUnit\Framework\TestCase;

/** Contrôleur factice utilisé pour vérifier le dispatch. */
final class DispatchSpy
{
    public static array $calls = [];

    public function show(string $id): void
    {
        self::$calls[] = $id;
    }
}

final class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        DispatchSpy::$calls = [];
    }

    public function testMatchesRouteAndPassesParameter(): void
    {
        $router = new Router();
        $router->get('/prestation/{id:\d+}', [DispatchSpy::class, 'show']);

        $router->dispatch('GET', '/prestation/42');

        $this->assertSame(['42'], DispatchSpy::$calls);
    }

    public function testUnknownPathThrows404(): void
    {
        $router = new Router();
        $router->get('/', [DispatchSpy::class, 'show']);

        try {
            $router->dispatch('GET', '/inexistant');
            $this->fail('Une HttpException 404 était attendue.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatus());
        }
    }

    public function testWrongMethodThrows405WithAllowHeader(): void
    {
        $router = new Router();
        $router->post('/deconnexion', [DispatchSpy::class, 'show']);

        try {
            $router->dispatch('GET', '/deconnexion');
            $this->fail('Une HttpException 405 était attendue.');
        } catch (HttpException $e) {
            $this->assertSame(405, $e->getStatus());
            $this->assertSame('POST', $e->getHeaders()['Allow'] ?? null);
        }
    }

    public function testConstraintRejectsNonMatchingParameter(): void
    {
        $router = new Router();
        $router->get('/reserver/{id:\d+}', [DispatchSpy::class, 'show']);

        // "abc" ne respecte pas \d+ → aucune route → 404
        $this->expectException(HttpException::class);
        $router->dispatch('GET', '/reserver/abc');
    }
}
