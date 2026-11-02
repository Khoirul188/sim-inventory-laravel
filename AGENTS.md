# AGENTS.md — Aturan Kerja Agen untuk SIM Inventory

Dokumen ini adalah aturan tetap untuk semua agen yang bekerja di repositori ini. **Baca seluruhnya sebelum menulis kode.** Tujuannya satu: hasil sesuai keinginan pemilik proyek dengan **revisi seminimal mungkin**.

## 0. Urutan membaca dan prioritas dokumen

| Urutan | File | Untuk apa | Jika bertentangan |
|---|---|---|---|
| 1 | `AGENTS.md` (ini) | Cara kerja, aturan kode, batasan | Menang atas semuanya soal *cara kerja* |
| 2 | `docs/PRD.md` | Fitur, alur bisnis, database, aturan bisnis | Menang soal *fungsi dan data* |
| 3 | `docs/DESIGN.md` | Tampilan, warna, komponen, tata letak | Menang soal *visual* (juga atas PRD §14) |

Jika dua dokumen bertentangan dan tidak bisa diselesaikan dengan tabel di atas, **berhenti dan tanyakan**. Jangan menebak.

## 1. Konteks proyek

- **Produk:** Sistem informasi inventory stok barang (satu gudang). Peran: `admin`, `staff`, `pimpinan`.
- **Pemilik:** mahasiswa Sistem Informasi, ini tugas kuliah. Kode harus **mudah dibaca dan dijelaskan**, bukan paling canggih.
- **Kondisi repositori:** folder project bernama `pw_project`, skeleton Laravel **baru dan belum diubah kodenya**. Kondisi rinci dan analisis kesenjangan ada di PRD §4. Pemilik mungkin sudah menjalankan persiapan di PRD Lampiran A (database, Breeze, Spatie, paket). **Jangan berasumsi, periksa dulu (§2)** dan pakai apa yang sudah terpasang.
- **Lingkungan:** Windows 11, XAMPP (Apache + MySQL), phpMyAdmin, VS Code/Antigravity, terminal **Command Prompt (cmd)**. Semua perintah yang Anda tuliskan atau jalankan harus berjalan di cmd Windows (bukan bash: tanpa `export`, jalur memakai `\`). Jika terminal bawaan IDE adalah PowerShell dan `npm` ditolak karena kebijakan skrip, minta pemilik mengganti profil terminal ke Command Prompt (PRD Lampiran A.1). Jangan mengubah kebijakan eksekusi sistem.
- **Bahasa:** komunikasi dengan pemilik proyek, teks antarmuka, pesan galat, dan komentar kode memakai **bahasa Indonesia**. Nama kelas, method, variabel, dan file memakai **bahasa Inggris**. Nama tabel dan kolom database **persis seperti di PRD §12** (campuran, misalnya `products.kode_barang`).

## 2. Tindakan pertama (preflight) — wajib sebelum Fase 1

Jalankan dan laporkan dalam maksimal 8 baris:

```bat
php -v
composer -V
node -v
npm -v
php artisan --version
git status
```
Lalu periksa dan laporkan: apakah `laravel/breeze`, `spatie/laravel-permission`, `barryvdh/laravel-dompdf`, `maatwebsite/excel` sudah ada di `composer.json`; apakah `.env` sudah mengarah ke database MySQL `inventory_stok` (bawaan skeleton adalah `DB_CONNECTION=sqlite`); apakah `node_modules` dan `tailwind.config.js` sudah ada (tanda Breeze terpasang); apakah ada migration/model/controller buatan sendiri; apakah tabel `sessions` sudah ada di MySQL (`php artisan migrate:status`).

Aturan hasil preflight:
- PHP lebih rendah dari syarat versi Laravel yang terpasang (Laravel 11/12: PHP 8.2+, Laravel 13: PHP 8.3+), atau Laravel di luar 11.x–13.x → **berhenti dan laporkan**, jangan lanjut. Laravel **11, 12, dan 13 semuanya diterima**; catat versinya di laporan.
- Jika ada `AGENTS.md`/`CLAUDE.md` bawaan Laravel 13 (Laravel Boost) selain berkas ini, **jangan menimpa**; ikuti keduanya, dan bila bertentangan, berkas ini yang menang.
- Jika Breeze belum terpasang, kerjakan **sebelum kode lain** (Breeze menimpa `routes/web.php`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`).
- Database belum ada → minta pemilik membuat database `inventory_stok` di phpMyAdmin (kolasi `utf8mb4_unicode_ci`). Jangan mencoba membuatnya sendiri.
- Hal lain yang sudah terpasang → pakai apa adanya, jangan pasang ulang.

