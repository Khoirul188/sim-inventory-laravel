# DESIGN.md — Panduan Desain UI SIM Inventory

> Dokumen ini adalah **satu-satunya sumber kebenaran untuk tampilan**. Fungsi, data, dan alur ada di `docs/PRD.md`.
> Jika DESIGN.md dan PRD §14 berbeda soal visual (misalnya radius sudut, tata letak dashboard), **DESIGN.md menang**.
> Palet warna **tetap memakai palet PRD §14.3**, bukan warna lime/hitam dari gambar referensi.

---

## 1. Referensi dan cara memakainya

Referensi: dashboard admin bergaya modern-minimal (sidebar gelap, konten terang, kartu putih membulat besar, grafik halus). Yang **diambil** dari referensi adalah *struktur, kerapatan, bentuk, dan gaya komponen*. Yang **diganti** adalah warna dan isi (disesuaikan dengan sistem inventory).

| Elemen di referensi | Diterapkan sebagai | Status |
|---|---|---|
| Sidebar hitam, menu aktif berupa pil abu | Sidebar `ink` (#0F172A), menu aktif pil `white/10` | Ambil bentuk, ganti warna |
| Aksen hijau lime | Biru utama `primary-600` (#2563EB) | Ganti |
| Judul halaman + search + profil + lonceng | Topbar sama persis, isi disesuaikan (§5.2) | Ambil |
| 4 kartu KPI dengan badge tren | 4 kartu KPI sesuai PRD FR-30 (§6.1) | Ambil, ganti isi |
| Donut "Profit by Category" | Donut **Stok per Kategori** | Ganti isi |
| Area chart "Order Summary" | Area chart **Barang Masuk vs Keluar** | Ganti isi |
| "Stock Level" dengan progress bar | **Level Stok** (barang paling kritis) | Ganti isi |
| "Upcoming Restock" | **Barang Masuk Terakhir** | Ganti isi (v1 tanpa modul PO) |
| "Traffic Source" + sparkline | **Barang Paling Sering Keluar** + sparkline | Ganti isi |
| "Recent Activity" timeline | **Aktivitas Terbaru** (5 transaksi terakhir) | Ganti isi |
| Tabel "Product" di bawah | **Daftar Barang** (5 baris) di dashboard | Ambil |
| Menu Stores, Billing, Orders, Delivery | — | **Jangan dibuat** (di luar lingkup) |
| Kolom "Traffic Source" (Amazon, Ebay, dst.) | — | **Jangan dibuat** |

---

## 2. Prinsip visual

1. **Lega dan tenang.** Banyak ruang kosong, kartu putih di atas latar abu sangat muda, bayangan sangat tipis.
2. **Satu warna aksen.** Biru dipakai untuk aksi dan elemen aktif. Hijau, amber, dan merah hanya untuk **status** (aman, menipis, habis, galat).
3. **Sudut besar.** Kartu `rounded-2xl` (16 px), input dan tombol `rounded-lg` (8 px), badge `rounded-full`.
4. **Angka adalah bintang.** Angka KPI besar dan tebal, label kecil dan redup.
5. **Status tidak hanya warna.** Selalu ada teks (Aman/Menipis/Habis).
6. **Konsisten.** Semua halaman memakai kerangka yang sama: header halaman → kartu → tabel/form.

---

## 3. Design tokens

### 3.1 Warna

| Token Tailwind | Hex | Penggunaan |
|---|---|---|
| `primary-600` (DEFAULT) | `#2563EB` | Tombol utama, tautan, fokus, seri "keluar" di grafik |
| `primary-800` | `#1E40AF` | Hover tombol utama, segmen donut tertua |
| `primary-50/100/200/300/400/500/700/900` | Skala `blue` bawaan Tailwind | Tint latar, area grafik, segmen donut |
| `ink` | `#0F172A` | Latar sidebar, teks utama, judul |
| `muted` | `#475569` | Teks sekunder, isi tabel |
| `subtle` | `#64748B` | Label kecil, keterangan, placeholder (kontras ≥ 4,5:1) |
| `line` | `#E2E8F0` | Border, pemisah |
| `canvas` | `#F8FAFC` | Latar halaman |
| `surface` | `#FFFFFF` | Kartu, tabel, form |
| `success` | `#16A34A` (soft `#DCFCE7`, ink `#15803D`) | Stok aman, berhasil, seri "masuk" di grafik |
| `warning` | `#D97706` (soft `#FEF3C7`, ink `#B45309`) | Stok menipis |
| `danger` | `#DC2626` (soft `#FEE2E2`, ink `#B91C1C`) | Stok habis, hapus, galat |
| `info` | `#0EA5E9` (soft `#E0F2FE`, ink `#0369A1`) | Informasi netral |

Teks pada badge memakai warna `ink` dari tiap status di atas latar `soft`-nya. Jangan memakai warna di luar tabel ini.

### 3.2 `tailwind.config.js` (Tailwind v3 yang dipasang Breeze)

> Skeleton Laravel 12/13 memakai Tailwind v4 (tanpa file ini). Breeze memasang Tailwind **v3** dan membuat `tailwind.config.js` serta `postcss.config.js`. Proyek ini mengikuti Breeze, jadi **jangan** memakai sintaks v4 (`@theme`, `@import "tailwindcss"`). File di bawah menggantikan isi bawaan Breeze (pertahankan `content` dan plugin `forms`).

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: { sans: ['"Inter Variable"', 'Inter', ...defaultTheme.fontFamily.sans] },
      colors: {
        primary: {
          50: '#EFF6FF', 100: '#DBEAFE', 200: '#BFDBFE', 300: '#93C5FD', 400: '#60A5FA',
          500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF', 900: '#1E3A8A',
          DEFAULT: '#2563EB',
        },
        ink: '#0F172A',
        muted: '#475569',
        subtle: '#64748B',
        line: '#E2E8F0',
        canvas: '#F8FAFC',
        success: { DEFAULT: '#16A34A', soft: '#DCFCE7', ink: '#15803D' },
        warning: { DEFAULT: '#D97706', soft: '#FEF3C7', ink: '#B45309' },
        danger:  { DEFAULT: '#DC2626', soft: '#FEE2E2', ink: '#B91C1C' },
        info:    { DEFAULT: '#0EA5E9', soft: '#E0F2FE', ink: '#0369A1' },
      },
      borderRadius: { card: '16px' },
      boxShadow: {
        card: '0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06)',
        pop: '0 10px 24px rgba(15,23,42,.12)',
      },
    },
  },
  plugins: [forms],
};
```

### 3.3 Tipografi

Font: **Inter**, dipasang lewat npm `@fontsource-variable/inter` (diimpor di `resources/js/app.js`), **bukan** CDN, supaya jalan tanpa internet di XAMPP.

| Peran | Kelas Tailwind |
|---|---|
| Judul halaman | `text-2xl font-semibold text-ink` |
| Judul kartu | `text-lg font-semibold text-ink` |
| Angka KPI | `text-3xl font-bold text-ink tabular-nums` |
| Isi teks / sel tabel | `text-sm text-muted` (sel utama: `text-ink font-medium`) |
| Label form | `text-sm font-medium text-ink` |
| Keterangan kecil | `text-xs text-subtle` |
| Label grup sidebar | `text-[11px] font-semibold uppercase tracking-wider text-slate-400` |

Semua angka (stok, jumlah, harga) memakai `tabular-nums` dan format Indonesia: `number_format($n, 0, ',', '.')`, harga `Rp 45.000`.

### 3.4 Spasi dan bentuk

- Kelipatan 4 px. Padding kartu `p-5` (mobile) / `p-6` (≥ md). Jarak antar kartu `gap-5`.
- Kartu: `bg-white rounded-card shadow-card border border-line/60`.
- Area konten: `px-4 md:px-6 lg:px-8 py-6`, lebar maksimum `max-w-[1600px]`.

---

## 4. Kerangka halaman (layout)

```
┌──────────┬─────────────────────────────────────────────────────────────┐
│ SIDEBAR  │ TOPBAR (judul halaman · search · profil · lonceng)          │
│ 240 px   ├─────────────────────────────────────────────────────────────┤
│ bg-ink   │  KONTEN (bg-canvas)                                         │
│ fixed    │                                                             │
└──────────┴─────────────────────────────────────────────────────────────┘
```

- File: `resources/views/layouts/app.blade.php` (membungkus `components/sidebar` dan `components/topbar`).
- Sidebar `fixed inset-y-0 left-0 w-60` pada ≥ `lg`. Konten diberi `lg:pl-60`.
- Di bawah `lg`: sidebar menjadi drawer (Alpine `x-data="{ open:false }"`), dibuka tombol ☰ di topbar, dengan overlay `bg-ink/50`.

---

## 5. Komponen global

### 5.1 Sidebar

- Latar `bg-ink`, tinggi penuh, `flex flex-col`, padding `px-3 py-5`.
- Logo: ikon (heroicon `cube` atau `square-3-stack-3d`, warna `primary-400`) + teks **SIM Inventory** `text-white text-lg font-semibold`.
- Label grup: gaya §3.3, `px-3 mt-6 mb-2`.
- Item menu: `flex items-center gap-3 rounded-xl px-3 h-11 text-sm text-slate-300 hover:bg-white/5 hover:text-white`, ikon `w-5 h-5`.
- Item **aktif**: `bg-white/10 text-white font-medium` (pil seperti referensi). Penentuan aktif memakai `request()->routeIs('products.*')`.
- Lencana angka (opsional) di menu **Barang**: jumlah stok menipis, `ml-auto bg-warning text-white text-[11px] rounded-full px-2`.
- **Menu dan ikon (Heroicons outline), tampil sesuai peran (PRD §5.2):**

| Grup | Menu | Ikon | Peran |
|---|---|---|---|
| MENU | Dashboard | `squares-2x2` | semua |
| DATA MASTER | Barang | `cube` | semua (pimpinan hanya lihat) |
| | Kategori | `rectangle-group` | admin, staff |
| | Satuan | `scale` | admin, staff |
| | Supplier | `truck` | admin, staff |
| TRANSAKSI | Barang Masuk | `arrow-down-tray` | admin, staff (pimpinan: hanya daftar dan detail, tanpa tombol tambah) |
| | Barang Keluar | `arrow-up-tray` | admin, staff (pimpinan: hanya daftar dan detail, tanpa tombol tambah) |
| | Kartu Stok | `clipboard-document-list` | semua |
| LAPORAN | Laporan | `chart-bar-square` | admin, pimpinan |
| PENGATURAN | Pengguna | `users` | admin |
| | Profil Saya | `cog-6-tooth` | semua |
| | Keluar | `arrow-right-on-rectangle` | semua (form POST logout), dipasang di dasar sidebar (`mt-auto`) |

### 5.2 Topbar

Satu baris, tinggi `h-20`, tanpa border bawah (menyatu dengan latar `canvas`).

- **Kiri:** tombol ☰ (hanya < `lg`) + judul halaman `text-2xl font-semibold`.
- **Tengah-kanan:** kolom pencarian global `w-full max-w-md h-12 rounded-xl bg-white border border-line pl-11` dengan ikon `magnifying-glass`, placeholder "Cari kode atau nama barang…". Submit → `GET /products?search=…`. Disembunyikan di < `md` (diganti ikon yang membuka kolom).
- **Profil:** avatar lingkaran 40 px berisi **inisial** nama (`bg-primary-100 text-primary-800 font-semibold`), di sebelahnya nama (`text-sm font-semibold text-ink`) dan nama peran (`text-xs text-subtle`, contoh "Staff Gudang"), lalu chevron. Klik membuka dropdown Alpine: *Profil Saya*, *Keluar*. Di < `sm` hanya avatar.
- **Lonceng:** tombol putih persegi membulat 48 px `rounded-xl border border-line`, ikon `bell`. Titik merah `bg-danger` di pojok kanan atas **hanya jika** ada stok menipis. Klik → `/products?status=menipis`. Atribut `aria-label="Ada N barang stok menipis"`.

### 5.3 Header halaman (selain dashboard)

```
Judul Halaman                                   [ + Tombol Aksi Utama ]
Beranda › Data Master › Barang
```
Breadcrumb `text-sm text-subtle`, tautan terakhir `text-ink`. Tombol aksi utama di kanan (di mobile turun ke bawah judul, `w-full`).

### 5.4 Kartu

Komponen `<x-card>` dengan slot judul opsional:

```blade
<section {{ $attributes->merge(['class' => 'bg-white rounded-card shadow-card border border-line/60 p-5 md:p-6']) }}>
  @isset($title)
    <header class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-ink">{{ $title }}</h2>
      {{ $action ?? '' }}
    </header>
  @endisset
  {{ $slot }}
