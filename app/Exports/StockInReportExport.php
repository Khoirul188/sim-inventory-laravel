<?php

namespace App\Exports;

use App\Services\ReportService;
use Illuminate\Support\Collection;

class StockInReportExport extends BaseReportExport
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Transaksi',
            'Supplier',
            'Kode',
            'Nama Barang',
            'Jumlah',
            'Harga Beli',
            'Subtotal',
        ];
    }

    public function collection(): Collection
    {
        $data = app(ReportService::class)->getExportData('masuk', $this->filters);
        $rows = collect();

        foreach ($data['rows']->values() as $idx => $item) {
            $jumlah = (int) $item->jumlah;
            $hargaBeli = (float) $item->harga_beli;
            $subtotal = $jumlah * $hargaBeli;

            $rows->push([
                (int) ($idx + 1),
                $item->stockIn?->tanggal?->translatedFormat('d M Y') ?? '',
                self::sanitizeText($item->stockIn?->no_transaksi),
                self::sanitizeText($item->stockIn?->supplier?->nama),
                self::sanitizeText($item->product?->kode_barang),
                self::sanitizeText($item->product?->nama),
                $jumlah,
                $hargaBeli,
                $subtotal,
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            'Total',
            (int) ($data['summary']['totalJumlah'] ?? 0),
            '',
            (float) ($data['summary']['totalNilai'] ?? 0),
        ]);

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'A' => self::NUMBER_FORMAT_THOUSANDS,
            'G' => self::NUMBER_FORMAT_THOUSANDS,
            'H' => self::NUMBER_FORMAT_THOUSANDS,
            'I' => self::NUMBER_FORMAT_THOUSANDS,
        ];
    }
}
