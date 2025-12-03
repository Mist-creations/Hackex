<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use App\Jobs\ProcessScan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    /**
     * Store a new scan request.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info("Store method called", [
            'has_file' => $request->hasFile('zip_file'),
            'has_url' => $request->has('url'),
            'all_keys' => array_keys($request->all()),
        ]);
        
        $validator = Validator::make($request->all(), [
            'url' => 'nullable|url|required_without:zip_file',
            'zip_file' => 'nullable|file|mimes:zip|max:51200|required_without:url', // 50MB max
            'consent' => 'required|accepted',
        ], [
            'url.required_without' => 'Please provide either a URL or upload a ZIP file.',
            'zip_file.required_without' => 'Please provide either a URL or upload a ZIP file.',
            'consent.required' => 'You must confirm that you own this website or have permission to scan it.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Rate limiting disabled for testing
        // TODO: Re-enable in production by uncommenting below
        /*
        $ipAddress = $request->ip();
        $rateLimitKey = 'scan_rate_limit:' . $ipAddress;
        $scanCount = Cache::get($rateLimitKey, 0);

        if ($scanCount >= 5) {
            return back()->withErrors([
                'rate_limit' => 'You have reached the maximum number of scans per hour. Please try again later.',
            ])->withInput();
        }

        Cache::put($rateLimitKey, $scanCount + 1, now()->addHour());
        */

        // Generate unique session-based scan ID (no database storage)
        $scanId = Str::uuid()->toString();
        
        // Store scan data in cache (expires in 1 hour)
        $scanData = [
            'id' => $scanId,
            'input_url' => $request->input('url'),
            'status' => 'pending',
            'created_at' => now()->toIso8601String(),
        ];

        // Handle ZIP file upload
        if ($request->hasFile('zip_file')) {
            $file = $request->file('zip_file');
            
            Log::info("ZIP file upload received", [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage(),
            ]);
            
            if (!$file->isValid()) {
                Log::error("Invalid file upload", [
                    'error' => $file->getError(),
                    'message' => $file->getErrorMessage(),
                ]);
                
                return back()->withErrors([
                    'zip_file' => 'File upload failed: ' . $file->getErrorMessage(),
                ])->withInput();
            }
            
            try {
                $path = $file->store('uploads');
                $scanData['uploaded_zip_path'] = $path;
                
                Log::info("ZIP file stored", [
                    'path' => $path,
                    'full_path' => storage_path('app/' . $path),
                    'exists' => file_exists(storage_path('app/' . $path)),
                    'size_on_disk' => file_exists(storage_path('app/' . $path)) ? filesize(storage_path('app/' . $path)) : 0,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to store ZIP file", [
                    'error' => $e->getMessage(),
                ]);
                
                return back()->withErrors([
                    'zip_file' => 'Failed to save uploaded file. Please try again.',
                ])->withInput();
            }
        }

        // Store in cache (extended to 2 hours for viewing results)
        Cache::put('scan:' . $scanId, $scanData, now()->addHours(2));

        // Create temporary scan record for job processing (will be deleted after scan)
        $scan = Scan::create([
            'input_url' => $scanData['input_url'],
            'uploaded_zip_path' => $scanData['uploaded_zip_path'] ?? null,
            'status' => 'pending',
        ]);

        // Store mapping between UUID and database ID in cache
        Cache::put('scan_mapping:' . $scanId, $scan->id, now()->addHours(2));
        
        // Store reverse mapping for job to update cache
        $reverseMapping = Cache::get('scan_mapping_reverse:' . $scan->id, []);
        $reverseMapping[] = $scanId;
        Cache::put('scan_mapping_reverse:' . $scan->id, $reverseMapping, now()->addHours(2));

        // Dispatch scan job
        ProcessScan::dispatch($scan);

        return redirect()->route('scan.show', $scanId)
            ->with('success', 'Scan started! Results will be automatically deleted after 2 hours for your privacy.');
    }

    /**
     * Display scan results.
     */
    public function show(string $scanId): View
    {
        // Get scan from cache
        $scanData = Cache::get('scan:' . $scanId);
        
        if (!$scanData) {
            abort(404, 'Scan not found or expired. Results are automatically deleted after 2 hours for your privacy.');
        }

        // Get actual scan from database using mapping
        $dbScanId = Cache::get('scan_mapping:' . $scanId);
        $scan = Scan::find($dbScanId);

        if (!$scan) {
            abort(404, 'Scan not found or expired.');
        }

        $scan->load('findings');

        return view('scan.show', [
            'scan' => $scan,
            'scanId' => $scanId,
            'findingsBySeverity' => $scan->findingsBySeverity(),
        ]);
    }

    /**
     * Get scan status (for AJAX polling).
     */
    public function status(string $scanId): JsonResponse
    {
        // Try to get scan from cache first
        $scanData = Cache::get('scan:' . $scanId);
        
        // Get actual scan from database using mapping
        $dbScanId = Cache::get('scan_mapping:' . $scanId);
        $scan = $dbScanId ? Scan::find($dbScanId) : null;

        // If no cache and no database record, scan truly doesn't exist
        if (!$scanData && !$scan) {
            return response()->json([
                'error' => 'Scan expired or not found',
                'status' => 'expired'
            ], 404);
        }

        // If no database record but cache exists, it's a problem
        if (!$scan) {
            return response()->json([
                'error' => 'Scan not found in database',
                'status' => 'not_found'
            ], 404);
        }

        // Return scan status from database (source of truth)
        return response()->json([
            'status' => $scan->status,
            'score' => $scan->score,
            'verdict' => $scan->verdict,
            'is_complete' => $scan->isComplete(),
            'findings_count' => $scan->findings()->count(),
        ]);
    }
}
