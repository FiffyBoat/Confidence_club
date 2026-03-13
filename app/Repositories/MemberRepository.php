<?php

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberRepository
{
    public function paginateWithSearch(
        ?string $search,
        int $perPage = 15,
        bool $withPayments = false,
        ?string $admissionFilter = null
    ): LengthAwarePaginator
    {
        $query = Member::query()
            ->withExists([
                'contributions as has_admission_fee' => fn ($builder) => $builder->where('type', 'Admission Fee'),
            ]);

        if ($search && $withPayments) {
            $query->with([
                'contributions' => fn ($builder) => $builder->orderBy('transaction_date', 'desc'),
                'loans.repayments' => fn ($builder) => $builder->orderBy('payment_date', 'desc'),
            ]);
        }

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('membership_id', 'like', '%'.$search.'%')
                    ->orWhere('full_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($withPayments) {
            if ($admissionFilter === 'paid') {
                $query->whereHas('contributions', fn ($builder) => $builder->where('type', 'Admission Fee'));
            } elseif ($admissionFilter === 'pending') {
                $query->whereDoesntHave('contributions', fn ($builder) => $builder->where('type', 'Admission Fee'));
            }
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }
}
