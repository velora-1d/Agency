<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable', 'uuid', 'exists:bank_accounts,id'],
            'invoice_id' => ['nullable', 'uuid', 'exists:invoices,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'type' => ['required', 'in:income,expense'],
            'category' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ];
    }
}
