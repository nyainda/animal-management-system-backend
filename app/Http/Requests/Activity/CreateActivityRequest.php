<?php
// app/Http/Requests/Activity/CreateActivityRequest.php
namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class CreateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->animal->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string'],
            'activity_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'breeding_date' => ['nullable', 'date', 'required_if:activity_type,breeding'],
            'breeding_notes' => ['nullable', 'string', 'required_if:activity_type,breeding'],
            'is_automatic' => ['boolean', 'nullable']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->id,
            'is_automatic' => false
        ]);
    }
}
