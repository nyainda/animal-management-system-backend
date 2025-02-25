<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedAnalyticResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Analytic_id' => $this->id,
            'feed_type' => new FeedTypeResource($this->whenLoaded('feedType')),
            'analysis_date' => $this->analysis_date,
            'daily_consumption' => $this->daily_consumption,
            'consumption_unit' => $this->consumption_unit,
            'daily_cost' => $this->daily_cost,
            'currency' => $this->currency,
            'waste_percentage' => $this->waste_percentage,
            'consumption_patterns' => $this->consumption_patterns,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
