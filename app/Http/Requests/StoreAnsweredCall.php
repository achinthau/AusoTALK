<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnsweredCall extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ani' => ['required', 'string'],
            'dnis' => ['required', 'string'],
            // 'agent' => ['required', 'string'],
            'unique_id' => ['nullable', 'string'],
            'tenant' => ['required', 'string'],
            'type' => ['required', 'string', 'in:primary,secondary'],
        ];
    }
}
