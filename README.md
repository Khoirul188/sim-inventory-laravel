# SIM Inventory — Sistem Informasi Manajemen Inventory Stok Barang

**SIM Inventory** adalah aplikasi web manajemen persediaan barang untuk satu gudang yang dibangun menggunakan **Laravel 12**, **Blade**, **Tailwind CSS v3**, **Alpine.js**, dan **Chart.js**. Sistem ini dirancang untuk mencatat data master barang, transaksi barang masuk dan keluar secara akurat melalui satu pintu layanan transaksi (`StockService`), memantau kartu stok dan batas stok minimum secara *real-time*, serta menghasilkan laporan resmi dalam format **PDF** dan **Excel (.xlsx)**.

---

## 1. Fitur Utama

- **Autentikasi & Otorisasi Berbasis Peran (`spatie/laravel-permission`)**:
  - **Admin**: Akses penuh ke seluruh fitur (Dashboard, Data Master, Transaksi Masuk/Keluar, Kartu Stok, Laporan PDF/Excel, dan Kelola Pengguna).
  - **Staff Gudang**: Mengelola Data Master (Barang, Kategori, Satuan, Supplier), mencatat Barang Masuk & Barang Keluar, serta melihat Kartu Stok dan Dashboard.
  - **Pimpinan**: Memantau Dashboard eksekutif, melihat daftar/detail Barang dan Transaksi (hanya baca), melihat Kartu Stok, serta melihat dan mengunduh seluruh Laporan (PDF & Excel).
- **Dashboard Analitik**:
  - 4 kartu KPI utama (Total Jenis Barang, Total Stok, Barang Masuk Bulan Ini, Barang Keluar Bulan Ini beserta persentase tren).
  - Panel **Level Stok** (progress bar barang menipis/habis), **Daftar Barang** terbaru, grafik area **Barang Masuk vs Keluar** (12 Bulan / 30 Hari), grafik donut **Stok per Kategori** (4 teratas + Lainnya), **Barang Masuk Terakhir**, **Barang Paling Sering Keluar** (sparkline 4 minggu), dan **Aktivitas Terbaru**.
- **Data Master**:
  - Pengelolaan Kategori, Satuan, Supplier, dan Barang (dilengkapi kode otomatis `BRG-xxxx`, unggah foto dengan pratinjau langsung, filter kategori/status stok, pencarian, dan *soft delete*).
- **Transaksi Barang Masuk & Barang Keluar**:
  - Formulir transaksi multi-baris dinamis menggunakan Alpine.js dengan pencegahan pilihan barang ganda.
  - Penomoran otomatis harian (`BM-YYYYMMDD-###` dan `BK-YYYYMMDD-###`).
  - Seluruh perubahan stok dikunci dengan `DB::transaction()` dan `lockForUpdate()` di `StockService` sehingga stok dijamin tidak pernah bernilai negatif.
  - Transaksi bersifat *immutable* (tidak dapat diubah atau dihapus setelah disimpan; koreksi dilakukan lewat transaksi pembalik).
- **Kartu Stok & Laporan Ekspor (PDF & Excel)**:
  - Riwayat pergerakan stok per barang lengkap dengan `stok_sebelum` dan `stok_sesudah`.
  - 4 laporan utama (**Stok Saat Ini**, **Barang Masuk**, **Barang Keluar**, **Stok Minimum**) + **Kartu Stok** dengan pratinjau di layar, unduhan PDF berkop resmi (dengan penomoran halaman `Halaman x dari y`), dan unduhan Excel (`.xlsx`) dengan tipe sel numerik asli serta proteksi injeksi rumus.

---

## 2. Prasyarat Lingkungan

Pastikan perangkat Anda telah memasang:

1. **XAMPP** dengan **PHP 8.2+** dan **MySQL / MariaDB** (pastikan ekstensi PHP `gd`, `zip`, `mbstring`, `fileinfo`, `pdo_mysql`, `pdo_sqlite`, `openssl`, `xml`, dan `dom` aktif di `C:\xampp\php\php.ini`).
2. **Composer** versi 2.x.
3. **Node.js** (v20.19+ atau v22 LTS) beserta **npm**.
4. Terminal **Command Prompt (`cmd`)** di Windows.

---

## 3. Langkah Instalasi dari Nol

Jalankan perintah berikut melalui **Command Prompt (`cmd`)**:

### Langkah 1 — Salin / Clone Project ke `htdocs`
Pastikan folder project berada di `C:\xampp\htdocs\pw_project`:
```bat
cd C:\xampp\htdocs\pw_project
```

### Langkah 2 — Pasang Dependensi PHP dan Frontend
```bat
composer install
npm install
```

