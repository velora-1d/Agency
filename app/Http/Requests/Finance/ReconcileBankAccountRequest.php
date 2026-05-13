<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ReconcileBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_reconciled_at' => ['required', 'date'],
            'reconciliation_notes' => ['nullable', 'string'],
        ];
    }
}
