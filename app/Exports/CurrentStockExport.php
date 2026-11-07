<?php

namespace App\Exports;

use App\Services\ReportService;
use Illuminate\Support\Collection;

class CurrentStockExport extends BaseReportExport
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Nama',
            'Kategori',
            'Satuan',
            'Stok',
            'Stok Min.',
            'Status',
            'Lokasi Rak',
        ];
    }

    public function collection(): Collection
    {
        $data = app(ReportService::class)->getExportData('stok', $this->filters);
        $rows = collect();

        foreach ($data['rows']->values() as $idx => $product) {
            $statusLabel = match ($product->status_stok) {
                'habis' => 'Habis',
                'menipis' => 'Menipis',
                default => 'Aman',
            };

            $rows->push([
                (int) ($idx + 1),
                self::sanitizeText($product->kode_barang),
                self::sanitizeText($product->nama),
                self::sanitizeText($product->category?->nama),
                self::sanitizeText($product->unit?->singkatan),
                (int) $product->stok,
                (int) $product->stok_minimum,
                $statusLabel,
                self::sanitizeText($product->lokasi_rak),
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            '',
            'Total Jenis Barang',
            (int) ($data['summary']['totalJenisBarang'] ?? 0),
            '',
            '',
            '',
        ]);

        $rows->push([
            '',
            '',
            '',
            '',
            'Total Stok',
            (int) ($data['summary']['totalStok'] ?? 0),
            '',
            '',
            '',
        ]);

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'A' => self::NUMBER_FORMAT_THOUSANDS,
            'F' => self::NUMBER_FORMAT_THOUSANDS,
            'G' => self::NUMBER_FORMAT_THOUSANDS,
        ];
    }
}
