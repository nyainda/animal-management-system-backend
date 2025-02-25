<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'amount' => 'sometimes|numeric',
            'unit' => 'sometimes|string',
            'cost' => 'sometimes|numeric',
            'currency' => 'sometimes|string',
            'fed_at' => 'sometimes|date',
            'notes' => 'nullable|string',
            'feeding_method' => 'nullable|string',
            'consumption_notes' => 'nullable|string',
        ];
    }
}
