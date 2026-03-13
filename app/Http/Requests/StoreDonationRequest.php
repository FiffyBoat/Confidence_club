<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'special_contribution_purpose' => ['required', 'string', 'max:255'],
            'donated_amount' => ['required', 'numeric', 'min:0.01'],
            'donation_purpose' => ['nullable', 'string', 'max:255'],
            'remaining_use' => ['nullable', 'string', 'max:255'],
            'donation_date' => ['required', 'date'],
        ];
    }
}
