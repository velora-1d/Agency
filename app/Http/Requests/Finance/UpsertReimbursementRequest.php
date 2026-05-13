<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertReimbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'department' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,approved,rejected,paid'],
            'receipt_path' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'paid_account_id' => ['nullable', 'uuid', 'exists:bank_accounts,id'],
        ];
    }
}
