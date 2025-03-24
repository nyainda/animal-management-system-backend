<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TaskResource",
 *     type="object",
 *     title="Task Resource",
 *     description="A resource representing a task associated with an animal",
 *     @OA\Property(property="task_id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8", description="Unique identifier of the task"),
 *     @OA\Property(property="title", type="string", example="Vaccination", description="Title of the task"),
 *     @OA\Property(property="task_type", type="string", example="medical", description="Type of the task"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-03-25", description="Start date of the task"),
 *     @OA\Property(property="start_time", type="string", format="time", example="09:00:00", description="Start time of the task"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-03-25", description="End date of the task"),
 *     @OA\Property(property="end_time", type="string", format="time", nullable=true, example="10:00:00", description="End time of the task"),
 *     @OA\Property(property="duration", type="integer", example=60, description="Duration of the task in minutes"),
 *     @OA\Property(property="description", type="string", example="Administer vaccine to animal", description="Description of the task"),
 *     @OA\Property(property="health_notes", type="string", nullable=true, example="Monitor for side effects", description="Health-related notes"),
 *     @OA\Property(property="location", type="string", example="Barn A", description="Location where the task occurs"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="high", description="Priority level of the task"),
 *     @OA\Property(property="status", type="string", example="pending", description="Current status of the task"),
 *     @OA\Property(property="repeats", type="boolean", example=false, description="Whether the task repeats"),
 *     @OA\Property(property="repeat_frequency", type="string", nullable=true, example="weekly", description="Frequency of repetition (e.g., daily, weekly)"),
 *     @OA\Property(property="end_repeat_date", type="string", format="date", nullable=true, example="2025-12-31", description="Date when repetition ends"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Timestamp when the task was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="Timestamp when the task was last updated"),
 *     @OA\Property(property="animal_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="UUID of the associated animal"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000", description="UUID of the associated user")
 * )
 */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'task_id' => $this->id,
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
            'animal_id' => $this->animal_id,
            'user_id' => $this->user_id,
        ];
    }
}