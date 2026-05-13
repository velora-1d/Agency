<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class TransferFundsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'uuid', 'different:to_account_id', 'exists:bank_accounts,id'],
            'to_account_id' => ['required', 'uuid', 'different:from_account_id', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
