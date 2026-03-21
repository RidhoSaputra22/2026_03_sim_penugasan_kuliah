<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $users = [
            [
                'nim' => '222140',
                'name' => 'Ridho Saputra',
                'email' => 'ridho@gmail.com',
            ],
            [
                'nim' => '222141',
                'name' => 'Siti Rahmawati',
                'email' => 'siti.rahmawati@gmail.com',
            ],
            [
                'nim' => '222142',
                'name' => 'Fajar Pratama',
                'email' => 'fajar.pratama@gmail.com',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['nim' => $user['nim']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                    'remember_token' => Str::random(10),
                ]
            );
        }
    }
}
