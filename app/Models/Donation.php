<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'special_contribution_id',
        'special_contribution_purpose',
        'donated_amount',
        'remaining_amount',
        'donation_purpose',
        'remaining_use',
        'donation_date',
        'recorded_by',
    ];

    protected $casts = [
        'donated_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'donation_date' => 'date',
    ];

    public function specialContribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class, 'special_contribution_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
