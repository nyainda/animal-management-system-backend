<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="StorageLocationResource",
 *     type="object",
 *     title="Storage Location Resource",
 *     @OA\Property(property="id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8"),
 *     @OA\Property(property="name", type="string", example="Cold Room 1"),
 *     @OA\Property(property="location_code", type="string", example="CR1"),
 *     @OA\Property(property="description", type="string", example="Main cold storage"),
 *     @OA\Property(property="storage_conditions", type="string", example="5°C, dry"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z")
 * )
 */
class StorageLocationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location_code' => $this->location_code,
            'description' => $this->description,
            'storage_conditions' => $this->storage_conditions,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
