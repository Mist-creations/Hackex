<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use Illuminate\View\View;

class ScanHistoryController extends Controller
{
    /**
     * Display scan history.
     */
    public function __invoke(): View
    {
        $scans = Scan::with('findings')
            ->latest()
            ->paginate(20);

        return view('scan.history', [
            'scans' => $scans,
        ]);
    }
}
