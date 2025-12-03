<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scan extends Model
{
    protected $fillable = [
        'input_url',
        'uploaded_zip_path',
        'score',
        'verdict',
        'status',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Get the findings for the scan.
     */
    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    /**
     * Get findings grouped by severity.
     */
    public function findingsBySeverity(): array
    {
        return [
            'critical' => $this->findings()->where('severity', 'critical')->get(),
            'high' => $this->findings()->where('severity', 'high')->get(),
            'medium' => $this->findings()->where('severity', 'medium')->get(),
            'low' => $this->findings()->where('severity', 'low')->get(),
        ];
    }

    /**
     * Calculate security score based on findings.
     */
    public function calculateScore(): int
    {
        $baseScore = 100;
        $deductions = 0;
        $bonusPoints = 0;

        $severityWeights = [
            'critical' => 30,  // Reduced from 40 - still serious but not instant fail
            'high' => 15,      // Reduced from 20
            'medium' => 8,     // Reduced from 10
            'low' => 2,        // Reduced from 3
            'positive' => -5,  // Bonus points for good security practices
        ];

        foreach ($this->findings as $finding) {
            $weight = $severityWeights[$finding->severity] ?? 0;
            if ($finding->severity === 'positive') {
                $bonusPoints += abs($weight); // Add bonus points
            } else {
                $deductions += $weight; // Deduct for issues
            }
        }

        // Score can go above 100 with bonus points, capped at 100
        return min(100, max(0, $baseScore - $deductions + $bonusPoints));
    }

    /**
     * Determine launch verdict based on score.
     */
    public function determineVerdict(): string
    {
        if ($this->score >= 80) {
            return 'Safe for Launch';
        } elseif ($this->score >= 50) {
            return 'Risky – Fix Recommended';
        } else {
            return 'Critical – Do Not Launch';
        }
    }

    /**
     * Get verdict badge color for UI.
     */
    public function getVerdictColorAttribute(): string
    {
        return match ($this->verdict) {
            'Safe for Launch' => 'green',
            'Risky – Fix Recommended' => 'yellow',
            'Critical – Do Not Launch' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if scan is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Check if scan is in progress.
     */
    public function isScanning(): bool
    {
        return in_array($this->status, ['pending', 'scanning']);
    }
}