</section>
```

### 5.5 Tombol

| Varian | Kelas |
|---|---|
| Primer | `h-10 px-4 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-800 focus-visible:ring-2 focus-visible:ring-primary-600/40 disabled:opacity-50` |
| Sekunder | `h-10 px-4 rounded-lg bg-white text-ink border border-line hover:bg-slate-50` |
| Bahaya | `h-10 px-4 rounded-lg bg-danger text-white hover:bg-danger-ink` |
| Ikon (tabel) | `h-9 w-9 grid place-items-center rounded-lg text-subtle hover:bg-slate-100 hover:text-ink` |

Tombol submit menjadi nonaktif dan menampilkan "Menyimpan…" setelah diklik (Alpine), supaya tidak terkirim dua kali.

### 5.6 Input form

- Input/select/textarea: `h-10 w-full rounded-lg border border-line bg-white px-3 text-sm text-ink placeholder:text-subtle focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20`.
- Label di atas, `mb-1.5`. Wajib diberi `<span class="text-danger">*</span>`.
- Galat: border `border-danger`, teks `text-xs text-danger-ink mt-1`. Teks bantuan: `text-xs text-subtle mt-1`.
- Input kosong tidak boleh kehilangan isian saat validasi gagal (`old()`).

### 5.7 Badge

`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium` + varian:

| Status | Kelas | Teks |
|---|---|---|
| Aman | `bg-success-soft text-success-ink` | Aman |
| Menipis | `bg-warning-soft text-warning-ink` | Menipis |
| Habis | `bg-danger-soft text-danger-ink` | Habis |
| Masuk | `bg-success-soft text-success-ink` | ⬇ Masuk |
| Keluar | `bg-primary-100 text-primary-800` | ⬆ Keluar |
| Tren naik | `bg-success-soft text-success-ink` | ▲ +6,5% |
| Tren turun | `bg-danger-soft text-danger-ink` | ▼ −6,2% |

Logika status stok ada **di satu tempat**: accessor `Product::status_stok` (`habis` jika stok = 0, `menipis` jika stok ≤ stok_minimum, selain itu `aman`). Komponen `<x-badge-stok :product="$p" />` memakainya.

### 5.8 Tabel

Dibungkus `<x-card class="p-0 overflow-hidden">` dengan `overflow-x-auto` di dalamnya.

- `thead`: `bg-slate-50 text-xs font-semibold uppercase tracking-wide text-subtle`, `th` `px-5 py-3 text-left`.
- `tbody`: `divide-y divide-line`, baris `hover:bg-slate-50/70`, `td` `px-5 py-3.5 text-sm text-muted`.
- Kolom angka rata kanan dengan `tabular-nums`.
- Sel foto: `h-10 w-10 rounded-lg object-cover bg-slate-100` (placeholder ikon `photo` jika kosong).
- Kolom aksi: tiga tombol ikon (`eye`, `pencil-square`, `trash`; ikon hapus `hover:text-danger`).
- Footer: "Menampilkan 1–10 dari 128" di kiri, pagination di kanan. Pagination memakai view kustom bergaya tombol `h-9 min-w-9 rounded-lg`, halaman aktif `bg-primary-600 text-white`. Publikasikan dengan `php artisan vendor:publish --tag=laravel-pagination` lalu sesuaikan `tailwind.blade.php`.
- Bar filter di atas tabel (dalam kartu terpisah atau bagian atas kartu yang sama): pencarian + select kategori + select status + tombol Reset, `flex flex-wrap gap-3`.

### 5.9 Alert/toast, modal, empty state

- **Toast:** kanan atas, `fixed top-5 right-5 z-50 w-80`, kartu putih dengan garis kiri 4 px berwarna status, ikon status, teks, tombol tutup. Hilang otomatis 5 detik (Alpine `x-init="setTimeout(() => show=false, 5000)"`). Dipicu dari `session('success'|'error'|'warning')`.
- **Modal konfirmasi:** overlay `bg-ink/50`, panel `rounded-2xl p-6 max-w-md`, judul, kalimat yang menyebut nama data, dua tombol (Batal + tombol aksi bernama jelas, misal "Hapus Barang").
- **Empty state:** di tengah kartu, ikon abu 48 px, judul, satu kalimat, tombol aksi.

---

## 6. Halaman Dashboard (`/dashboard`)

Urutan prioritas implementasi mengikuti PRD: **M/S** dulu, panel bertanda *Opsional* dibuat terakhir.

### 6.1 Grid

```
≥ 2xl (1536 px): [ kolom utama (1fr) ........................ ][ kolom kanan 320 px ]
                 grid grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_320px] gap-5
