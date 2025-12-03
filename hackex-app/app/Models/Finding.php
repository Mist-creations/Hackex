<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finding extends Model
{
    protected $fillable = [
        'scan_id',
        'type',
        'title',
        'severity',
        'location',
        'evidence',
        'ai_explanation',
        'ai_attack_scenario',
        'fix_recommendation',
    ];

    /**
     * Get the scan that owns the finding.
     */
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    /**
     * Get severity badge color for UI.
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get severity icon for UI.
     */
    public function getSeverityIconAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '🔴',
            'high' => '🟠',
            'medium' => '🟡',
            'low' => '🔵',
            default => '⚪',
        };
    }

    /**
     * Get type badge for UI.
     */
    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'runtime' => 'Live Server',
            'static' => 'Source Code',
            default => 'Unknown',
        };
    }
}
