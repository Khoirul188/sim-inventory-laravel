<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard lengkap dengan seluruh metrik, grafik, dan panel (PRD §11.4 & DESIGN §6).
     */
    public function index(Request $request, DashboardService $dashboardService): View
    {
        $range = $request->string('range', '12m')->toString();
        $search = $request->input('search');

        return view('dashboard', $dashboardService->getDashboardData($range, $search));
    }
}