< 2xl:           satu kolom, kolom kanan turun di bawah. (Pada 1280–1535 px, sidebar 240 px + kolom kanan 320 px menyisakan terlalu sedikit lebar untuk 4 kartu KPI, labelnya terpotong jadi 3 baris.)
```

**Kolom utama (atas ke bawah):**

1. **Baris KPI** — `grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5`
2. **Baris grafik** — `grid grid-cols-1 lg:grid-cols-2 gap-5`: *Stok per Kategori* | *Barang Masuk vs Keluar*
3. **Baris panel** — `grid grid-cols-1 lg:grid-cols-5 gap-5`: *Level Stok* (3/5) | *Barang Masuk Terakhir* (2/5)
4. **Daftar Barang** (tabel 5 baris)

**Kolom kanan:** *Barang Paling Sering Keluar* lalu *Aktivitas Terbaru*.

### 6.2 Kartu KPI (M — PRD FR-30)

Struktur kartu (seperti referensi): label kecil kiri atas, angka besar kiri bawah, badge tren + teks kecil di kanan bawah.

| # | Label | Angka | Badge + teks kecil |
|---|---|---|---|
| 1 | Total Jenis Barang | `count(products)` | badge: `+N` barang baru, teks "minggu ini" |
| 2 | Total Stok | `sum(products.stok)` | badge: ▲/▼ selisih bersih 7 hari (masuk − keluar dari `stock_movements`), teks "7 hari terakhir" |
| 3 | Barang Masuk | jumlah unit masuk bulan ini | badge: % perubahan, teks "dari bulan lalu" |
| 4 | Barang Keluar | jumlah unit keluar bulan ini | badge: % perubahan, teks "dari bulan lalu" |

Kelas: `<x-card class="!p-5">`, angka `text-3xl font-bold tabular-nums`. Jika pembanding bernilai 0, tampilkan "—" (bukan "∞%" atau "NaN"). Ikon kecil di pojok kanan atas kartu opsional: lingkaran `bg-primary-50 text-primary-600` 36 px.

### 6.3 Stok per Kategori — donut (S)

- Kiri: Chart.js `doughnut`, `cutout: '72%'`, tinggi 200 px, segmen berjarak (`borderWidth: 4`, `borderColor: '#fff'`, `borderRadius: 8`). Di tengah: teks total stok (plugin sederhana atau elemen absolut).
- Kanan: legenda daftar (bukan legenda bawaan Chart.js): kotak warna 10 px, nama kategori + persen redup (`text-subtle`), jumlah stok rata kanan `tabular-nums`. Seperti referensi.
- Warna segmen berurutan dari terbesar: `#1E40AF`, `#2563EB`, `#60A5FA`, `#BFDBFE`, `#E2E8F0` (kategori ke-5 dan seterusnya digabung menjadi "Lainnya"). Maksimal 5 segmen.
- Header kanan: teks "Total Stok" kecil + angka besar `text-2xl font-semibold` (seperti "Total Annual Profit").

