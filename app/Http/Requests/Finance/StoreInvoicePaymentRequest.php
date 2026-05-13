<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoicePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:60', 'regex:/^(manual_transfer|pakasir_[a-z0-9_]+)$/'],
            'notes' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
