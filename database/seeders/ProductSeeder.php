<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Buat 32 data barang realistis dengan stok awal 0 (BR-13).
     * Riwayat stok masuk dan keluar diisi melalui StockService pada Fase 3.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'nama');
        $units = Unit::pluck('id', 'singkatan');

        $atk = $categories['Alat Tulis Kantor (ATK)'];
        $kertas = $categories['Kertas & Dokumen'];
        $elektronik = $categories['Elektronik Kecil'];
        $printer = $categories['Perlengkapan Printer'];
        $kantor = $categories['Perlengkapan Kantor'];
        $kebersihan = $categories['Kebersihan & Pantry'];

        $items = [
            // Kertas & Dokumen
            ['kode' => 'BRG-0001', 'nama' => 'Kertas HVS A4 80gsm PaperOne', 'cat' => $kertas, 'unit' => $units['rim'], 'harga' => 54000, 'min' => 20, 'rak' => 'A-01', 'desc' => 'Kertas HVS ukuran A4 80 gram isi 500 lembar.'],
            ['kode' => 'BRG-0002', 'nama' => 'Kertas HVS F4 75gsm Sinar Dunia', 'cat' => $kertas, 'unit' => $units['rim'], 'harga' => 52000, 'min' => 15, 'rak' => 'A-02', 'desc' => 'Kertas HVS ukuran Folio/F4 75 gram isi 500 lembar.'],
            ['kode' => 'BRG-0003', 'nama' => 'Amplop Putih Panjang Paperline 90 PPS', 'cat' => $kertas, 'unit' => $units['box'], 'harga' => 24000, 'min' => 10, 'rak' => 'A-03', 'desc' => 'Amplop putih kabinet berperekat isi 100 lembar per box.'],
            ['kode' => 'BRG-0004', 'nama' => 'Amplop Coklat Folio Tali', 'cat' => $kertas, 'unit' => $units['pak'], 'harga' => 35000, 'min' => 8, 'rak' => 'A-04', 'desc' => 'Amplop coklat ukuran Folio untuk dokumen resmi.'],
            ['kode' => 'BRG-0005', 'nama' => 'Ordner Bantex Folio 7cm Biru', 'cat' => $kertas, 'unit' => $units['pcs'], 'harga' => 32000, 'min' => 15, 'rak' => 'A-05', 'desc' => 'Map ordner karton tebal ukuran Folio dengan mekanik pengunci.'],
            ['kode' => 'BRG-0006', 'nama' => 'Map Plastik L Clear Sleeves A4', 'cat' => $kertas, 'unit' => $units['pak'], 'harga' => 22000, 'min' => 12, 'rak' => 'A-06', 'desc' => 'Map plastik transparan model L ukuran A4 isi 12 pcs.'],
            ['kode' => 'BRG-0007', 'nama' => 'Buku Agenda Kerja Hardcover A5', 'cat' => $kertas, 'unit' => $units['pcs'], 'harga' => 28000, 'min' => 10, 'rak' => 'A-07', 'desc' => 'Buku catatan rapat ukuran A5 sampul tebal.'],
            ['kode' => 'BRG-0008', 'nama' => 'Sticky Notes Post-it 3x3 Kuning', 'cat' => $kertas, 'unit' => $units['pak'], 'harga' => 14000, 'min' => 15, 'rak' => 'A-08', 'desc' => 'Kertas catatan tempel ukuran 76x76 mm isi 100 lembar.'],

            // Alat Tulis Kantor (ATK)
            ['kode' => 'BRG-0009', 'nama' => 'Pulpen Standard AE7 Hitam 0.5', 'cat' => $atk, 'unit' => $units['box'], 'harga' => 26000, 'min' => 12, 'rak' => 'B-01', 'desc' => 'Pulpen tinta hitam 0.5 mm isi 12 batang per box.'],
            ['kode' => 'BRG-0010', 'nama' => 'Pulpen Gel Joyko GP-265 Biru', 'cat' => $atk, 'unit' => $units['box'], 'harga' => 30000, 'min' => 10, 'rak' => 'B-02', 'desc' => 'Pulpen gel tinta biru untuk tanda tangan dokumen.'],
            ['kode' => 'BRG-0011', 'nama' => 'Spidol Whiteboard Snowman BG-12 Hitam', 'cat' => $atk, 'unit' => $units['box'], 'harga' => 85000, 'min' => 6, 'rak' => 'B-03', 'desc' => 'Spidol papan tulis dapat dihapus isi 12 pcs per box.'],
            ['kode' => 'BRG-0012', 'nama' => 'Spidol Permanent Snowman G-12 Hitam', 'cat' => $atk, 'unit' => $units['box'], 'harga' => 78000, 'min' => 5, 'rak' => 'B-04', 'desc' => 'Spidol permanen untuk pelabelan kardus dan logistik.'],
            ['kode' => 'BRG-0013', 'nama' => 'Pensil 2B Faber-Castell', 'cat' => $atk, 'unit' => $units['box'], 'harga' => 42000, 'min' => 6, 'rak' => 'B-05', 'desc' => 'Pensil kayu 2B isi 12 batang per box.'],
            ['kode' => 'BRG-0014', 'nama' => 'Correction Tape Joyko CT-522', 'cat' => $atk, 'unit' => $units['pcs'], 'harga' => 8500, 'min' => 20, 'rak' => 'B-06', 'desc' => 'Pita koreksi kertas panjang 12 meter lebar 5 mm.'],
            ['kode' => 'BRG-0015', 'nama' => 'Stabilo Boss Highlighter Kuning', 'cat' => $atk, 'unit' => $units['pcs'], 'harga' => 11000, 'min' => 15, 'rak' => 'B-07', 'desc' => 'Penanda teks warna kuning terang.'],

            // Perlengkapan Printer
            ['kode' => 'BRG-0016', 'nama' => 'Tinta Epson 003 Original Black', 'cat' => $printer, 'unit' => $units['pcs'], 'harga' => 95000, 'min' => 8, 'rak' => 'C-01', 'desc' => 'Tinta botol original Epson L3110/L3210 warna hitam 65 ml.'],
            ['kode' => 'BRG-0017', 'nama' => 'Tinta Epson 003 Original Cyan', 'cat' => $printer, 'unit' => $units['pcs'], 'harga' => 95000, 'min' => 5, 'rak' => 'C-02', 'desc' => 'Tinta botol original Epson L3110/L3210 warna biru 65 ml.'],
            ['kode' => 'BRG-0018', 'nama' => 'Tinta Epson 003 Original Magenta', 'cat' => $printer, 'unit' => $units['pcs'], 'harga' => 95000, 'min' => 5, 'rak' => 'C-03', 'desc' => 'Tinta botol original Epson L3110/L3210 warna merah 65 ml.'],
            ['kode' => 'BRG-0019', 'nama' => 'Tinta Epson 003 Original Yellow', 'cat' => $printer, 'unit' => $units['pcs'], 'harga' => 95000, 'min' => 5, 'rak' => 'C-04', 'desc' => 'Tinta botol original Epson L3110/L3210 warna kuning 65 ml.'],
            ['kode' => 'BRG-0020', 'nama' => 'Toner Cartridge HP 85A Black', 'cat' => $printer, 'unit' => $units['pcs'], 'harga' => 285000, 'min' => 4, 'rak' => 'C-05', 'desc' => 'Cartridge toner hitam untuk printer laser HP LaserJet Pro.'],

            // Elektronik Kecil
            ['kode' => 'BRG-0021', 'nama' => 'Mouse Wireless Logitech B170', 'cat' => $elektronik, 'unit' => $units['pcs'], 'harga' => 135000, 'min' => 5, 'rak' => 'D-01', 'desc' => 'Mouse nirkabel 2.4 GHz dengan receiver USB nano.'],
            ['kode' => 'BRG-0022', 'nama' => 'Flashdisk SanDisk Cruzer Blade 32GB', 'cat' => $elektronik, 'unit' => $units['pcs'], 'harga' => 65000, 'min' => 8, 'rak' => 'D-02', 'desc' => 'Media penyimpanan USB kapasitas 32 GB.'],
            ['kode' => 'BRG-0023', 'nama' => 'Kalkulator Meja Citizen SDC-868L', 'cat' => $elektronik, 'unit' => $units['pcs'], 'harga' => 145000, 'min' => 4, 'rak' => 'D-03', 'desc' => 'Kalkulator 12 digit layar lebar untuk bagian administrasi.'],
            ['kode' => 'BRG-0024', 'nama' => 'Baterai Alkaline AA ABC Isi 6', 'cat' => $elektronik, 'unit' => $units['pak'], 'harga' => 23000, 'min' => 10, 'rak' => 'D-04', 'desc' => 'Baterai AA untuk mouse wireless dan jam dinding kantor.'],
            ['kode' => 'BRG-0025', 'nama' => 'Stop Kontak Kabel Uticon 4 Lubang 3M', 'cat' => $elektronik, 'unit' => $units['pcs'], 'harga' => 68000, 'min' => 5, 'rak' => 'D-05', 'desc' => 'Kabel ekstensi listrik 4 soket dengan sakelar utama.'],

            // Perlengkapan Kantor
            ['kode' => 'BRG-0026', 'nama' => 'Stapler Max HD-10', 'cat' => $kantor, 'unit' => $units['pcs'], 'harga' => 21000, 'min' => 10, 'rak' => 'E-01', 'desc' => 'Stapler ukuran kecil standar kantor.'],
            ['kode' => 'BRG-0027', 'nama' => 'Isi Staples Max No. 10-1M', 'cat' => $kantor, 'unit' => $units['box'], 'harga' => 45000, 'min' => 10, 'rak' => 'E-02', 'desc' => 'Isi staples ukuran No. 10 kemasan pak 20 kotak kecil.'],
            ['kode' => 'BRG-0028', 'nama' => 'Lakban Coklat Daimaru 2 Inch 90 Yard', 'cat' => $kantor, 'unit' => $units['pcs'], 'harga' => 13500, 'min' => 24, 'rak' => 'E-03', 'desc' => 'Lakban pengemasan kardus lebar 48 mm.'],
            ['kode' => 'BRG-0029', 'nama' => 'Gunting Kertas Joyko SC-838', 'cat' => $kantor, 'unit' => $units['pcs'], 'harga' => 12000, 'min' => 8, 'rak' => 'E-04', 'desc' => 'Gunting baja tahan karat ukuran sedang.'],
            ['kode' => 'BRG-0030', 'nama' => 'Binder Clip Joyko No. 107 (19mm)', 'cat' => $kantor, 'unit' => $units['box'], 'harga' => 6500, 'min' => 15, 'rak' => 'E-05', 'desc' => 'Penjepit kertas hitam lebar 19 mm isi 12 pcs per box.'],

            // Kebersihan & Pantry
            ['kode' => 'BRG-0031', 'nama' => 'Tisu Wajah Paseo Smart 250 Sheets', 'cat' => $kebersihan, 'unit' => $units['pak'], 'harga' => 13000, 'min' => 20, 'rak' => 'F-01', 'desc' => 'Tisu wajah 2 ply untuk ruang kerja dan ruang tamu.'],
            ['kode' => 'BRG-0032', 'nama' => 'Cairan Pembersih Lantai So Klin 4 Liter', 'cat' => $kebersihan, 'unit' => $units['liter'], 'harga' => 58000, 'min' => 8, 'rak' => 'F-02', 'desc' => 'Pembersih lantai aroma pinus kemasan jeriken.'],
        ];

        foreach ($items as $item) {
            Product::withTrashed()->firstOrCreate(
                ['kode_barang' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'category_id' => $item['cat'],
                    'unit_id' => $item['unit'],
                    'harga_beli' => $item['harga'],
                    'stok' => 0, // BR-13: stok awal selalu 0
                    'stok_minimum' => $item['min'],
                    'lokasi_rak' => $item['rak'],
                    'deskripsi' => $item['desc'],
                    'deleted_at' => null,
                ]
            );
        }
    }
}