## 3. Keputusan yang sudah final (JANGAN ditanyakan lagi)

| Topik | Keputusan |
|---|---|
| Framework | Laravel 11, 12, atau 13 (ikuti versi yang terpasang di project); PHP sesuai syarat versi itu |
| Auth | Laravel Breeze **Blade** (bukan Livewire/Inertia). **Registrasi publik dimatikan**: hapus route dan tautan daftar. Akun dibuat admin. |
| Role | `spatie/laravel-permission`; middleware alias `role` didaftarkan di `bootstrap/app.php` |
| Database | MySQL `inventory_stok`, engine InnoDB, utf8mb4. Ganti `DB_CONNECTION` dari `sqlite` ke `mysql`; `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` tetap `database` (tabelnya dari migration bawaan) |
| Frontend | Blade + **Tailwind v3 dari Breeze** + Alpine.js + Chart.js. Skeleton Laravel 12/13 membawa Tailwind v4, tetapi proyek ini **mengikuti Breeze (v3)**: pakai `tailwind.config.js`, bukan `@theme`/`@import "tailwindcss"`. Hapus sisa `@tailwindcss/vite` bila ada |
| Ikon | `blade-ui-kit/blade-heroicons` (outline) |
| Font | `@fontsource-variable/inter` via npm (tanpa CDN) |
| PDF / Excel | `barryvdh/laravel-dompdf` / `maatwebsite/excel` |
| Terjemahan | `laravel-lang/common` untuk `id` (jika gagal dipasang, tulis `lang/id/validation.php` manual) |
| Zona waktu | `Asia/Jakarta` (ubah di `config/app.php`), locale `id` |
| Stok minimum | Barang menipis jika `stok <= stok_minimum`, habis jika `stok = 0` |
| Transaksi | Tidak dapat diubah/dihapus setelah disimpan |
| Stok awal | Tidak ada isian stok di form barang. Stok hanya berubah lewat transaksi |
| Penomoran | `BM-YYYYMMDD-###` (masuk), `BK-YYYYMMDD-###` (keluar), urut per hari |
| Alasan barang keluar | enum: `pemakaian`, `penjualan`, `rusak`, `koreksi`, `lainnya` |

**Paket yang diizinkan:** hanya yang tertulis di tabel di atas dan bawaan Laravel/Breeze. **Dilarang menambah paket lain** (Filament, Livewire, Inertia, Vue, React, Bootstrap, jQuery, DataTables, SweetAlert, dll.) tanpa izin tertulis dari pemilik.

## 4. Aturan arsitektur

> Spesifikasi field dan validasi: PRD §7. Aturan bisnis BR-01..BR-15: PRD §13. Rumus metrik dashboard: PRD §11.4. Spesifikasi laporan: PRD §15. Katalog pesan: PRD Lampiran B.

1. **Controller tipis.** Validasi di `FormRequest`, logika stok di `App\Services\StockService`, query laporan di method/Scope model atau kelas `Exports`.
2. **Semua perubahan stok hanya lewat `StockService`.** Dilarang `Product::increment('stok')`, `update(['stok' => …])`, atau perubahan stok langsung di controller/seeder lain.
3. `StockService` wajib: `DB::transaction()`, `lockForUpdate()` saat membaca barang, menolak stok negatif dengan exception khusus (`InsufficientStockException`), menulis satu baris `stock_movements` per perubahan (`stok_sebelum`, `stok_sesudah`, `user_id`, referensi).
4. **Model:** definisikan `$fillable`, `$casts`, relasi dua arah, dan scope: `Product::lowStock()` (stok ≤ minimum), `Product::outOfStock()`, accessor `status_stok` (`aman|menipis|habis`). **Logika status stok hanya di satu tempat ini.**
5. **Otorisasi di server:** route dikelompokkan dengan middleware `auth` + `role:` sesuai PRD §5.2 dan §16. Menyembunyikan menu saja **tidak cukup**.
6. **Akun nonaktif** (`users.is_active = false`) ditolak saat login dan sesi aktifnya diakhiri (middleware).
7. **Hapus data master** dicek dulu: bila masih dipakai, tolak dengan pesan jelas (PRD BR-09). Barang pakai `SoftDeletes` (BR-10).
8. **Tidak ada N+1:** gunakan eager loading (`with()`) pada semua daftar. Semua daftar memakai `paginate()`.
9. Gunakan route resource dan nama route standar (`products.index`, `stock-ins.create`, dst.). Tanpa edit/update/destroy untuk `stock-ins` dan `stock-outs`.
10. Query dashboard dan laporan dikumpulkan dalam kelas khusus (`App\Services\DashboardService`, `ReportService`), bukan di view.
11. Format kode: PSR-12, `declare(strict_types=1);` tidak wajib. Jalankan `./vendor/bin/pint` (di cmd: `vendor\bin\pint`) sebelum menyatakan fase selesai.
12. **Komentar dalam bahasa Indonesia** pada logika yang tidak jelas dengan sendirinya (terutama `StockService`, validasi stok, dan query laporan). Satu-dua baris, bukan tiap baris.

