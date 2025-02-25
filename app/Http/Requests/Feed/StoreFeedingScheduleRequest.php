<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //'feed_type_id' => 'required|uuid|exists:feed_types,id',
            'feeding_time' => 'required|date_format:H:i',
            'portion_size' => 'required|numeric|min:0',
            'portion_unit' => 'required|string|max:50',
            'frequency' => [
                'required',
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

    public function messages()
    {
        return [
            'days_of_week.*.in' => 'Invalid day of week selected.',
        ];
    }
}
