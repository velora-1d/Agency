<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveLeadStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pipeline_id' => ['nullable', 'uuid', Rule::exists('pipelines', 'id')],
            'stage_id' => ['nullable', 'uuid', Rule::exists('pipeline_stages', 'id')],
        ];
    }
}
