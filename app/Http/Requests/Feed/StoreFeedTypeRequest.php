<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'recommended_storage' => 'nullable|string',
            'shelf_life_days' => 'nullable|integer',
            'nutritional_info' => 'nullable|string',
        ];
    }
}
