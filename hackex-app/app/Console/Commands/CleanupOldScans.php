<?php

namespace App\Console\Commands;

use App\Models\Scan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldScans extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scans:cleanup {--hours=1 : Delete scans older than this many hours}';

    /**
     * The console command description.
     */
    protected $description = 'Delete old scans and uploaded files for privacy (default: 1 hour)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $this->info("Cleaning up scans older than {$hours} hour(s)...");

        // Find old scans
        $oldScans = Scan::where('created_at', '<', now()->subHours($hours))->get();
        $count = $oldScans->count();

        if ($count === 0) {
            $this->info('No scans to clean up.');
            return Command::SUCCESS;
        }

        $filesDeleted = 0;
        $scansDeleted = 0;

        foreach ($oldScans as $scan) {
            // Delete uploaded ZIP file if exists
            if ($scan->uploaded_zip_path && Storage::exists($scan->uploaded_zip_path)) {
                Storage::delete($scan->uploaded_zip_path);
                $filesDeleted++;
            }

            // Delete findings first (foreign key constraint)
            $scan->findings()->delete();

            // Delete scan
            $scan->delete();
            $scansDeleted++;
        }

        $this->info("Deleted {$scansDeleted} scan(s) and {$filesDeleted} uploaded file(s).");
        $this->info('Privacy cleanup complete!');

        return Command::SUCCESS;
    }
}
