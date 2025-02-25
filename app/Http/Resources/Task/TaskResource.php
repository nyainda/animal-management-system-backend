<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'Task_id' => $this->id,
            'title' => $this->title,
            'task_type' => $this->task_type,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_date' => $this->end_date,
            'end_time' => $this->end_time,
            'duration' => $this->duration,
            'description' => $this->description,
            'health_notes' => $this->health_notes,
            'location' => $this->location,
            'priority' => $this->priority,
            'status' => $this->status,
            'repeats' => $this->repeats,
            'repeat_frequency' => $this->repeat_frequency,
            'end_repeat_date' => $this->end_repeat_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'animal' => $this->whenLoaded('animal'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
