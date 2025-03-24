<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ActivityResource",
 *     type="object",
 *     title="Activity Resource",
 *     description="A resource representing an animal activity",
 *     @OA\Property(property="id", type="integer", example=1, description="The unique identifier of the activity"),
 *     @OA\Property(property="animal_id", type="integer", example=1, description="The ID of the animal associated with this activity"),
 *     @OA\Property(property="activity_type", type="string", example="feeding", description="The type of activity (e.g., feeding, breeding)"),
 *     @OA\Property(property="activity_date", type="string", format="date", example="2025-03-24", description="The date the activity occurred"),
 *     @OA\Property(property="description", type="string", example="Fed the animal with grain", description="A description of the activity"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Animal seemed healthy", description="Additional notes about the activity"),
 *     @OA\Property(property="breeding_date", type="string", format="date", nullable=true, example="2025-03-24", description="The date of breeding, if applicable"),
 *     @OA\Property(property="breeding_notes", type="string", nullable=true, example="Successful breeding", description="Notes specific to breeding, if applicable"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="The timestamp when the activity was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="The timestamp when the activity was last updated")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedActivityResource",
 *     type="object",
 *     title="Paginated Activity Resource",
 *     description="A paginated collection of activity resources",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ActivityResource")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://api.example.com/activities?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.example.com/activities?page=10"),
 *         @OA\Property(property="prev", type="string", nullable=true, example=null),
 *         @OA\Property(property="next", type="string", nullable=true, example="http://api.example.com/activities?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=10),
 *         @OA\Property(property="path", type="string", example="http://api.example.com/activities"),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=100)
 *     )
 * )
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'animal_id' => $this->animal_id,
            'activity_type' => $this->activity_type,
            'activity_date' => $this->activity_date,
            'description' => $this->description,
            'notes' => $this->notes,
            'breeding_date' => $this->breeding_date,
            'breeding_notes' => $this->breeding_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}