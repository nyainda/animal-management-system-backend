<?php

// app/Http/Requests/StoreOffspringRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class StoreOffspringRequest extends FormRequest
{
    public function authorize()
    {
        $animal = $this->route('animal');
        return $animal && $animal->user_id == Auth::id();
    }

    public function rules()
    {
        $animal = $this->route('animal');

        return [
            'offspring_id' => [
                'required',
                Rule::exists('animals', 'id')->where('user_id', Auth::id())
            ],
            'parent_type' => [
                'required',
                Rule::in(['dam', 'sire']),
                function ($attribute, $value, $fail) use ($animal) {
                    if ($value === 'dam' && $animal->gender !== 'female') {
                        $fail('The parent must be female to be a dam.');
                    }
                    if ($value === 'sire' && $animal->gender !== 'male') {
                        $fail('The parent must be male to be a sire.');
                    }
                },
            ],
            'breeding_date' => 'nullable|date',
            'breeding_notes' => 'nullable|string|max:500'
        ];
    }
}
