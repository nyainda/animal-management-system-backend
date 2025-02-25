<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Note\Status;
use App\Enums\Note\Priority;
use Illuminate\Validation\Rules\Enum;
class StoreNoteRequest extends FormRequest
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
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'keywords' => 'nullable|array',
            'file_path' => 'nullable|string|max:255',
            'add_to_calendar' => 'nullable|boolean',
            //'priority' => 'nullable|in:low,medium,high',
           // 'status' => 'nullable|in:pending,completed,archived',
            'status' => ['required', new Enum(Status::class)],
            'priority' => ['required', new Enum(Priority::class)],
            'due_date' => 'nullable|date',
        ];
    }
}
