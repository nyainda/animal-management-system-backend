<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:255',
            'recommended_storage' => 'nullable|string',
            'shelf_life_days' => 'nullable|integer',
            'nutritional_info' => 'nullable|string',
        ];
    }
}
