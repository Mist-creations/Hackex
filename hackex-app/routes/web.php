<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScanHistoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', HomeController::class)->name('home');

// Debug route to test uploads
Route::post('/test-upload', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info("TEST UPLOAD ENDPOINT HIT", [
        'has_file' => $request->hasFile('zip_file'),
        'all_files' => $request->allFiles(),
        'all_input' => array_keys($request->all()),
    ]);
    
    if ($request->hasFile('zip_file')) {
        $file = $request->file('zip_file');
        return response()->json([
            'success' => true,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'valid' => $file->isValid(),
            'error' => $file->getError(),
        ]);
    }
    
    return response()->json(['success' => false, 'message' => 'No file uploaded']);
})->name('test.upload');

// Scan routes (using UUID for privacy - no sequential IDs)
Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
Route::get('/scan/{scanId}', [ScanController::class, 'show'])->name('scan.show');
Route::get('/scan/{scanId}/status', [ScanController::class, 'status'])->name('scan.status');

// Authenticated routes (optional for MVP)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/scan-history', ScanHistoryController::class)->name('scan.history');
});
