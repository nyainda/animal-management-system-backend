<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Priority;
use App\Enums\Status;
use App\Enums\TaskType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Set to `false` and add logic if you need authorization
    }

    public function rules(): array
    {
        return [

            'title' => 'required|string|max:255',
            'task_type' => ['required', new Enum(TaskType::class)],
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'priority' => ['required', new Enum(Priority::class)],
            'status' => ['required', new Enum(Status::class)],
            'repeats' => 'nullable|in:daily,weekly,monthly,yearly',
            'repeat_frequency' => 'nullable|integer|min:1',
            'end_repeat_date' => 'nullable|date',
        ];
    }

     /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $formattedErrors = [];

        foreach ($errors->messages() as $key => $messages) {
            foreach ($messages as $message) {
                $formattedErrors[$key] = $message;
            }
        }

        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors,
            ], 422)
        );
    }
}
