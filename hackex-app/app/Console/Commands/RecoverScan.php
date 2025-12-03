<?php

namespace App\Console\Commands;

use App\Models\Scan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RecoverScan extends Command
{
    protected $signature = 'scans:recover {scan_id} {--uuid= : Specific UUID to use}';
    protected $description = 'Recover a scan by recreating its cache entry';

    public function handle(): int
    {
        $scanId = $this->argument('scan_id');
        $scan = Scan::find($scanId);

        if (!$scan) {
            $this->error("Scan #{$scanId} not found.");
            return Command::FAILURE;
        }

        // Generate or use provided UUID
        $uuid = $this->option('uuid') ?: Str::uuid()->toString();

        // Create cache entry
        $scanData = [
            'id' => $uuid,
            'input_url' => $scan->input_url,
            'status' => $scan->status,
            'created_at' => $scan->created_at->toIso8601String(),
        ];

        if ($scan->uploaded_zip_path) {
            $scanData['uploaded_zip_path'] = $scan->uploaded_zip_path;
        }

        // Store in cache
        Cache::put('scan:' . $uuid, $scanData, now()->addHours(2));
        Cache::put('scan_mapping:' . $uuid, $scan->id, now()->addHours(2));

        // Store reverse mapping
        $reverseMapping = Cache::get('scan_mapping_reverse:' . $scan->id, []);
        $reverseMapping[] = $uuid;
        Cache::put('scan_mapping_reverse:' . $scan->id, $reverseMapping, now()->addHours(2));

        $url = url('/scan/' . $uuid);
        
        $this->info("Scan #{$scanId} recovered!");
        $this->info("UUID: {$uuid}");
        $this->info("URL: {$url}");
        $this->info("Status: {$scan->status}");
        $this->info("Score: {$scan->score}");
        $this->info("Verdict: {$scan->verdict}");

        return Command::SUCCESS;
    }
}
