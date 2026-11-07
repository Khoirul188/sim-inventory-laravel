# Pemetaan Kasus Uji (PRD §18 & §21) ke Pengujian Otomatis & Responsif

Dokumen ini memetakan seluruh kasus uji di `docs/PRD.md` §18 yang menjadi syarat Kriteria Penerimaan §21 ke method pengujian otomatis (`tests/Feature/*`) serta verifikasi headless browser (`docs/screenshots/*`).

## 1. Transaksi & Kartu Stok (`TC-01` s/d `TC-13`)

| ID Kasus Uji | Skenario | File Tes Otomatis | Method Tes | Status |
|---|---|---|---|---|
| **TC-01** | Barang masuk 10 unit untuk barang bersetok 3 → stok 13; kartu stok IN (sebelum 3, sesudah 13) | `tests/Feature/StockTransactionTest.php` | `test_tc01_tc02_tc03_tc05_stock_in_and_out_lifecycle` | ✔ Lulus |
| **TC-02** | Barang keluar 5 dari stok 13 → stok 8; kartu stok OUT (sebelum 13, sesudah 8) | `tests/Feature/StockTransactionTest.php` | `test_tc01_tc02_tc03_tc05_stock_in_and_out_lifecycle` | ✔ Lulus |
| **TC-03** | Barang keluar 20 dari stok 8 → ditolak (`InsufficientStockException`); stok tetap 8; tidak ada transaksi tersimpan | `tests/Feature/StockTransactionTest.php` | `test_tc01_tc02_tc03_tc05_stock_in_and_out_lifecycle` | ✔ Lulus |
| **TC-04** | Dua item dalam satu transaksi, item kedua gagal → seluruh transaksi di-rollback; stok item pertama tidak berubah | `tests/Feature/StockTransactionTest.php` | `test_tc04_tc11_rollback_when_second_item_fails_and_sequential_depletion` | ✔ Lulus |
| **TC-05** | Stok turun hingga sama dengan stok minimum → status `menipis` dan muncul di Level Stok (`Product::lowStock()`) | `tests/Feature/StockTransactionTest.php` | `test_tc01_tc02_tc03_tc05_stock_in_and_out_lifecycle` | ✔ Lulus |
| **TC-06** | Barang dobel dalam satu transaksi → ditolak validasi (`distinct`) | `tests/Feature/StockTransactionTest.php` | `test_tc06_tc07_tc08_tc09_validation_rules` | ✔ Lulus |
| **TC-07** | Jumlah 0 atau negatif → ditolak validasi (`min:1`) | `tests/Feature/StockTransactionTest.php` | `test_tc06_tc07_tc08_tc09_validation_rules` | ✔ Lulus |
| **TC-08** | Tanggal masa depan → ditolak validasi (`before_or_equal:today`) | `tests/Feature/StockTransactionTest.php` | `test_tc06_tc07_tc08_tc09_validation_rules` | ✔ Lulus |
| **TC-09** | Alasan kosong ditolak; *pemakaian*/*penjualan* tanpa tujuan ditolak; *koreksi*/*lainnya* tanpa keterangan ditolak; *rusak* tanpa tujuan diterima; urutan dropdown barang menurut `kode_barang` naik | `tests/Feature/StockTransactionTest.php` | `test_tc06_tc07_tc08_tc09_validation_rules`, `test_product_dropdown_in_transaction_forms_is_ordered_by_kode_barang` | ✔ Lulus |
| **TC-10** | Dua transaksi pada hari yang sama → nomor berurutan `BM-YYYYMMDD-001`, `BM-YYYYMMDD-002` (dan `BK-...`) | `tests/Feature/StockTransactionTest.php`, `tests/Feature/ReportTest.php` | `test_tc10_daily_sequential_transaction_numbers_and_immutability`, `test_four_reports_content_filters_soft_delete_shortage_and_tc10_sequential_numbers` | ✔ Lulus |
| **TC-11** | Dua permintaan mengeluarkan stok yang hanya cukup untuk satu → satu berhasil, satu ditolak; stok tidak pernah negatif (`lockForUpdate`) | `tests/Feature/StockTransactionTest.php` | `test_tc04_tc11_rollback_when_second_item_fails_and_sequential_depletion` | ✔ Lulus |
| **TC-12** | Cek kartu stok setelah banyak transaksi → `stok_sebelum` baris berikutnya = `stok_sesudah` baris sebelumnya | `tests/Feature/StockTransactionTest.php` | `test_tc12_tc13_seeder_stock_consistency_and_movement_chain` | ✔ Lulus |
| **TC-13** | Kueri konsistensi PRD §12.6 pada data seeder → mengembalikan 0 baris | `tests/Feature/StockTransactionTest.php` | `test_tc12_tc13_seeder_stock_consistency_and_movement_chain` | ✔ Lulus |

