<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Buat data supplier pemasok barang (5 supplier realistis).
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama' => 'PT Sumber Jaya ATK',
                'telepon' => '(0291) 431201',
                'email' => 'penjualan@sumberjayaatk.co.id',
                'alamat' => 'Jl. Jenderal Sudirman No. 45, Kudus, Jawa Tengah',
            ],
            [
                'nama' => 'CV Mitra Kertas Nusantara',
                'telepon' => '(024) 8415520',
                'email' => 'order@mitrakertas.co.id',
                'alamat' => 'Kawasan Industri Candi Blok B-12, Semarang, Jawa Tengah',
            ],
            [
                'nama' => 'PT Tekno Mandiri Solusindo',
                'telepon' => '0812-2500-8899',
                'email' => 'sales@teknomandiri.id',
                'alamat' => 'Jl. Pandanaran No. 88, Semarang, Jawa Tengah',
            ],
            [
                'nama' => 'UD Berkah Perlengkapan Kantor',
                'telepon' => '(0291) 442890',
                'email' => 'info@berkahkantor.com',
                'alamat' => 'Jl. Sunan Muria No. 19, Kudus, Jawa Tengah',
            ],
            [
                'nama' => 'CV Prima Bersih Sejahtera',
                'telepon' => '0813-9011-4455',
                'email' => 'admin@primabersih.co.id',
                'alamat' => 'Jl. Raya Kaligawe KM 5, Semarang, Jawa Tengah',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['nama' => $supplier['nama']],
                [
                    'telepon' => $supplier['telepon'],
                    'email' => $supplier['email'],
                    'alamat' => $supplier['alamat'],
                ]
            );
        }
    }
}