## 5. Aturan frontend

1. Ikuti `docs/DESIGN.md` **tanpa improvisasi**: token warna, kelas komponen, tata letak, dan daftar larangan di §12-nya.
2. Konfigurasi Tailwind **harus** memakai token dari DESIGN.md §3.2. Jangan menulis hex langsung di view; gunakan token (`bg-primary-600`, `text-ink`, dst.).
3. Buat dan **pakai** komponen Blade di DESIGN.md §11. Dilarang menyalin-tempel kelas tombol/kartu/badge ke banyak view.
4. Grafik memakai Chart.js yang di-*import* lewat npm di `resources/js/charts/*.js`. **Tanpa CDN.** Data grafik dikirim dari controller (JSON di atribut `data-*` atau `@json`).
5. Form transaksi dengan baris dinamis memakai Alpine.js (`x-data`, `x-for`), tanpa library tambahan.
6. Semua teks antarmuka bahasa Indonesia. Format: tanggal `02 Nov 2026` (Carbon `translatedFormat('d M Y')`), angka `1.250`, rupiah `Rp 45.000`. Buat helper `format_rupiah()` dan `format_angka()` di `app/helpers.php` (didaftarkan di `composer.json` → `autoload.files`).
7. Dilarang data dummy di view. Semua data dari database.
8. Setiap halaman daftar wajib punya: empty state, pagination, dan (bila ada) filter yang tetap tersimpan di query string.
9. Setiap form wajib: `@csrf`, `old()`, pesan galat per input, tombol submit yang dinonaktifkan setelah diklik.

## 6. Aturan data dan migration

1. Migration **satu tabel per file**, urutan: `categories`, `units`, `suppliers`, `products`, `stock_ins`, `stock_in_items`, `stock_outs`, `stock_out_items`, `stock_movements`, lalu tambahan kolom `users.is_active`.
2. Tipe, panjang, index, foreign key, dan perilaku `onDelete` **persis** seperti PRD §12.3. Jangan mengganti nama kolom, menambah kolom, atau menormalisasi ulang tanpa izin. Jika menurut Anda perlu perubahan skema: **berhenti, jelaskan alasan, tunggu persetujuan**, lalu perbarui PRD di commit yang sama.
3. Tambahkan `CHECK`/validasi aplikasi agar `products.stok >= 0` (kolom `unsignedInteger` untuk `stok` dan `stok_minimum`).
4. Seeder (`php artisan db:seed`) wajib membuat:
   - Role `admin`, `staff`, `pimpinan` dan akun contoh: `admin@inventory.test`, `staff@inventory.test`, `pimpinan@inventory.test` (kata sandi `password`, ditandai komentar "hanya untuk demo lokal").
   - Satuan: pcs, box, pak, rim, kg, liter, meter.
   - 5–6 kategori, 4–5 supplier, minimal 30 barang realistis (alat tulis, elektronik kecil, perlengkapan kantor), dengan stok minimum bervariasi.
   - Riwayat transaksi 6–12 bulan ke belakang yang dibuat **melalui `StockService`** agar kartu stok konsisten dan grafik dashboard terisi. Sebagian barang harus berstatus menipis dan habis.
