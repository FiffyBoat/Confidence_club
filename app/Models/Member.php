<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Member $member) {
            $isForceDeleting = $member->isForceDeleting();

            $member->contributions()->withTrashed()->get()->each(function ($contribution) use ($isForceDeleting) {
                if ($contribution->donation) {
                    $isForceDeleting ? $contribution->donation->forceDelete() : $contribution->donation->delete();
                }

                $isForceDeleting ? $contribution->forceDelete() : $contribution->delete();
            });

            $member->loans()->withTrashed()->get()->each(function ($loan) use ($isForceDeleting) {
                $loan->repayments()->withTrashed()->get()->each(function ($repayment) use ($isForceDeleting) {
                    $isForceDeleting ? $repayment->forceDelete() : $repayment->delete();
                });

                $isForceDeleting ? $loan->forceDelete() : $loan->delete();
            });

            $member->receipts()->withTrashed()->get()->each(function ($receipt) use ($isForceDeleting) {
                if ($receipt->pdf_path) {
                    Storage::disk('public')->delete($receipt->pdf_path);
                }

                $isForceDeleting ? $receipt->forceDelete() : $receipt->delete();
            });
        });
    }

    protected $fillable = [
        'membership_id',
        'full_name',
        'phone',
        'email',
        'status',
        'join_date',
        'birth_month',
        'birth_day',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }
}
