<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan seluruh seeder aplikasi.
     */
    public function run(): void
    {
        $this->call([
            RoleAndUserSeeder::class,
            UnitSeeder::class,
        ]);
    }
}
