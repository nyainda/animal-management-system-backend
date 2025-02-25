<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Priority;
use App\Enums\Status;
use App\Enums\TaskType;
use Illuminate\Validation\Rules\Enum;

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
}
