<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'quotation_id' => ['nullable', 'uuid', 'exists:quotations,id'],
            'contract_id' => ['nullable', 'uuid', 'exists:contracts,id'],
            'billing_id' => ['nullable', 'uuid', 'exists:billings,id'],
            'type' => ['nullable', 'in:invoice,proforma,credit_note'],
            'status' => ['nullable', 'in:draft,sent,partial,paid,overdue'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'due_date' => ['nullable', 'date'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_rule' => ['nullable', 'in:weekly,monthly,quarterly,yearly'],
            'payment_method' => ['nullable', 'string', 'max:60', 'regex:/^(manual_transfer|pakasir_[a-z0-9_]+)$/'],
            'pakasir_order_id' => ['nullable', 'string', 'max:100'],
            'pakasir_payment_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'items' => ['required_without:quotation_id', 'array', 'min:1'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
