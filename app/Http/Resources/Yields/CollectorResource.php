<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CollectorResource",
 *     type="object",
 *     title="Collector Resource",
 *     @OA\Property(property="id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="employee_id", type="string", example="EMP123"),
 *     @OA\Property(property="contact_number", type="string", example="+1234567890"),
 *     @OA\Property(property="certification_number", type="string", example="CERT456"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z")
 * )
 */
class CollectorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'employee_id' => $this->employee_id,
            'contact_number' => $this->contact_number,
            'certification_number' => $this->certification_number,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}