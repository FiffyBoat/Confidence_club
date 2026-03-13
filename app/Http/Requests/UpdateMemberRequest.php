<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'membership_id' => ['required', 'string', 'max:40', Rule::unique('members', 'membership_id')->ignore($memberId)],
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'status' => ['required', 'in:active,inactive'],
            'join_date' => ['required', 'date'],
            'birth_month' => ['nullable', 'integer', 'min:1', 'max:12', 'required_with:birth_day'],
            'birth_day' => ['nullable', 'integer', 'min:1', 'max:31', 'required_with:birth_month'],
            'record_admission_fee' => ['nullable', 'boolean'],
            'admission_payment_method' => ['exclude_unless:record_admission_fee,1', 'required', 'in:cash,momo,bank,card'],
            'admission_transaction_date' => ['exclude_unless:record_admission_fee,1', 'required', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $month = $this->input('birth_month');
            $day = $this->input('birth_day');

            if ($month && $day) {
                if (! checkdate((int) $month, (int) $day, 2024)) {
                    $validator->errors()->add('birth_day', 'Birth month/day is not a valid date.');
                }
            }
        });
    }
}