5. `php artisan migrate:fresh --seed` boleh dijalankan **hanya** setelah `.env` dipastikan `DB_DATABASE=inventory_stok`. Dilarang pada database lain.

## 7. Urutan pengerjaan (fase) dan pemeriksaan

Kerjakan **berurutan**. Setelah tiap fase: jalankan pemeriksaan, laporkan sesuai §9, lalu **berhenti dan tunggu konfirmasi** sebelum fase berikutnya. Jangan mengerjakan fase di depan.

### Fase 1 — Fondasi
- Pasang hanya yang **belum** terpasang (urutan dan perintah: PRD Lampiran A.5–A.9): migrate bawaan ke MySQL, `breeze:install blade`, Spatie (publish + migrate), DomPDF, Excel, heroicons, `lang`, `chart.js`, `@fontsource-variable/inter`.
- Hapus tautan font bunny.net (Figtree) dari layout bawaan Breeze, ganti dengan font Inter dari npm (DESIGN §3.3).
- Tambahkan `use HasRoles;` pada model `User`; ubah `config/app.php` `timezone` menjadi `Asia/Jakarta`.
- Konfigurasi `.env` (hanya `APP_NAME`, `APP_URL`, kunci `DB_*`), `config/app.php` (timezone, locale), `tailwind.config.js` (DESIGN §3.2), `bootstrap/app.php` (alias middleware).
- Semua migration + model + relasi + seeder role/akun/satuan. Matikan registrasi. Middleware `is_active`.
- Layout `app` + `guest`, sidebar, topbar, komponen dasar DESIGN §11 (card, button, input, badge, table, toast, modal, empty-state, page-header).
- Halaman login bergaya DESIGN. Dashboard sementara (hanya kerangka).
- **Selesai jika:** `php artisan migrate:fresh --seed` sukses; tiga akun bisa login; sidebar menampilkan menu sesuai peran; `npm run build` sukses; akses `/users` oleh staff → 403.

### Fase 2 — Data master
- CRUD kategori, satuan, supplier, barang (pencarian, filter kategori/status, pagination, upload foto ke `storage/app/public/products`, `php artisan storage:link`).
- Kode barang otomatis `BRG-0001` jika kosong. Pencegahan hapus data yang masih dipakai. Soft delete barang.
- Halaman detail barang (kartu stok tampil kosong dulu).
- **Selesai jika:** semua skenario FR-10 s/d FR-16 lolos; pimpinan hanya bisa melihat barang.

### Fase 3 — Transaksi dan kartu stok
- `StockService`, `InsufficientStockException`, penomoran transaksi.
- Barang masuk dan keluar (daftar, form baris dinamis, detail), kartu stok di detail barang + halaman Kartu Stok.
- Tes otomatis untuk `StockService` (lihat §8).
- **Selesai jika:** skenario TC-01 s/d TC-13 PRD §18 lolos; stok `products` = hasil penjumlahan `stock_movements` untuk semua barang di data seeder.

### Fase 4 — Dashboard
- `DashboardService` + view sesuai DESIGN §6. Urutan: KPI → Level Stok → Daftar Barang → grafik Masuk vs Keluar → Stok per Kategori → Barang Masuk Terakhir → kolom kanan (sparkline + aktivitas).
- Lonceng topbar dan lencana menu Barang menampilkan jumlah stok menipis.
- **Selesai jika:** tampilan sesuai checklist DESIGN §13; angka cocok dengan hitungan manual dari database (verifikasi dengan `php artisan tinker`).

### Fase 5 — Laporan dan ekspor
- Empat laporan PRD §6.5 dan §15 dengan filter, pratinjau, unduh PDF dan Excel. Kartu Stok bisa diekspor (opsional).
- **Selesai jika:** TC-10 lolos; PDF memuat kop, periode, tanggal cetak, dan nama pencetak.

### Fase 6 — Finalisasi
- Halaman 403/404/419, uji responsif 360/768/1280 px, perapihan, `README.md` (instalasi XAMPP, akun demo, cara backup database), jalankan seluruh tes dan `pint`.
- **Selesai jika:** seluruh Kriteria Penerimaan PRD §21 terpenuhi.

*(Fase opsional v1.1 — stok opname, cetak bukti, log aktivitas — **hanya** dikerjakan jika diminta.)*

## 8. Pengujian

