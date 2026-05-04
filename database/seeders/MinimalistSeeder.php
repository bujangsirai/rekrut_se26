<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MinimalistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('123');
        $users = [
            [
                'nip' => '340063164',
                'nip_baru' => '200206252024121002',
                'username' => 'fatihwisesa',
                'nama' => 'Fatih Mahawisesa S.Tr.Stat',
                'email_bps' => 'fatihwisesa@bps.go.id',
                'email_gmail' => 'fatihmahawisesa@gmail.com',
                'status_pegawai' => 'aktif',
                'golongan' => 'III/a',
                'jabatan' => 'Pranata Komputer Ahli Pertama BPS Kabupaten/Kota',
                'url_foto' => 'img/avatar/fatihwisesa.jpg',
                'password' => $defaultPassword,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '999999999',
                'nip_baru' => '999999999',
                'username' => 'dummy',
                'nama' => 'Akun Dummy',
                'email_bps' => 'dummy@bps.go.id',
                'email_gmail' => 'dummy@gmail.com',
                'status_pegawai' => 'aktif',
                'golongan' => 'III/d',
                'jabatan' => 'Pranata Komputer Ahli Utama BPS Kabupaten/Kota',
                'url_foto' => 'img/avatar/default.png',
                'password' => $defaultPassword,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // USER
        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']], // Unik berdasarkan username
                $user
            );
        }

        // ROLE
        Role::create(['name' => 'Superadmin', 'description' => 'Memiliki semua akses dan kontrol penuh terhadap sistem.']);

        // ROLE-USER
        $user1 = User::where('username', 'fatihwisesa')->first();
        $user1->assignRole(['Superadmin']);
    }
}