### 6.4 Barang Masuk vs Keluar — area chart (S — PRD FR-32)

- Chart.js `line` dengan `fill: true`, `tension: 0.4`, `pointRadius: 0`, `pointHoverRadius: 5`, titik hover berisi putih dengan border seri.
- Dua seri: **Masuk** `#16A34A`, **Keluar** `#2563EB`. Isi area gradien vertikal dari warna seri `alpha .18` ke transparan.
- Sumbu: tanpa garis vertikal, garis horizontal `#E2E8F0` putus-putus tipis, label sumbu `#64748B` 12 px. Sumbu Y mulai dari 0.
- Tooltip gelap seperti referensi: `bg-ink`, teks putih, sudut 8 px, menampilkan bulan + dua nilai.
- Angka ringkasan di kiri atas (seperti "$8,870 Total Profit"): total keluar periode + label "Total keluar".
- Dropdown kanan atas (gaya teks `text-subtle` + chevron): **12 Bulan Terakhir** (default) / **30 Hari Terakhir**, memakai query `?range=12m|30d`.
- Legenda manual di atas grafik: titik warna + nama seri.

### 6.5 Level Stok — progress bar (M — PRD FR-31)

Mengambil pola "Stock Level" di referensi.

- Header: judul **Level Stok**; di kanan, pil status: jika ada stok menipis → `bg-warning-soft text-warning-ink` "Perlu Perhatian"; jika tidak → `bg-success-soft text-success-ink` "Aman".
- Di bawah header: kiri "Barang Menipis" (label kecil) + angka besar `N` `/ total jenis barang` (seperti "225 /370").
- Daftar **5 barang paling kritis** (`stok <= stok_minimum`, urut rasio `stok/stok_minimum` terkecil). Tiap baris:
  - Kiri: nama barang `text-sm text-ink`. Kanan: "`{stok} {satuan}` dari min. `{stok_minimum}`" `text-sm text-muted tabular-nums`.
  - Bar: `h-1.5 rounded-full bg-slate-100`, isi `rounded-full` dengan lebar `min(stok/stok_minimum,1)*100%`. Warna isi `bg-warning`; jika stok 0 maka lebar 0 dan teks "Habis" berwarna `text-danger-ink`.
