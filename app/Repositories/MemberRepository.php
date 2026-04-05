<?php

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
            $this->applySearchFilter($query, $search);
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

    public function searchSuggestions(?string $search, int $limit = 8): Collection
    {
        $search = trim((string) $search);

        if ($search === '') {
            return collect();
        }

        $query = Member::query()
            ->select('id', 'membership_id', 'full_name', 'phone', 'email', 'status')
            ->orderBy('full_name')
            ->limit($limit);

        $this->applySearchFilter($query, $search);

        return $query->get()->map(function (Member $member) {
            return [
                'id' => $member->id,
                'membership_id' => $member->membership_id,
                'full_name' => $member->full_name,
                'phone' => $member->phone,
                'email' => $member->email,
                'status' => $member->status,
                'search_value' => $member->membership_id,
                'search_text' => trim($member->full_name.' '.$member->membership_id),
            ];
        });
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search) {
            $builder->where('membership_id', 'like', '%'.$search.'%')
                ->orWhere('full_name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%');
        });
    }
}
