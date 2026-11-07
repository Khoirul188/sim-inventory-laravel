<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $meta['title'] }} - {{ $appName }}</title>
    <style>
        @page {
            margin: 22mm 15mm 20mm 15mm;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0F172A;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0F172A;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-top {
            width: 100%;
        }
        .system-name {
            font-size: 11px;
            font-weight: bold;
            color: #2563EB;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #0F172A;
            margin: 2px 0 4px 0;
        }
        .meta-table {
            width: 100%;
            font-size: 9.5px;
            color: #334155;
            margin-top: 4px;
        }
        .meta-table td {
            padding: 1px 0;
        }
        .meta-right {
            text-align: right;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
            margin-bottom: 14px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tfoot {
            display: table-footer-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data-table th {
            background-color: #F1F5F9;
            color: #334155;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 7px;
            border: 1px solid #CBD5E1;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 7px;
            border: 1px solid #E2E8F0;
            vertical-align: top;
            font-size: 9.5px;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .font-bold {
            font-weight: bold;
        }
        .summary-box {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            padding: 10px 12px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .summary-title {
            font-size: 10.5px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #0F172A;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .summary-table td {
            padding: 2px 4px;
        }
        .pdf-footer-note {
            position: fixed;
            bottom: -12mm;
            left: 0;
            font-size: 8.5px;
            color: #64748B;
        }
    </style>
</head>
<body>
    <!-- Kop Laporan (PRD §15) -->
    <div class="header">
        <div class="system-name">{{ $appName }}</div>
        <div class="report-title">{{ $meta['title'] }}</div>
        @php
            $cleanFilterSummary = str_replace([' · ', ' — '], [' | ', ' - '], $meta['filterSummary']);
            $cleanFilterSummary = preg_replace('/^Periode:\s*/i', '', $cleanFilterSummary);
        @endphp
        <table class="meta-table">
            <tr>
                <td><strong>Filter / Periode:</strong> {{ $cleanFilterSummary }}</td>
                <td class="meta-right"><strong>Tanggal Cetak:</strong> {{ $meta['printedAt'] }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="meta-right"><strong>Dicetak oleh:</strong> {{ $printerName }}</td>
            </tr>
        </table>
    </div>

    <div class="pdf-footer-note">
        {{ $appName }} | Dicetak pada {{ $meta['printedAt'] }} oleh {{ $printerName }}
    </div>

    <!-- Isi Tabel Laporan -->
    @if ($rows->isEmpty())
        <p style="text-align: center; padding: 24px 0; color: #64748B;">Tidak ada data yang sesuai dengan filter laporan.</p>
    @elseif ($jenis === 'stok')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22px;" class="text-center">No</th>
                    <th style="width: 58px;">Kode</th>
                    <th style="width: 34%;">Nama Barang</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 36px;">Satuan</th>
                    <th style="width: 42px;" class="text-right">Stok</th>
                    <th style="width: 48px;" class="text-right">Stok Min.</th>
                    <th style="width: 48px;">Status</th>
                    <th style="width: 42px;">Lokasi Rak</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows->values() as $idx => $product)
                    @php
                        $statusLabel = match ($product->status_stok) {
                            'habis' => 'Habis',
                            'menipis' => 'Menipis',
                            default => 'Aman',
                        };
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $product->kode_barang }}</td>
                        <td>{{ $product->nama }}</td>
                        <td>{{ $product->category?->nama ?? '-' }}</td>
                        <td>{{ $product->unit?->singkatan ?? '-' }}</td>
                        <td class="text-right font-bold">{{ format_angka($product->stok) }}</td>
                        <td class="text-right">{{ format_angka($product->stok_minimum) }}</td>
                        <td>{{ $statusLabel }}</td>
                        <td>{{ $product->lokasi_rak ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Ringkasan Laporan Stok Saat Ini -->
        <div class="summary-box">
            <div class="summary-title">Ringkasan Laporan</div>
            <table class="summary-table">
                <tr>
                    <td style="width: 180px;">Total Jenis Barang</td>
                    <td class="font-bold">: {{ format_angka($summary['totalJenisBarang']) }} jenis</td>
                </tr>
                <tr>
                    <td>Total Seluruh Stok</td>
                    <td class="font-bold">: {{ format_angka($summary['totalStok']) }} unit</td>
                </tr>
            </table>

            @if (! empty($summary['subtotalPerKategori']))
                <div style="margin-top: 8px; font-weight: bold; font-size: 9.5px;">Subtotal per Kategori:</div>
                <table class="summary-table" style="margin-top: 3px;">
                    @foreach ($summary['subtotalPerKategori'] as $sub)
                        <tr>
                            <td style="width: 240px;">- {{ $sub['kategori'] }} ({{ format_angka($sub['jumlah_jenis']) }} jenis)</td>
                            <td class="font-bold">: {{ format_angka($sub['total_stok']) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

    @elseif ($jenis === 'masuk')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 26px;" class="text-center">No</th>
                    <th style="width: 72px;">Tanggal</th>
                    <th style="width: 105px;">No. Transaksi</th>
                    <th>Supplier</th>
                    <th style="width: 68px;">Kode</th>
                    <th>Nama Barang</th>
                    <th style="width: 65px;" class="text-right">Jumlah</th>
                    <th style="width: 85px;" class="text-right">Harga Beli</th>
                    <th style="width: 95px;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows->values() as $idx => $item)
                    @php
                        $subtotal = (int) $item->jumlah * (float) $item->harga_beli;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $item->stockIn?->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $item->stockIn?->no_transaksi ?? '-' }}</td>
                        <td>{{ $item->stockIn?->supplier?->nama ?? '-' }}</td>
                        <td>{{ $item->product?->kode_barang ?? '-' }}</td>
                        <td>
                            {{ $item->product?->nama ?? '-' }}
                            @if ($item->product?->trashed())
                                (Terhapus)
                            @endif
                        </td>
                        <td class="text-right font-bold">{{ format_angka($item->jumlah) }} {{ $item->product?->unit?->singkatan }}</td>
                        <td class="text-right">{{ format_rupiah($item->harga_beli) }}</td>
                        <td class="text-right font-bold">{{ format_rupiah($subtotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-title">Ringkasan Laporan Barang Masuk</div>
            <table class="summary-table">
                <tr>
                    <td style="width: 180px;">Total Jumlah Barang Masuk</td>
                    <td class="font-bold">: {{ format_angka($summary['totalJumlah']) }} unit</td>
                </tr>
                <tr>
                    <td>Total Nilai Pembelian</td>
                    <td class="font-bold">: {{ format_rupiah($summary['totalNilai']) }}</td>
                </tr>
            </table>
        </div>

    @elseif ($jenis === 'keluar')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 26px;" class="text-center">No</th>
                    <th style="width: 75px;">Tanggal</th>
                    <th style="width: 110px;">No. Transaksi</th>
                    <th>Tujuan / Penerima</th>
                    <th style="width: 85px;">Alasan</th>
                    <th style="width: 72px;">Kode</th>
                    <th>Nama Barang</th>
                    <th style="width: 75px;" class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows->values() as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $item->stockOut?->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $item->stockOut?->no_transaksi ?? '-' }}</td>
                        <td>{{ $item->stockOut?->tujuan ?: '-' }}</td>
                        <td>{{ $item->stockOut?->alasan_label ?? '-' }}</td>
                        <td>{{ $item->product?->kode_barang ?? '-' }}</td>
                        <td>
                            {{ $item->product?->nama ?? '-' }}
                            @if ($item->product?->trashed())
                                (Terhapus)
                            @endif
                        </td>
                        <td class="text-right font-bold">{{ format_angka($item->jumlah) }} {{ $item->product?->unit?->singkatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-title">Ringkasan Laporan Barang Keluar</div>
            <table class="summary-table">
                <tr>
                    <td style="width: 180px;">Total Jumlah Barang Keluar</td>
                    <td class="font-bold">: {{ format_angka($summary['totalJumlah']) }} unit</td>
                </tr>
            </table>
        </div>

    @elseif ($jenis === 'minimum')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28px;" class="text-center">No</th>
                    <th style="width: 72px;">Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th style="width: 65px;" class="text-right">Stok</th>
                    <th style="width: 65px;" class="text-right">Stok Min.</th>
                    <th style="width: 75px;" class="text-right">Kekurangan</th>
                    <th style="width: 65px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows->values() as $idx => $product)
                    @php
                        $kekurangan = \App\Services\ReportService::calculateShortage((int) $product->stok, (int) $product->stok_minimum);
                        $statusLabel = $product->status_stok === 'habis' ? 'Habis' : 'Menipis';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $product->kode_barang }}</td>
                        <td>{{ $product->nama }}</td>
                        <td>{{ $product->category?->nama ?? '-' }}</td>
                        <td class="text-right font-bold">{{ format_angka($product->stok) }} {{ $product->unit?->singkatan }}</td>
                        <td class="text-right">{{ format_angka($product->stok_minimum) }} {{ $product->unit?->singkatan }}</td>
                        <td class="text-right font-bold">{{ format_angka($kekurangan) }} {{ $product->unit?->singkatan }}</td>
                        <td>{{ $statusLabel }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-title">Ringkasan Laporan Stok Minimum</div>
            <table class="summary-table">
                <tr>
                    <td style="width: 210px;">Jumlah Barang Menipis (Termasuk Habis)</td>
                    <td class="font-bold">: {{ format_angka($summary['jumlahMenipis']) }} jenis</td>
                </tr>
                <tr>
                    <td>Jumlah Barang Habis (Stok = 0)</td>
                    <td class="font-bold">: {{ format_angka($summary['jumlahHabis']) }} jenis</td>
                </tr>
                <tr>
                    <td>Total Kekurangan Stok</td>
                    <td class="font-bold">: {{ format_angka($summary['totalKekurangan']) }} unit</td>
                </tr>
            </table>
        </div>

    @elseif ($jenis === 'kartu-stok')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 26px;" class="text-center">No</th>
                    <th style="width: 90px;">Tanggal</th>
                    <th style="width: 70px;">Kode</th>
                    <th>Nama Barang</th>
                    <th style="width: 105px;">Referensi</th>
                    <th style="width: 55px;">Jenis</th>
                    <th style="width: 60px;" class="text-right">Jumlah</th>
                    <th style="width: 60px;" class="text-right">Sebelum</th>
                    <th style="width: 60px;" class="text-right">Sesudah</th>
                    <th style="width: 90px;">Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows->values() as $idx => $movement)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $movement->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        <td>{{ $movement->product?->kode_barang ?? '-' }}</td>
                        <td>{{ $movement->product?->nama ?? '-' }}</td>
                        <td>{{ $movement->nomor_referensi }}</td>
                        <td>{{ $movement->tipe === 'in' ? 'IN' : 'OUT' }}</td>
                        <td class="text-right font-bold">{{ $movement->tipe === 'in' ? '+' : '-' }}{{ format_angka($movement->jumlah) }}</td>
                        <td class="text-right">{{ format_angka($movement->stok_sebelum) }}</td>
                        <td class="text-right font-bold">{{ format_angka($movement->stok_sesudah) }}</td>
                        <td>{{ $movement->user?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-title">Ringkasan Kartu Stok</div>
            <table class="summary-table">
                <tr>
                    <td style="width: 180px;">Total Baris Mutasi</td>
                    <td class="font-bold">: {{ format_angka($summary['totalBaris']) }} baris</td>
                </tr>
                <tr>
                    <td>Total Masuk (IN)</td>
                    <td class="font-bold">: +{{ format_angka($summary['totalMasuk']) }} unit</td>
                </tr>
                <tr>
                    <td>Total Keluar (OUT)</td>
                    <td class="font-bold">: -{{ format_angka($summary['totalKeluar']) }} unit</td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>
