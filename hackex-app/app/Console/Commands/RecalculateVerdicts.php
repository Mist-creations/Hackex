<?php

namespace App\Console\Commands;

use App\Models\Scan;
use Illuminate\Console\Command;

class RecalculateVerdicts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scans:recalculate-verdicts';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate verdicts for all scans based on their scores';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Recalculating verdicts for all scans...');

        $scans = Scan::whereNotNull('score')->get();
        $updated = 0;

        foreach ($scans as $scan) {
            $oldVerdict = $scan->verdict;
            $newVerdict = $scan->determineVerdict();

            if ($oldVerdict !== $newVerdict) {
                $scan->update(['verdict' => $newVerdict]);
                $updated++;
                
                $this->line("Scan #{$scan->id}: {$oldVerdict} → {$newVerdict}");
            }
        }

        $this->info("Updated {$updated} scan(s).");
        $this->info('Done!');

        return Command::SUCCESS;
    }
}
