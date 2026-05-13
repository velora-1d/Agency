<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'uuid', 'exists:vendors,id'],
            'transaction_id' => ['nullable', 'uuid', 'exists:transactions,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'status' => ['nullable', 'in:active,expired,cancelled'],
            'next_renewal_date' => ['nullable', 'date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:0', 'max:365'],
        ];
    }
}
