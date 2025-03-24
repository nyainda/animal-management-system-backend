<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductCategoryResource",
 *     type="object",
 *     title="Product Category Resource",
 *     @OA\Property(property="id", type="string", format="uuid", example="9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"),
 *     @OA\Property(property="name", type="string", example="Milk"),
 *     @OA\Property(property="measurement_unit", type="string", example="liters"),
 *     @OA\Property(property="description", type="string", example="Dairy product category"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="grades",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ProductGradeResource")
 *     )
 * )
 */
class ProductCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'measurement_unit' => $this->measurement_unit,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'grades' => ProductGradeResource::collection($this->whenLoaded('grades')),
        ];
    }
}

