<?php

namespace App\Http\Resources\Note;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="NoteResource",
 *     type="object",
 *     title="Note Resource",
 *     description="A resource representing a note associated with an animal",
 *     @OA\Property(property="notes_id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8", description="Unique identifier of the note"),
 *     @OA\Property(property="content", type="string", example="Check animal health status", description="Main content of the note"),
 *     @OA\Property(property="category", type="string", example="Health", description="Category of the note"),
 *     @OA\Property(property="keywords", type="string", example="health,checkup", description="Keywords associated with the note"),
 *     @OA\Property(property="file_path", type="string", nullable=true, example="/uploads/notes/health_check.pdf", description="Path to an attached file, if any"),
 *     @OA\Property(property="add_to_calendar", type="boolean", example=false, description="Whether the note should be added to a calendar"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium", description="Priority level of the note"),
 *     @OA\Property(property="status", type="string", example="pending", description="Current status of the note"),
 *     @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2025-03-25T14:00:00Z", description="Due date for the note, if applicable"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Timestamp when the note was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="Timestamp when the note was last updated"),
 *     @OA\Property(property="animal_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="UUID of the associated animal"),
 *     @OA\Property(property="user_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000", description="UUID of the user who created the note")
 * )
 */
class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'notes_id' => $this->id,
            'content' => $this->content,
            'category' => $this->category,
            'keywords' => $this->keywords,
            'file_path' => $this->file_path,
            'add_to_calendar' => $this->add_to_calendar,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'animal_id' => $this->animal_id,
            'user_id' => $this->user_id,
        ];
    }
}