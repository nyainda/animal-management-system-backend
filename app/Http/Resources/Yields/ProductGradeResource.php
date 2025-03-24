<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductGradeResource",
 *     type="object",
 *     title="Product Grade Resource",
 *     @OA\Property(property="id", type="string", format="uuid", example="8f14e45f-ceea-41d4-a716-446655440000"),
 *     @OA\Property(property="grade_name", type="string", example="Grade A"),
 *     @OA\Property(property="description", type="string", example="High quality grade"),
 *     @OA\Property(property="price_modifier", type="number", format="float", example=1.2)
 * )
 */
class ProductGradeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'grade_name' => $this->grade_name,
            'description' => $this->description,
            'price_modifier' => $this->price_modifier,
        ];
    }
}
