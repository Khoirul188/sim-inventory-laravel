<?php

namespace App\Exports;

use App\Services\ReportService;
use Illuminate\Support\Collection;

class StockCardExport extends BaseReportExport
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kode',
            'Nama Barang',
            'Referensi',
            'Jenis',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Oleh',
        ];
    }

    public function collection(): Collection
    {
        $data = app(ReportService::class)->getExportData('kartu-stok', $this->filters);
        $rows = collect();

        foreach ($data['rows']->values() as $idx => $movement) {
            $jenisLabel = $movement->tipe === 'in' ? 'Masuk (IN)' : 'Keluar (OUT)';

            $rows->push([
                (int) ($idx + 1),
                $movement->created_at?->translatedFormat('d M Y H:i') ?? '',
                self::sanitizeText($movement->product?->kode_barang),
                self::sanitizeText($movement->product?->nama),
                self::sanitizeText($movement->nomor_referensi),
                $jenisLabel,
                (int) $movement->jumlah,
                (int) $movement->stok_sebelum,
                (int) $movement->stok_sesudah,
                self::sanitizeText($movement->user?->name),
            ]);
        }

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
