<?php

namespace App\Http\Resources\Yields;

use Illuminate\Http\Resources\Json\JsonResource;

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
