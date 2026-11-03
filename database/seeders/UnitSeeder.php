<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Buat data satuan barang standar.
     */
    public function run(): void
    {
        $units = [
            ['nama' => 'Pieces', 'singkatan' => 'pcs'],
            ['nama' => 'Box', 'singkatan' => 'box'],
            ['nama' => 'Pak', 'singkatan' => 'pak'],
            ['nama' => 'Rim', 'singkatan' => 'rim'],
            ['nama' => 'Kilogram', 'singkatan' => 'kg'],
            ['nama' => 'Liter', 'singkatan' => 'liter'],
            ['nama' => 'Meter', 'singkatan' => 'meter'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['singkatan' => $unit['singkatan']],
                ['nama' => $unit['nama']]
            );
        }
    }
}
