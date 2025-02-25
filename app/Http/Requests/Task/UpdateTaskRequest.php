<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Priority;
use App\Enums\Status;
use App\Enums\TaskType;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_id' => 'sometimes|uuid|exists:animals,id',
            'user_id' => 'sometimes|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'task_type' => ['sometimes', new Enum(TaskType::class)],
            'start_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'health_notes' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'priority' => ['sometimes', new Enum(Priority::class)],
            'status' => ['sometimes', new Enum(Status::class)],
            'repeats' => 'nullable|in:daily,weekly,monthly,yearly',
            'repeat_frequency' => 'nullable|integer|min:1',
            'end_repeat_date' => 'nullable|date',
        ];
    }
}
