<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Note\Status;
use App\Enums\Note\Priority;
use Illuminate\Validation\Rules\Enum;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => 'sometimes|string',
            'category' => 'sometimes|nullable|string|max:255',
            'keywords' => 'sometimes|nullable|array',
            'file_path' => 'sometimes|nullable|string|max:255',
            'add_to_calendar' => 'sometimes|nullable|boolean',
            'status' => ['required', new Enum(Status::class)],
            'priority' => ['required', new Enum(Priority::class)],
            'due_date' => 'sometimes|nullable|date',
        ];
    }
}
