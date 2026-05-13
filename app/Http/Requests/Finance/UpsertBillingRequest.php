<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:retainer,project_based'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'start_date' => ['required', 'date'],
            'next_invoice_date' => ['required', 'date'],
            'status' => ['required', 'in:active,paused,completed'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