## 2. Autentikasi & Akun (`TC-20` s/d `TC-26`)

| ID Kasus Uji | Skenario | File Tes Otomatis | Method Tes | Status |
|---|---|---|---|---|
| **TC-20** | Login dengan kredensial benar (tiap peran: Admin, Staff, Pimpinan) → masuk dashboard, menu sesuai peran | `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/RoleAccessTest.php` | `test_users_can_authenticate_using_the_login_screen`, `test_three_demo_accounts_can_login_and_see_role_specific_sidebar` | ✔ Lulus |
| **TC-21** | Login dengan sandi salah → pesan `"Email atau kata sandi salah."` | `tests/Feature/Auth/AuthenticationTest.php` | `test_users_can_not_authenticate_with_invalid_password` | ✔ Lulus |
| **TC-22** | Login dengan akun nonaktif → ditolak dengan pesan `"Akun Anda dinonaktifkan. Hubungi admin."` | `tests/Feature/Auth/AuthenticationTest.php` | `test_inactive_user_cannot_login` | ✔ Lulus |
| **TC-23** | 6 kali login gagal beruntun → percobaan ke-6 diblokir sementara oleh `RateLimiter` | `tests/Feature/Auth/AuthenticationTest.php` | `test_tc23_six_failed_login_attempts_triggers_rate_limit` | ✔ Lulus |
| **TC-24** | Admin menonaktifkan akun yang sedang login → permintaan berikutnya pengguna itu di-logout dan diarahkan ke `/login` | `tests/Feature/Auth/AuthenticationTest.php` | `test_deactivated_logged_in_user_is_logged_out_on_next_request` | ✔ Lulus |
| **TC-25** | Admin menonaktifkan/menghapus/menurunkan peran dirinya sendiri atau menghapus admin aktif terakhir → ditolak di server | `tests/Feature/RoleAccessTest.php` | `test_admin_cannot_delete_deactivate_or_demote_self_and_must_keep_at_least_one_active_admin` | ✔ Lulus |
| **TC-26** | Membuka `/register` (serta `/forgot-password`, `/reset-password`, `/verify-email`, `/confirm-password`) → 404 | `tests/Feature/Auth/RegistrationTest.php` | `test_public_registration_and_unused_breeze_routes_are_disabled` | ✔ Lulus |

## 3. Hak Akses & Keamanan (`TC-30` s/d `TC-38`)