- Jika kosong: empty state kecil ikon `check-circle` hijau + teks "Semua stok di atas batas minimum".
- Tautan "Lihat semua (N)" → `/products?status=menipis`.

### 6.6 Barang Masuk Terakhir (S)

Menggantikan "Upcoming Restock". Lima baris item dari transaksi masuk terbaru. Tiap baris: foto 40 px `rounded-lg`, nama barang `text-sm text-ink`, di kanan `+{jumlah} {satuan}` `font-semibold tabular-nums` dan tanggal `text-xs text-subtle`. Tautan "Lihat semua" → `/stock-ins`.

### 6.7 Daftar Barang di dashboard (S)

Tabel §5.8 dengan bar atas: judul **Daftar Barang**, pencarian kecil, tautan "Lihat semua →" ke `/products`. Kolom: #, Foto, Kode, Nama, Kategori, Stok, Status (badge), Aksi (ikon `eye`). Tampilkan 5 baris terbaru.

### 6.8 Kolom kanan (Opsional — dibuat terakhir)

**a) Barang Paling Sering Keluar** (30 hari terakhir, top 5)
Meniru "Traffic Source": tiap baris berisi nama (`text-sm text-muted`), persen besar `text-2xl font-bold` (porsi dari total keluar), keterangan "Minggu lalu: X%" `text-xs text-subtle`, dan **sparkline** di kanan (Chart.js `line`, 96×40 px, tanpa sumbu, `tension .4`, isi gradien tipis, warna `#2563EB`, 4 titik = jumlah keluar per minggu). Pemisah `border-b border-line` antar baris.