- Gunakan kerangka tes yang sudah terpasang (`php artisan test`). Basis data tes: SQLite in-memory atau database terpisah `inventory_stok_test`, **jangan** `inventory_stok`.
- Tes wajib (minimal):
  - `StockService`: tambah stok; kurangi stok; tolak stok kurang tanpa mengubah apa pun; rollback bila item kedua gagal; kartu stok tercatat dengan `stok_sebelum/sesudah` benar.
  - Akses per peran: staff tak bisa `/users`; pimpinan tak bisa `/stock-ins/create`; pimpinan bisa `/reports/*`; tamu dialihkan ke login.
  - Validasi: jumlah ≤ 0 ditolak; barang duplikat dalam satu transaksi ditolak/digabung; tanggal masa depan ditolak.
- Jangan menyatakan fase selesai jika ada tes gagal.

## 9. Cara melapor (setiap akhir fase)

Gunakan format ini, singkat, bahasa Indonesia:

1. **Ringkasan** — 2–3 kalimat apa yang selesai.
2. **File penting** — daftar file baru/diubah (tanpa isi).
3. **Penjelasan kode** — 3–6 butir yang menjelaskan *mengapa* dan *bagaimana* bagian paling penting bekerja (pemilik perlu bisa menjelaskannya saat presentasi).
4. **Cara mencoba** — URL, akun, dan langkah klik, atau perintah cmd.
5. **Hasil pemeriksaan** — keluaran `php artisan test`, `npm run build`, dan pemeriksaan "Selesai jika".
6. **Catatan/keputusan** — hal yang Anda putuskan sendiri (harus sesuai §3 dan PRD) dan apa pun yang belum tuntas.

Jangan membuat dokumen tambahan (catatan, rencana, changelog) di luar `README.md` dan `docs/PROGRESS.md`. `docs/PROGRESS.md` diperbarui tiap akhir fase: daftar fase + status (✔/◻) + tanggal.

## 10. Kapan boleh bertanya, kapan tidak

**Bertanya hanya jika:**
- Dokumen saling bertentangan dan tabel §0 tidak menyelesaikannya.
- Perlu mengubah skema database, menambah paket, atau menyimpang dari PRD/DESIGN.
- Tindakan merusak data di luar `inventory_stok`, atau perlu kredensial/rahasia.
- Hasil preflight gagal (§2).

**Tidak perlu bertanya** untuk hal yang sudah diputuskan di §3, nama kelas/variabel, penataan folder, teks galat, dan detail kecil visual yang sudah diatur DESIGN.md. Pilih opsi paling sederhana yang sesuai dokumen, lalu catat di laporan (§9 butir 6).

## 11. Larangan keras

- ❌ Mengubah stok di luar `StockService`.
- ❌ Mengedit atau menghapus transaksi yang sudah tersimpan.
- ❌ Menambah menu, modul, atau tabel yang tidak ada di PRD (Stores, Billing, Orders, Delivery, dll.).
- ❌ Mengubah warna/desain di luar DESIGN.md, atau memakai lime/hitam dari gambar referensi.
- ❌ Menambah paket tanpa izin. Memakai CDN. Mengubah `.env` selain kunci yang disebut di Fase 1.
- ❌ Menjalankan `migrate:fresh`, `db:wipe`, atau `rm -rf` pada target yang belum diverifikasi.
- ❌ Menyimpan kata sandi/rahasia di kode atau repo selain akun demo seeder.
- ❌ Mengubah file Breeze secara berlebihan; ubah hanya yang perlu (tampilan login, profil, middleware) dan pertahankan logika autentikasinya.
- ❌ `git commit`/`git push` tanpa diminta.
- ❌ Menandai fase selesai tanpa menjalankan pemeriksaan.

## 12. Definisi selesai (per fase)

- [ ] Semua poin fase dikerjakan, tidak ada fitur fase berikutnya yang ikut dibuat.
- [ ] `php artisan migrate:fresh --seed` sukses.
- [ ] `php artisan test` hijau, `npm run build` sukses, `vendor\bin\pint` bersih.
- [ ] Tidak ada galat di log (`storage/logs/laravel.log`) maupun konsol browser pada halaman yang dikerjakan.
- [ ] Tampilan sesuai DESIGN.md; teks Indonesia; tidak ada data dummy di view.
- [ ] Laporan fase (§9) dan `docs/PROGRESS.md` diperbarui.
