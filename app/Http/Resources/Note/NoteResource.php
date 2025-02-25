<?php

namespace App\Http\Resources\Note;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