**b) Aktivitas Terbaru**
Meniru "Recent Activity": subjudul kelompok ("Hari ini", "Kemarin"), lalu daftar vertikal dengan garis pemisah kiri. Tiap entri: avatar inisial 36 px, teks "**{nama}** mencatat barang masuk **BM-…** ({n} item)" `text-sm`, dan waktu `text-xs text-subtle`. Sumber: 5 transaksi terbaru gabungan `stock_ins` + `stock_outs` (PRD FR-33).

---

## 7. Halaman lain (pola yang sama)

| Halaman | Isi | Catatan |
|---|---|---|
| **Login** | Kartu tengah `max-w-md`, latar `canvas`, logo di atas, form email + sandi (ikon mata), "Ingat saya", tombol **Masuk** `w-full`. Tanpa tautan daftar. | Galat: "Email atau kata sandi salah." |
| **Daftar Barang** | Header + tombol "+ Tambah Barang"; bar filter (cari, kategori, status, reset); tabel §5.8 | Status filter: `semua/aman/menipis/habis` |
| **Form Barang** | Satu kartu, grid 2 kolom di ≥ md, satu kolom di mobile; tombol Batal + Simpan di kanan bawah | Tidak ada isian stok awal |
| **Detail Barang** | Kartu atas: foto 160 px di kiri, detail 2 kolom + badge stok; kartu bawah: **Kartu Stok** (tabel dengan filter tanggal) | Jumlah `+10` hijau, `−5` merah (dengan tanda, bukan warna saja) |
| **Barang Masuk / Keluar (daftar)** | Bar filter tanggal + cari; tabel: No. Transaksi, Tanggal, Supplier/Tujuan, Jumlah Item, Oleh, Aksi (`eye`) | Tanpa tombol ubah/hapus |
| **Form Barang Masuk / Keluar** | Kartu header (no. otomatis, tanggal, supplier/alasan/tujuan) + kartu baris barang dinamis (Alpine) + total | Di form keluar, kolom "Tersedia" real-time dan galat per baris |
| **Detail Transaksi** | Kartu header + tabel item + tombol "Cetak" (opsional) | |
| **Kartu Stok** | Pilih barang (select pencarian) + rentang tanggal → tabel riwayat | |
| **Laporan** | Tab jenis laporan (pil: aktif `bg-primary-600 text-white`, lain `bg-white border`), filter, pratinjau tabel, tombol Unduh PDF/Excel | |
| **Pengguna** | Tabel + form (nama, email, peran, status aktif, sandi) | Hanya admin |
| **Profil** | Bawaan Breeze, dibungkus gaya kartu di atas | |
| **403 / 404 / 419** | Kartu tengah, ikon besar, penjelasan, tombol "Kembali ke Dashboard" | `resources/views/errors/` |

