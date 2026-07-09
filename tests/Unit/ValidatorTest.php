<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredAndEmail(): void
    {
        $v = new Validator(['email' => 'pas-un-email', 'name' => '']);
        $v->required('name', 'Nom')->email('email');

        $this->assertFalse($v->isValid());
        $this->assertArrayHasKey('name', $v->errors());
        $this->assertArrayHasKey('email', $v->errors());
    }

    public function testStrongPasswordRejectsWeak(): void
    {
        $v = new Validator(['password' => 'azerty']);
        $v->strongPassword('password');
        $this->assertArrayHasKey('password', $v->errors());
    }

    public function testStrongPasswordAcceptsStrong(): void
    {
        $v = new Validator(['password' => 'Sereine2026!']);
        $v->strongPassword('password');
        $this->assertTrue($v->isValid());
    }

    public function testMatchesDetectsMismatch(): void
    {
        $v = new Validator(['password' => 'Sereine2026!', 'password_confirm' => 'Autre2026!']);
        $v->matches('password_confirm', 'password', 'Mot de passe');
        $this->assertArrayHasKey('password_confirm', $v->errors());
    }
}
