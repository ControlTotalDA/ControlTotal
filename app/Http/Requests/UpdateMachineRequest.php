<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['laser', 'bending', 'cnc', 'press', 'other'])],
            'phases' => ['sometimes', Rule::in(['single', 'split', 'three'])],
            'max_voltage' => ['nullable', 'numeric', 'min:0'],
            'min_voltage' => ['nullable', 'numeric', 'min:0'],
            'max_current' => ['nullable', 'numeric', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
