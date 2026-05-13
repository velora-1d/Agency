<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpsertPayrollSplitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'template_name' => ['nullable', 'string', 'max:100'],
            'kas_kantor_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_trigger' => ['nullable', 'in:dp,completion,full_paid,custom'],
            'payment_trigger_custom' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:operational,team_fee'],
            'items.*.component_type' => ['nullable', 'in:operational,base_fee,bonus,commission,deduction'],
            'items.*.label' => ['required', 'string', 'max:100'],
            'items.*.user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'items.*.calculation_type' => ['nullable', 'in:percentage,flat'],
            'items.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.flat_amount' => ['nullable', 'numeric'],
            'items.*.status' => ['nullable', 'in:pending,paid'],
        ];
    }
}
