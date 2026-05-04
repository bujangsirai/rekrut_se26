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
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

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
