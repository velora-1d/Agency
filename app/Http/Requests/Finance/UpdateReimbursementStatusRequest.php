<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReimbursementStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,approved,rejected,paid'],
            'paid_account_id' => ['nullable', 'uuid', 'exists:bank_accounts,id'],
        ];
    }
}
