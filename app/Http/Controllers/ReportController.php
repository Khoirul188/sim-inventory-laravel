<?php

namespace App\Http\Controllers;

use App\Exports\CurrentStockExport;
use App\Exports\MinimumStockExport;
use App\Exports\StockCardExport;
use App\Exports\StockInReportExport;
use App\Exports\StockOutReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Tampilkan pratinjau laporan di layar dengan filter dan paginasi (PRD §15 & §16).
     */
    public function show(Request $request, string $jenis): View
    {
        if (! $this->reportService->isValidType($jenis)) {
            abort(404);
        }

        $filters = $this->reportService->validateFilters($jenis, $request->query(), false);
        $data = $this->reportService->getPreviewData($jenis, $filters);

        return view('reports.show', $data);
    }

    /**
     * Unduh laporan dalam format PDF dengan kop lengkap dan penomoran halaman (PRD §15 & FR-44).
     */
    public function pdf(Request $request, string $jenis): Response
    {
        if (! $this->reportService->isValidType($jenis)) {
            abort(404);
        }

        $filters = $this->reportService->validateFilters($jenis, $request->query(), true);
        $data = $this->reportService->getExportData($jenis, $filters);
        $data['printerName'] = $request->user()?->name ?? '—';
        $data['appName'] = config('app.name', 'SIM Inventory');

        $orientation = $data['meta']['orientation'] ?? 'portrait';

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', $orientation);

        // Render lebih dulu untuk menyematkan teks kaki "Halaman x dari y" di setiap halaman kanvas PDF
        $dompdf = $pdf->getDomPDF();
        $pdf->render();

        $canvas = $dompdf->getCanvas();
        $x = $orientation === 'landscape' ? 720 : 480;
        $y = $orientation === 'landscape' ? 565 : 815;
        $canvas->page_text($x, $y, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', null, 8.5, [0.39, 0.45, 0.55]);

        $dateStr = now()->timezone('Asia/Jakarta')->format('Ymd');
        $filename = "laporan-{$jenis}-{$dateStr}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Unduh laporan dalam format Excel (.xlsx) dengan format angka ribuan & proteksi rumus (PRD §15 & FR-44).
     */
    public function excel(Request $request, string $jenis): BinaryFileResponse
    {
        if (! $this->reportService->isValidType($jenis)) {
            abort(404);
        }

        $filters = $this->reportService->validateFilters($jenis, $request->query(), true);

        $export = match ($jenis) {
            'stok' => new CurrentStockExport($filters),
            'masuk' => new StockInReportExport($filters),
            'keluar' => new StockOutReportExport($filters),
            'minimum' => new MinimumStockExport($filters),
            'kartu-stok' => new StockCardExport($filters),
        };

        $dateStr = now()->timezone('Asia/Jakarta')->format('Ymd');
        $filename = "laporan-{$jenis}-{$dateStr}.xlsx";

        return Excel::download($export, $filename);
    }
}
