<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="YieldRecordResource",
 *     type="object",
 *     title="Yield Record Resource",
 *     description="A resource representing a production record",
 *     @OA\Property(property="yield_id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8"),
 *     @OA\Property(property="storage_location", ref="#/components/schemas/StorageLocationResource"),
 *     @OA\Property(property="production_method", ref="#/components/schemas/ProductionMethodResource"),
 *     @OA\Property(property="production_grade", ref="#/components/schemas/ProductGradeResource"),
 *     @OA\Property(property="product_category", ref="#/components/schemas/ProductCategoryResource"),
 *     @OA\Property(property="collector", ref="#/components/schemas/CollectorResource"),
 *     @OA\Property(property="quantity", type="number", format="float", example=5.5),
 *     @OA\Property(property="measurement_unit", type="string", example="liters"),
 *     @OA\Property(property="price_per_unit", type="number", format="float", example=2.5),
 *     @OA\Property(property="total_price", type="number", format="float", example=13.75),
 *     @OA\Property(property="production_date", type="string", format="date", example="2025-03-24"),
 *     @OA\Property(property="production_time", type="string", format="time", example="10:00:00"),
 *     @OA\Property(
 *         property="quality",
 *         type="object",
 *         @OA\Property(property="status", type="string", example="good"),
 *         @OA\Property(property="notes", type="string", example="No issues observed"),
 *         @OA\Property(property="trace_number", type="string", example="TRC12345")
 *     ),
 *     @OA\Property(
 *         property="conditions",
 *         type="object",
 *         @OA\Property(property="weather", type="string", example="sunny"),
 *         @OA\Property(property="storage", type="string", example="cool and dry")
 *     ),
 *     @OA\Property(
 *         property="organic",
 *         type="object",
 *         @OA\Property(property="is_organic", type="boolean", example=true),
 *         @OA\Property(property="certification_number", type="string", example="ORG-789")
 *     ),
 *     @OA\Property(property="additional_attributes", type="object", example={"color": "white"}),
 *     @OA\Property(property="notes", type="string", example="Processed quickly"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z")
 * )
 */
class YieldRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'yield_id' => $this->id,
            'storage_location' => new StorageLocationResource($this->whenLoaded('storageLocation')),
            'production_method' => new ProductionMethodResource($this->whenLoaded('productionMethod')),
            'production_grade' => new ProductGradeResource($this->whenLoaded('productGrade')),
            'product_category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
            'collector' => new CollectorResource($this->whenLoaded('collector')),
            'quantity' => $this->quantity,
            'measurement_unit' => $this->productCategory->measurement_unit,
            'price_per_unit' => $this->price_per_unit,
            'total_price' => $this->total_price,
            'production_date' => $this->production_date,
            'production_time' => $this->production_time,
            'quality' => [
                'status' => $this->quality_status,
                'notes' => $this->quality_notes,
                'trace_number' => $this->trace_number,
            ],
            'conditions' => [
                'weather' => $this->weather_conditions,
                'storage' => $this->storage_conditions,
            ],
            'organic' => [
                'is_organic' => $this->is_organic,
                'certification_number' => $this->certification_number,
            ],
            'additional_attributes' => $this->additional_attributes,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}