<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Buat data kategori barang (6 kategori realistis).
     */
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'Alat Tulis Kantor (ATK)',
                'deskripsi' => 'Pulpen, pensil, spidol, penghapus, tipe-x, dan perlengkapan tulis harian.',
            ],
            [
                'nama' => 'Kertas & Dokumen',
                'deskripsi' => 'Kertas HVS, amplop, buku catatan, map, ordner, dan perlengkapan arsip.',
            ],
            [
                'nama' => 'Elektronik Kecil',
                'deskripsi' => 'Mouse, keyboard, flashdisk, kalkulator, baterai, dan kabel ekstensi.',
            ],
            [
                'nama' => 'Perlengkapan Printer',
                'deskripsi' => 'Tinta botol, cartridge, dan pita printer kantor.',
            ],
            [
                'nama' => 'Perlengkapan Kantor',
                'deskripsi' => 'Stapler, isi staples, gunting, cutter, lakban, dan baki surat.',
            ],
            [
                'nama' => 'Kebersihan & Pantry',
                'deskripsi' => 'Tisu, cairan pembersih, sabun cuci tangan, dan kantong sampah.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['nama' => $category['nama']],
                ['deskripsi' => $category['deskripsi']]
            );
        }
    }
}
