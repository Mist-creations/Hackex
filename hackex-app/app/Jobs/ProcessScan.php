<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Finding;
use App\Services\RuntimeScanner;
use App\Services\StaticScanner;
use App\Services\AIExplanationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProcessScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3; // Allow 3 attempts before failing

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Scan $scan
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting scan #{$this->scan->id}");

            // Update scan status to scanning
            $this->scan->update(['status' => 'scanning']);
            
            // Update cache status for UUID-based access
            $this->updateCacheStatus('scanning');

            $allFindings = [];

            // Run runtime scan if URL provided
            if ($this->scan->input_url) {
                Log::info("Running runtime scan for: {$this->scan->input_url}");
                $runtimeScanner = new RuntimeScanner();
                $runtimeFindings = $runtimeScanner->scan($this->scan->input_url);
                $allFindings = array_merge($allFindings, $runtimeFindings);
            }

            // Run static scan if ZIP provided
            if ($this->scan->uploaded_zip_path) {
                Log::info("Running static scan for: {$this->scan->uploaded_zip_path}");
                $staticScanner = new StaticScanner();
                $staticFindings = $staticScanner->scan($this->scan->uploaded_zip_path);
                $allFindings = array_merge($allFindings, $staticFindings);
            }

            Log::info("Found " . count($allFindings) . " security issues");

            // Generate AI explanations for findings
            if (!empty($allFindings)) {
                $aiService = new AIExplanationService();
                $findingsWithExplanations = $aiService->generateBatchExplanations($allFindings);

                // Store findings in database
                foreach ($findingsWithExplanations as $finding) {
                    Finding::create([
                        'scan_id' => $this->scan->id,
                        'type' => $finding['type'],
                        'title' => $finding['title'],
                        'severity' => $finding['severity'],
                        'location' => $finding['location'] ?? null,
                        'evidence' => $finding['evidence'] ?? null,
                        'ai_explanation' => $finding['explanation'] ?? null,
                        'ai_attack_scenario' => $finding['attack_scenario'] ?? null,
                        'fix_recommendation' => $finding['fix_recommendation'] ?? null,
                    ]);
                }
            }

            // Calculate score and verdict
            $score = $this->scan->calculateScore();
            
            // Update scan with results
            $this->scan->update([
                'score' => $score,
                'status' => 'done',
            ]);
            
            // Calculate verdict AFTER score is saved
            $verdict = $this->scan->determineVerdict();
            $this->scan->update(['verdict' => $verdict]);
            
            // Update cache status for UUID-based access
            $this->updateCacheStatus('done');

            Log::info("Scan #{$this->scan->id} completed. Score: {$score}, Verdict: {$verdict}");

        } catch (\Exception $e) {
            Log::error("Scan #{$this->scan->id} failed: " . $e->getMessage());
            
            $this->scan->update([
                'status' => 'failed',
            ]);

            throw $e;
        }
    }

    /**
     * Update cache status for UUID-based access.
     */
    protected function updateCacheStatus(string $status): void
    {
        // Find all cache keys for this scan ID
        $cacheKeys = Cache::get('scan_mapping_reverse:' . $this->scan->id, []);
        
        foreach ($cacheKeys as $uuid) {
            $scanData = Cache::get('scan:' . $uuid);
            if ($scanData) {
                $scanData['status'] = $status;
                Cache::put('scan:' . $uuid, $scanData, now()->addHours(2)); // Extended to 2 hours
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Scan job failed for scan #{$this->scan->id}: " . $exception->getMessage());
        
        $this->scan->update([
            'status' => 'failed',
        ]);
        
        // Update cache status
        $this->updateCacheStatus('failed');
    }
}
