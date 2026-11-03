<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Buat peran sistem dan akun contoh untuk setiap peran.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = ['admin', 'staff', 'pimpinan'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Hanya untuk demo lokal — wajib diganti pada lingkungan nyata
        $demoUsers = [
            [
                'name' => 'Pak Admin',
                'email' => 'admin@inventory.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Mbak Gudang',
                'email' => 'staff@inventory.test',
                'role' => 'staff',
            ],
            [
                'name' => 'Bu Pimpinan',
                'email' => 'pimpinan@inventory.test',
                'role' => 'pimpinan',
            ],
        ];

        foreach ($demoUsers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'), // Hanya untuk demo lokal
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
