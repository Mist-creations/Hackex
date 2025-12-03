<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(): View
    {
        $scans = Scan::with('findings')
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_scans' => Scan::count(),
            'safe_scans' => Scan::where('verdict', 'Safe for Launch')->count(),
            'risky_scans' => Scan::where('verdict', 'Risky – Fix Recommended')->count(),
            'critical_scans' => Scan::where('verdict', 'Critical – Do Not Launch')->count(),
        ];

        return view('dashboard', [
            'scans' => $scans,
            'stats' => $stats,
        ]);
    }
}