### Langkah 3 — Siapkan Berkas Konfigurasi `.env`
Salin `.env.example` menjadi `.env` lalu buat `APP_KEY`:
```bat
copy .env.example .env
php artisan key:generate
```
Buka file `.env` dan pastikan nilai berikut sesuai dengan lingkungan XAMPP Anda:
```ini
APP_NAME="SIM Inventory"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/pw_project/public

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_stok
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 4 — Buat Database MySQL
Nyalakan modul **Apache** dan **MySQL** di XAMPP Control Panel, lalu buat database `inventory_stok` melalui phpMyAdmin (`http://localhost/phpmyadmin`) atau lewat Command Prompt:
```bat
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS inventory_stok CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Langkah 5 — Jalankan Migration dan Seeder Data Demo
```bat
php artisan migrate --seed
```
*(Atau gunakan `php artisan migrate:fresh --seed` untuk mereset ulang database beserta data demo 32 barang dan riwayat transaksi 9 bulan).*

### Langkah 6 — Buat Symbolic Link Storage & Build Aset Frontend
Jalankan Command Prompt sebagai **Administrator** (atau aktifkan *Developer Mode* di Windows) agar symlink foto barang berhasil dibuat:
```bat
php artisan storage:link
npm run build
```

### Langkah 7 — Buka Aplikasi di Browser
Buka alamat berikut di browser:
```text
http://localhost/pw_project/public
```

---

## 4. Akun Demo (Seeder)

Gunakan akun contoh berikut untuk mencoba masing-masing peran (hanya untuk lingkungan pengembangan/demo lokal):

| Peran | Nama Pengguna | Email | Kata Sandi | Hak Akses Utama |
|---|---|---|---|---|
| **Admin** | Pak Admin | `admin@inventory.test` | `password` | Akses penuh seluruh modul + Kelola Pengguna + Laporan |
| **Staff Gudang** | Mbak Gudang | `staff@inventory.test` | `password` | Data Master + Transaksi Masuk/Keluar + Kartu Stok |
| **Pimpinan** | Bu Pimpinan | `pimpinan@inventory.test` | `password` | Dashboard + Lihat Barang/Transaksi/Kartu Stok + Unduh Laporan PDF/Excel |

---

## 5. Cara Backup dan Restore Database

### Opsi A — Melalui Command Prompt (`mysqldump` XAMPP)
- **Backup (Ekspor ke file `.sql`):**
  ```bat
  C:\xampp\mysql\bin\mysqldump.exe -u root inventory_stok > backup_inventory_stok.sql
  ```
- **Restore (Impor dari file `.sql`):**
  ```bat
  C:\xampp\mysql\bin\mysql.exe -u root inventory_stok < backup_inventory_stok.sql
  ```

### Opsi B — Melalui phpMyAdmin
1. **Backup**: Buka `http://localhost/phpmyadmin` → pilih database **`inventory_stok`** di panel kiri → klik tab **Export (Ekspor)** di bagian atas → pilih metode **Quick (Cepat)** dan format **SQL** → klik **Go (Kirim)** untuk mengunduh file `.sql`.
2. **Restore**: Pilih database **`inventory_stok`** → klik tab **Import (Impor)** → pilih file `.sql` hasil backup → klik **Go (Kirim)**.

---

## 6. Menjalankan Pengujian Otomatis, Build, dan Pemeriksaan Gaya Kode

Jalankan perintah berikut dari folder root `C:\xampp\htdocs\pw_project`:

```bat
:: Menjalankan seluruh pengujian otomatis (PHPUnit pada SQLite in-memory)
php artisan test

:: Mem-build ulang aset CSS (Tailwind) dan JS (Alpine & Chart.js)
npm run build

:: Memeriksa kerapihan gaya kode (PSR-12 / Laravel Pint)
vendor\bin\pint --test

:: Merapikan gaya kode secara otomatis
vendor\bin\pint
```

---

## 7. Struktur Folder Penting & Aturan Arsitektur

```text
pw_project/
├── app/
│   ├── Exceptions/
│   │   └── InsufficientStockException.php  # Exception khusus saat stok barang tidak mencukupi
│   ├── Exports/                            # Kelas ekspor Excel (Laravel Excel + sanitasi rumus)
│   ├── Http/
│   │   ├── Controllers/                    # Controller tipis (delegasi ke FormRequest & Services)
│   │   ├── Middleware/EnsureUserIsActive.php
│   │   └── Requests/                       # Validasi input di sisi server (FormRequest)
│   ├── Models/                             # Model Eloquent ($fillable eksplisit, scope status stok)
│   ├── Services/
│   │   ├── StockService.php                # SATU-SATUNYA pintu perubahan stok & pencatatan kartu stok
│   │   ├── DashboardService.php            # Perhitungan seluruh metrik & grafik dashboard
│   │   └── ReportService.php               # Query terpusat untuk 4 laporan & kartu stok
│   └── helpers.php                         # Helper format_rupiah() dan format_angka()
├── database/
│   ├── migrations/                         # Skema tabel domain & pengguna
│   └── seeders/                            # Seeder role, akun demo, master barang, & riwayat 9 bulan
├── docs/
│   ├── PRD.md                              # Dokumen spesifikasi kebutuhan produk
│   ├── DESIGN.md                           # Sistem desain & pedoman komponen UI
│   ├── PROGRESS.md                         # Catatan progres pengerjaan per fase
│   ├── TEST-MAPPING.md                     # Pemetaan kasus uji PRD ke tes otomatis
│   └── screenshots/                        # Bukti tangkapan layar responsif (360, 768, 1280 px)
├── resources/
│   ├── js/charts/                          # Modul Chart.js (area, donut, sparkline)
│   └── views/                              # Komponen Blade reusable, halaman modul, PDF, & error pages
└── tests/Feature/                          # Pengujian fitur otomatis (Auth, Role, Master, Transaksi, Dashboard, Laporan)
```

