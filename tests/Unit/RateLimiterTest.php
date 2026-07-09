<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    public function testAllowsUpToMaxThenBlocks(): void
    {
        $rl = new RateLimiter();           // pilote « file » en test
        $key = 'test:' . bin2hex(random_bytes(6));

        // 3 tentatives autorisées, la 4e bloquée.
        $this->assertTrue($rl->attempt($key, 3, 60));
        $this->assertTrue($rl->attempt($key, 3, 60));
        $this->assertTrue($rl->attempt($key, 3, 60));
        $this->assertFalse($rl->attempt($key, 3, 60));
    }

    public function testClearResetsCounter(): void
    {
        $rl = new RateLimiter();
        $key = 'test:' . bin2hex(random_bytes(6));

        $rl->attempt($key, 1, 60);
        $this->assertFalse($rl->attempt($key, 1, 60)); // quota dépassé
        $rl->clear($key);
        $this->assertTrue($rl->attempt($key, 1, 60));   // de nouveau autorisé
    }
}
