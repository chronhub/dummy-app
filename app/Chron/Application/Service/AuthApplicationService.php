<?php

declare(strict_types=1);

namespace App\Chron\Application\Service;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class AuthApplicationService
{
    public function createAuthUser(string $id, string $name, string $email): void
    {
        $password = Hash::make('password');

        User::create([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
