<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeedingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feeding_time' => 'sometimes|date_format:H:i',
            'portion_size' => 'sometimes|numeric|min:0',
            'portion_unit' => 'sometimes|string|max:50',
            'frequency' => [
                'sometimes',
                Rule::in(['daily', 'weekly', 'monthly'])
            ],
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => [
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ],
            'special_instructions' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
