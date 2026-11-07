<?php

namespace App\Exports;

use App\Services\ReportService;
use Illuminate\Support\Collection;

class StockOutReportExport extends BaseReportExport
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
            'Tujuan',
            'Alasan',
            'Kode',
            'Nama Barang',
            'Jumlah',
        ];
    }

    public function collection(): Collection
    {
        $data = app(ReportService::class)->getExportData('keluar', $this->filters);
        $rows = collect();

        foreach ($data['rows']->values() as $idx => $item) {
            $rows->push([
                (int) ($idx + 1),
                $item->stockOut?->tanggal?->translatedFormat('d M Y') ?? '—',
                self::sanitizeText($item->stockOut?->no_transaksi ?? '—'),
                self::sanitizeText($item->stockOut?->tujuan ?? '—'),
                self::sanitizeText($item->stockOut?->alasan_label ?? '—'),
                self::sanitizeText($item->product?->kode_barang ?? '—'),
                self::sanitizeText($item->product?->nama ?? '—'),
                (int) $item->jumlah,
            ]);
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'A' => self::NUMBER_FORMAT_THOUSANDS,
            'H' => self::NUMBER_FORMAT_THOUSANDS,
        ];
    }
}
