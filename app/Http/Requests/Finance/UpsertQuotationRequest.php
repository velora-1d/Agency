<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'lead_id' => ['nullable', 'uuid', 'exists:leads,id'],
            'title' => ['required', 'string', 'max:255'],
            'cover_letter' => ['nullable', 'string'],
            'scope_of_work' => ['nullable', 'string'],
            'timeline' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'dp_percentage' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.category' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