Baris dinamis form transaksi: tombol "+ Tambah Baris" (sekunder), tombol hapus baris ikon `x-mark`, minimal satu baris, barang yang sudah dipilih tidak muncul lagi di baris lain (PRD BR-03).

---

## 8. Responsif

| Lebar | Perilaku |
|---|---|
| ≥ 1536 px (`2xl`) | Sidebar tetap, dashboard dua kolom (utama + kanan 320 px), KPI 4 kolom |
| 1024–1535 px (`lg` s/d `xl`) | Sidebar tetap, dashboard satu kolom (panel kanan turun ke bawah), KPI 4 kolom mulai `xl` (1280 px), 2 kolom di bawahnya |
| 640–1023 px | Sidebar drawer, KPI 2 kolom, grafik tersusun satu kolom |
| < 640 px | KPI 1 kolom, tabel bisa digulir horizontal, form satu kolom, tombol aksi utama `w-full` |

Uji pada 360, 768, 1280 px. Tidak boleh ada gulir horizontal pada halaman (hanya di dalam tabel).

---

## 9. Aksesibilitas

- Setiap input punya `<label for>`; tombol ikon punya `aria-label`.
- Fokus terlihat: `focus-visible:ring-2 ring-primary-600/40`. Jangan `outline-none` tanpa pengganti.
- Kontras teks ≥ 4,5:1. Dilarang memakai `slate-400` ke bawah untuk teks pada latar putih (khusus sidebar gelap boleh `slate-300/400`).
- Status stok selalu berteks. Grafik diberi `aria-label` ringkas dan tabel data tersedia di halaman Laporan.
- Area sentuh ≥ 40 × 40 px. Hormati `prefers-reduced-motion` (matikan animasi grafik jika aktif).

