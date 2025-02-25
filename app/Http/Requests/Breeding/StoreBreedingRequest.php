<?php

namespace App\Http\Requests\Breeding;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Breeding\BreedingStatus;
use App\Enums\Breeding\PregnancyStatus;

class StoreBreedingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            //'mate_id' => ['required', 'exists:animals,id'],
            'breeding_status' => ['required', 'string', 'in:' . implode(',', array_column(BreedingStatus::cases(), 'value'))],
            'heat_date' => ['nullable', 'date'],
            'breeding_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after:breeding_date'],
            'pregnancy_status' => ['nullable', 'string', 'in:' . implode(',', array_column(PregnancyStatus::cases(), 'value'))],
            'offspring_count' => ['nullable', 'integer', 'min:0'],
            'offspring_details' => ['nullable', 'array'],
            'remarks' => ['nullable', 'string'],
            'health_notes' => ['nullable', 'array'],
        ];
    }
}