> **Aturan Emas Arsitektur (`StockService`)**:
> Seluruh perubahan nilai `products.stok` **wajib** melalui `App\Services\StockService` (`createStockIn`, `createStockOut`, `increase`, `decrease`) yang berjalan di dalam transaksi database (`DB::transaction()`) dengan penguncian baris (`lockForUpdate()`) dan menulis riwayat ke tabel `stock_movements`. Dilarang mengubah kolom `stok` secara langsung di controller, model, ataupun seeder.

---

## 8. Catatan Keamanan

1. **Ganti Kata Sandi Demo**: Akun demo (`admin@inventory.test`, `staff@inventory.test`, `pimpinan@inventory.test`) menggunakan kata sandi `password` hanya untuk keperluan pengujian lokal. Wajib mengganti seluruh kata sandi tersebut sebelum digunakan pada lingkungan produksi.
2. **Nonaktifkan Mode Debug saat Demo Akhir / Produksi**: Ubah `APP_DEBUG=false` dan `APP_ENV=production` di `.env` agar detail teknis tidak pernah tampil ke pengguna, lalu jalankan `php artisan config:clear`.
3. **Kerahasiaan `.env`**: Jangan pernah melakukan commit atau mengunggah berkas `.env` ke repositori Git (sudah terdaftar di `.gitignore`).

---

## 9. Pemecahan Masalah Umum (Troubleshooting)

Aplikasi ini dapat dibuka baik melalui **Apache XAMPP** (`http://localhost/pw_project/public`) maupun melalui server bawaan Laravel **`php artisan serve`** (`http://127.0.0.1:8000`). Nilai `APP_URL` di `.env` tidak memengaruhi tampilnya foto barang karena URL foto dibentuk mengikuti alamat request aktif (`asset('storage/...')`). Bila foto barang tidak muncul, pastikan symbolic link `public\storage` sudah terbentuk dengan menjalankan `php artisan storage:link` melalui **Command Prompt (Run as Administrator)** di Windows. Selain itu, pastikan berkas `public/hot` tidak tersisa dari sesi `npm run dev` sebelumnya (hapus `public/hot` dan gunakan `npm run build` agar halaman tidak mencari server Vite), serta selalu hentikan layanan **MySQL** melalui tombol **Stop** di XAMPP Control Panel sebelum mematikan atau menutup komputer agar tabel dan *redo log* InnoDB tidak mengalami *hang*/*corrupt* saat dinyalakan kembali.

| Gejala | Penyebab | Solusi |
|---|---|---|
| **Foto barang tidak tampil (ikon gambar rusak / 404)** | Symbolic link `public\storage` belum dibuat atau gagal karena izin Windows | Buka **Command Prompt sebagai Administrator**, masuk ke `C:\xampp\htdocs\pw_project`, lalu jalankan `php artisan storage:link`. |
| **Semua request menghasilkan 404 saat menjalankan `php artisan test`** | `APP_URL` di `.env` mengandung subfolder (`/pw_project/public`) dan tidak ditimpa saat tes | Pastikan di dalam `phpunit.xml` terdapat baris `<env name="APP_URL" value="http://localhost"/>`. |
| **Tabel/data di database MySQL `inventory_stok` kosong setelah menjalankan `php artisan test`** | Pengaturan database tes di `phpunit.xml` masih dikomentari sehingga `RefreshDatabase` menghapus MySQL | Pastikan `<env name="DB_CONNECTION" value="sqlite"/>` dan `<env name="DB_DATABASE" value=":memory:"/>` aktif (tidak dikomentari) di `phpunit.xml`, lalu pulihkan data demo dengan `php artisan migrate:fresh --seed`. |
| **Browser *loading* terus saat membuka aplikasi** | Berkas `public/hot` masih tersisa dari `npm run dev` atau layanan MySQL XAMPP mengalami *hang* akibat komputer dimatikan tanpa menekan tombol Stop | Hapus berkas `public/hot` bila ada, jalankan `npm run build`, serta hentikan lalu nyalakan ulang MySQL dari XAMPP Control Panel. |
| **`npm` ditolak dengan pesan *running scripts is disabled*** | Terminal menggunakan PowerShell dengan kebijakan *Restricted* | Ganti profil terminal ke **Command Prompt (`cmd`)** sesuai panduan. |
| **Tampilan halaman tidak bergaya / error *Vite manifest not found*** | Aset frontend belum di-build ke `public/build` | Jalankan `npm run build` di Command Prompt. |
