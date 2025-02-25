<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedAnalyticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

           // 'feed_type_id' => 'required|uuid',
            'analysis_date' => 'required|date',
            'daily_consumption' => 'required|numeric',
            'consumption_unit' => 'required|string',
            'daily_cost' => 'required|numeric',
            'currency' => 'required|string',
            'waste_percentage' => 'nullable|numeric',
            'consumption_patterns' => 'nullable|array',
        ];
    }
}