| ID Kasus Uji | Skenario | File Tes Otomatis | Method Tes | Status |
|---|---|---|---|---|
| **TC-30** | Staff membuka `/users` lewat URL → 403 dengan halaman kustom bahasa Indonesia | `tests/Feature/RoleAccessTest.php` | `test_role_authorization_rules`, `test_custom_error_pages_403_404_419_render_indonesian_text_without_leaking_details` | ✔ Lulus |
| **TC-31** | Pimpinan membuka `/stock-ins/create` dan `/stock-outs/create` → 403 | `tests/Feature/RoleAccessTest.php` | `test_role_authorization_rules` | ✔ Lulus |
| **TC-32** | Pimpinan membuka `/products/create`, `/categories`, `/units`, `/suppliers` → 403 | `tests/Feature/RoleAccessTest.php` | `test_role_authorization_rules` | ✔ Lulus |
| **TC-33** | Staff membuka `/reports/stok` (dan unduhan PDF/Excel laporan) → 403 | `tests/Feature/RoleAccessTest.php`, `tests/Feature/ReportTest.php` | `test_role_authorization_rules`, `test_role_access_and_preview_html_contains_pdf_and_excel_links_with_active_filters` | ✔ Lulus |
| **TC-34** | Tamu membuka `/dashboard`, `/users`, `/products`, `/reports/stok` → diarahkan ke `/login` | `tests/Feature/RoleAccessTest.php`, `tests/Feature/ReportTest.php` | `test_guest_is_redirected_to_login`, `test_role_access_and_preview_html_contains_pdf_and_excel_links_with_active_filters` | ✔ Lulus |
| **TC-35** | Nama barang `<script>alert(1)</script>` → di-escape otomatis oleh Blade (`&lt;script&gt;...`), tidak dieksekusi | `tests/Feature/MasterDataTest.php` | `test_tc35_and_tc37_xss_escaped_and_fake_php_image_rejected` | ✔ Lulus |
| **TC-36** | Kirim form POST tanpa token CSRF → 419 dengan halaman kustom bahasa Indonesia | `tests/Feature/RoleAccessTest.php` | `test_custom_error_pages_403_404_419_render_indonesian_text_without_leaking_details` | ✔ Lulus |
| **TC-37** | Unggah berkas `.php` yang diganti ekstensi menjadi `.jpg` → ditolak validasi `image|mimes:jpg,jpeg,png` | `tests/Feature/MasterDataTest.php` | `test_tc35_and_tc37_xss_escaped_and_fake_php_image_rejected` | ✔ Lulus |
| **TC-38** | Nama barang `=HYPERLINK(...)` (atau `+`, `-`, `@`) diekspor ke Excel → diawali `'` dan disimpan sebagai `DataType::TYPE_STRING`, bukan rumus | `tests/Feature/ReportTest.php` | `test_pdf_and_excel_downloads_for_all_reports_and_formula_injection_protection` | ✔ Lulus |

## 4. Data Master (`TC-40` s/d `TC-44`)

| ID Kasus Uji | Skenario | File Tes Otomatis | Method Tes | Status |
|---|---|---|---|---|
| **TC-40** | Tambah barang tanpa kode → kode otomatis `BRG-xxxx` berurutan dan stok awal 0 | `tests/Feature/MasterDataTest.php` | `test_product_auto_code_photo_upload_duplicate_code_and_soft_delete` | ✔ Lulus |
| **TC-41** | Tambah barang dengan kode yang sudah ada → ditolak dengan pesan `"Kode barang sudah digunakan."` | `tests/Feature/MasterDataTest.php` | `test_product_auto_code_photo_upload_duplicate_code_and_soft_delete` | ✔ Lulus |
| **TC-42** | Hapus kategori/satuan yang masih dipakai barang → ditolak dengan pesan penjelasan jumlah barang pemakai | `tests/Feature/MasterDataTest.php` | `test_category_crud_and_cannot_delete_used_category`, `test_unit_crud_and_cannot_delete_used_unit` | ✔ Lulus |
| **TC-43** | Hapus barang yang sudah punya transaksi → soft delete (`deleted_at` terisi); riwayat tetap tampil di laporan transaksi historis | `tests/Feature/MasterDataTest.php`, `tests/Feature/ReportTest.php` | `test_product_auto_code_photo_upload_duplicate_code_and_soft_delete`, `test_four_reports_content_filters_soft_delete_shortage_and_tc10_sequential_numbers` | ✔ Lulus |
| **TC-44** | Filter barang status `"menipis"` → hanya menampilkan barang dengan `stok <= stok_minimum` | `tests/Feature/MasterDataTest.php` | `test_product_search_and_status_filter` | ✔ Lulus |

