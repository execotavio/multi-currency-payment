<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_is_finance_returns_true_for_finance_role(): void
    {
        $user = new User(['role' => 'finance']);

        $this->assertTrue($user->isFinance());
        $this->assertFalse($user->isEmployee());
    }

    public function test_is_employee_returns_true_for_employee_role(): void
    {
        $user = new User(['role' => 'employee']);

        $this->assertTrue($user->isEmployee());
        $this->assertFalse($user->isFinance());
    }
}
