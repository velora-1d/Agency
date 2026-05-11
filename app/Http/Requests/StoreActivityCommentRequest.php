<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware handles workspace access
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:1000',
        ];
    }
}
