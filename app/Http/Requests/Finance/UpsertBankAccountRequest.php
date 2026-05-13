<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:bank,cash,e-wallet'],
            'is_active' => ['nullable', 'boolean'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
