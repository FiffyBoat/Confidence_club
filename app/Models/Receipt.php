<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_CONTRIBUTION = 'contribution';
    public const TYPE_INCOME = 'income';
    public const TYPE_LOAN_REPAYMENT = 'loan_repayment';

    protected $fillable = [
        'receipt_number',
        'member_id',
        'reference_type',
        'reference_id',
        'amount',
        'generated_by',
        'pdf_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
