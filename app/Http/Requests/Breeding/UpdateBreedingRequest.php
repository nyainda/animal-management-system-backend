<?php

namespace App\Http\Requests\Breeding;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Breeding\BreedingStatus;
use App\Enums\Breeding\PregnancyStatus;
use Illuminate\Validation\Rules\Enum;
class UpdateBreedingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['breeding_date'] = ['sometimes', 'date'];
        $rules['mate_id'] = ['sometimes', 'exists:animals,id'];
        return $rules;
    }
}
