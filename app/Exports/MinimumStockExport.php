<?php

namespace App\Exports;

use App\Services\ReportService;
use Illuminate\Support\Collection;

class MinimumStockExport extends BaseReportExport
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
            'Stok',
            'Stok Min.',
            'Kekurangan',
            'Status',
        ];
    }

    public function collection(): Collection
    {
        $data = app(ReportService::class)->getExportData('minimum', $this->filters);
        $rows = collect();

        foreach ($data['rows']->values() as $idx => $product) {
            $kekurangan = ReportService::calculateShortage((int) $product->stok, (int) $product->stok_minimum);
            $statusLabel = $product->status_stok === 'habis' ? 'Habis' : 'Menipis';

            $rows->push([
                (int) ($idx + 1),
                self::sanitizeText($product->kode_barang),
                self::sanitizeText($product->nama),
                self::sanitizeText($product->category?->nama),
                (int) $product->stok,
                (int) $product->stok_minimum,
                (int) $kekurangan,
                $statusLabel,
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            'Jumlah Barang Menipis (Termasuk Habis)',
            '',
            '',
            (int) ($data['summary']['jumlahMenipis'] ?? 0),
            '',
        ]);

        $rows->push([
            '',
            '',
            '',
            'Jumlah Barang Habis (Stok = 0)',
            '',
            '',
            (int) ($data['summary']['jumlahHabis'] ?? 0),
            '',
        ]);

        $rows->push([
            '',
            '',
            '',
            'Total Kekurangan Stok',
            '',
            '',
            (int) ($data['summary']['totalKekurangan'] ?? 0),
            '',
        ]);

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'A' => self::NUMBER_FORMAT_THOUSANDS,
            'E' => self::NUMBER_FORMAT_THOUSANDS,
            'F' => self::NUMBER_FORMAT_THOUSANDS,
            'G' => self::NUMBER_FORMAT_THOUSANDS,
        ];
    }
}
