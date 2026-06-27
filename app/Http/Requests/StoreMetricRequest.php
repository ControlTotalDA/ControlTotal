<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetricRequest extends FormRequest
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
            'machine_id' => ['required', 'uuid'],
            'recorded_at' => ['required', 'date'],
            'readings' => ['required', 'array', 'min:1'],
            'readings.*.phase' => ['required', Rule::in(['L1', 'L2', 'L3'])],
            'readings.*.voltage' => ['required', 'numeric'],
            'readings.*.current' => ['required', 'numeric'],
            'readings.*.power_real' => ['required', 'numeric'],
            'readings.*.power_apparent' => ['required', 'numeric'],
            'readings.*.power_factor' => ['required', 'numeric', 'between:0,1'],
            'readings.*.energy_kwh' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
