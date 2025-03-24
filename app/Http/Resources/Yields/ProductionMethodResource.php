<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductionMethodResource",
 *     type="object",
 *     title="Production Method Resource",
 *     @OA\Property(property="id", type="string", format="uuid", example="7c9e6679-7425-40de-944b-e07fc1f90ae7"),
 *     @OA\Property(property="product_category_id", type="string", format="uuid", example="9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"),
 *     @OA\Property(property="method_name", type="string", example="Manual Milking"),
 *     @OA\Property(property="description", type="string", example="Traditional milking method"),
 *     @OA\Property(property="requires_certification", type="boolean", example=false),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z")
 * )
 */
class ProductionMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'method_name' => $this->method_name,
            'description' => $this->description,
            'requires_certification' => $this->requires_certification,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
