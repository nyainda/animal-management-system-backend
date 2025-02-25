<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedAnalyticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'analysis_date' => 'sometimes|date',
            'daily_consumption' => 'sometimes|numeric',
            'consumption_unit' => 'sometimes|string',
            'daily_cost' => 'sometimes|numeric',
            'currency' => 'sometimes|string',
            'waste_percentage' => 'nullable|numeric',
            'consumption_patterns' => 'nullable|array',
        ];
    }
}
