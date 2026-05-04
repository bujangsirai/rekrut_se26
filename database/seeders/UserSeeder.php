<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Akses penuh untuk manajemen data dan pengaturan sistem.',
            ],
            [
                'name' => 'operator',
                'description' => 'Akses operasional untuk input dan pembaruan data.',
            ],
            [
                'name' => 'viewer',
                'description' => 'Akses baca saja untuk melihat data dan laporan.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'name' => $role['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $role['description'],
                ]
            );
        }

        $users = [
            [
                'username' => 'ahzan',
                'password' => '1234',
                'role' => 'admin',
            ],
            [
                'username' => 'fatih',
                'password' => '1234',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'password' => Hash::make($data['password']),
                ]
            );

            $user->assignRole($data['role']);
        }
    }
}
