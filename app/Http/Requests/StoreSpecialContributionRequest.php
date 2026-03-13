<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'min:100'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,momo,bank,card'],
            'transaction_date' => ['required', 'date'],
        ];
    }
}