---

## 10. Gerak (animasi)

Minimal dan halus: transisi `transition-colors duration-150` pada hover, drawer geser 200 ms, toast fade 150 ms, animasi masuk grafik 600 ms (`easeOutQuart`). Tidak ada animasi berulang atau mencolok.

---

## 11. Struktur komponen Blade yang harus ada

```
resources/views/
├── layouts/app.blade.php
├── layouts/guest.blade.php          (login)
├── components/
│   ├── sidebar.blade.php   topbar.blade.php   page-header.blade.php
│   ├── card.blade.php      stat-card.blade.php
│   ├── button.blade.php    input.blade.php    select.blade.php   label.blade.php   input-error.blade.php
│   ├── badge.blade.php     badge-stok.blade.php
│   ├── table.blade.php     empty-state.blade.php
│   ├── toast.blade.php     confirm-modal.blade.php
│   └── icon.blade.php      (pembungkus blade-heroicons)
├── dashboard.blade.php
└── errors/403.blade.php  404.blade.php  419.blade.php
resources/js/
├── app.js            (Alpine, font, impor chart)
└── charts/           (donut.js, area.js, sparkline.js)
```

Semua halaman **wajib** memakai komponen ini. Dilarang menulis ulang kelas tombol, kartu, atau badge secara manual di view.

---

## 12. Daftar larangan

- ❌ Warna di luar §3.1 (terutama lime/hijau stabilo dan hitam pekat #000 dari gambar referensi).
- ❌ Bootstrap, jQuery, CDN eksternal untuk CSS/JS/font.
- ❌ Gaya inline (`style="..."`) kecuali untuk lebar progress bar dan nilai dinamis grafik.
- ❌ Menu atau halaman yang tidak ada di PRD (Stores, Billing, Orders, Delivery).
- ❌ Teks antarmuka berbahasa Inggris (semua label, tombol, pesan memakai bahasa Indonesia).
- ❌ Data dummy hard-coded di view. Semua angka berasal dari database lewat controller.

---

## 13. Checklist visual sebelum dianggap selesai

- [ ] Dashboard secara tata letak mirip referensi: sidebar gelap, topbar, 4 KPI, donut + area chart, level stok + daftar, tabel, kolom kanan.
- [ ] Hanya satu warna aksen (biru). Hijau/amber/merah hanya muncul sebagai status.
- [ ] Semua kartu `rounded-card`, bayangan tipis, latar halaman `canvas`.
- [ ] Tabel, form, badge, tombol identik gayanya di semua halaman.
- [ ] Tampilan benar pada 360 / 768 / 1280 px.
- [ ] Format angka dan tanggal Indonesia (`02 Nov 2026`, `Rp 45.000`, `1.250`).
- [ ] Tidak ada galat di konsol browser, dan `npm run build` sukses.
