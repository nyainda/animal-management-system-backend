<?php

// app/Http/Requests/Animal/ListAnimalRequest.php
namespace App\Http\Requests\Animal;

use Illuminate\Foundation\Http\FormRequest;

class ListAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|string|in:all,cattle,sheep,goat,pig,horse,poultry',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:name,type,breed,birth_date',
            'sort_direction' => 'sometimes|string|in:asc,desc',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Invalid animal type selected',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page cannot exceed 100',
        ];
    }
}
