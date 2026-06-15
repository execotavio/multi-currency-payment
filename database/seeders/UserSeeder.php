<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, email: string, role: string, country: string, currency: string}>
     */
    private const USERS = [
        [
            'name' => 'Employee Brazil',
            'email' => 'employee.br@example.com',
            'role' => 'employee',
            'country' => 'BR',
            'currency' => 'BRL',
        ],
        [
            'name' => 'Employee United States',
            'email' => 'employee.us@example.com',
            'role' => 'employee',
            'country' => 'US',
            'currency' => 'USD',
        ],
        [
            'name' => 'Employee United Kingdom',
            'email' => 'employee.gb@example.com',
            'role' => 'employee',
            'country' => 'GB',
            'currency' => 'GBP',
        ],
        [
            'name' => 'Employee Japan',
            'email' => 'employee.jp@example.com',
            'role' => 'employee',
            'country' => 'JP',
            'currency' => 'JPY',
        ],
        [
            'name' => 'Employee Canada',
            'email' => 'employee.ca@example.com',
            'role' => 'employee',
            'country' => 'CA',
            'currency' => 'CAD',
        ],
        [
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'role' => 'finance',
            'country' => 'US',
            'currency' => 'USD',
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password123'),
                    'role' => $user['role'],
                    'country' => $user['country'],
                    'currency' => $user['currency'],
                ],
            );
        }
    }
}