## 5. Responsif & Antarmuka (`TC-50`, `TC-51`, `TC-53`)

| ID Kasus Uji | Skenario | Bukti Pengujian | Hasil | Status |
|---|---|---|---|---|
| **TC-50** | Semua halaman utama pada lebar 360 px, 768 px, dan 1280 px tanpa gulir horizontal pada `body`/`documentElement` | Tangkapan layar & audit otomatis Playwright headless di `docs/screenshots/*-360.png`, `*-768.png`, `*-1280.png` | `scrollWidth <= clientWidth` pada seluruh halaman di 360 px, 768 px, dan 1280 px | ✔ Lulus |
| **TC-51** | Sidebar pada lebar < 1024 px menjadi menu geser (drawer) yang dapat dibuka dan ditutup lewat tombol hamburger | Pengujian klik tombol hamburger & tutup menu via Playwright pada lebar 360 px (`docs/screenshots/sidebar-open-360.png`) | Drawer terbuka (`translate-x-0`) saat tombol hamburger diklik dan tertutup kembali saat tombol tutup/overlay diklik | ✔ Lulus |
| **TC-53** | Format tanggal, angka, dan rupiah (`02 Nov 2026`, `1.250`, `Rp 45.000`) | `tests/Feature/DashboardTest.php`, `app/helpers.php` | `format_rupiah()` dan `format_angka()` serta `translatedFormat('d M Y')` terverifikasi | ✔ Lulus |

## 6. Dashboard & Laporan (`TC-60` s/d `TC-66`)

| ID Kasus Uji | Skenario | File Tes Otomatis | Method Tes | Status |
|---|---|---|---|---|
| **TC-60** | Bandingkan 4 KPI, Level Stok, Grafik 12m/30d, Donut Kategori, Top 5 Keluar, dan Aktivitas dengan hitungan manual database | `tests/Feature/DashboardTest.php` | `test_dashboard_metrics_match_manual_calculations_including_soft_delete_and_zero_baseline` | ✔ Lulus |
| **TC-61** | Dashboard pada database kosong → semua panel menampilkan empty state tanpa galat | `tests/Feature/DashboardTest.php` | `test_dashboard_empty_database_renders_cleanly_and_hides_low_stock_badges` | ✔ Lulus |
| **TC-62** | Pembanding bulan lalu bernilai 0 → badge tren menampilkan `"—"` (bukan pembagian nol) | `tests/Feature/DashboardTest.php` | `test_dashboard_metrics_match_manual_calculations_including_soft_delete_and_zero_baseline` | ✔ Lulus |
| **TC-63** | Lonceng topbar dan lencana menu Barang menampilkan jumlah barang `stok <= stok_minimum` tanpa query N+1 | `tests/Feature/DashboardTest.php` | `test_dashboard_access_per_role_and_no_n_plus_one_queries` | ✔ Lulus |
| **TC-64** | Unduh keempat laporan + Kartu Stok sebagai PDF dan Excel → file valid (`%PDF-`, `.xlsx`), memuat kop, periode, pencetak, halaman, dan sel angka bertipe numerik | `tests/Feature/ReportTest.php` | `test_role_access_and_preview_html_contains_pdf_and_excel_links_with_active_filters`, `test_pdf_and_excel_downloads_for_all_reports_and_formula_injection_protection` | ✔ Lulus |
| **TC-65** | Laporan Barang Masuk dengan filter supplier dan tanggal → hanya menampilkan data yang sesuai dengan total akurat | `tests/Feature/ReportTest.php` | `test_four_reports_content_filters_soft_delete_shortage_and_tc10_sequential_numbers` | ✔ Lulus |
| **TC-66** | Rentang tanggal > 12 bulan atau tanggal wajib kosong pada laporan Masuk/Keluar → ditolak validasi dengan pesan jelas | `tests/Feature/ReportTest.php` | `test_date_range_validation_required_and_max_12_months_on_masuk_and_keluar` | ✔ Lulus |

