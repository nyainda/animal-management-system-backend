<?php

// app/Http/Requests/StoreSireRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class StoreSireRequest extends FormRequest
{
    public function authorize()
    {
        $animal = $this->route('animal');
        return $animal && $animal->user_id == Auth::id();
    }

    public function rules()
    {
        return [
            'related_animal_id' => [
                'required',
                Rule::exists('animals', 'id')
                    ->where('user_id', Auth::id())
                    ->where('gender', 'male')
            ],
            'breeding_date' => 'nullable|date',
            'breeding_notes' => 'nullable|string|max:500'
        ];
    }
}
