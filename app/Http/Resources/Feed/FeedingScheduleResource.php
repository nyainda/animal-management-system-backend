<?php

namespace App\Http\Resources\Feed;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Str;
class FeedingScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        // Allow the client to specify a limit for next feeding times (default: 5)
        $limit = $request->query('limit', 5);

        return [
            'id' => $this->id,
            'feed_type' => $this->whenLoaded('feedType', fn () => new FeedTypeResource($this->feedType)),
            'feeding_time' => $this->feeding_time,
            'portion_size' => $this->portion_size,
            'portion_unit' => $this->portion_unit,
            'frequency' => $this->frequency,
            'days_of_week' => $this->days_of_week,
            'special_instructions' => Str::limit($this->special_instructions, 500),
            'is_active' => $this->is_active,
            'next_feeding_times' => $this->calculateNextFeedingTimes($limit),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Calculate the next feeding times based on the schedule.
     *
     * @param int $limit The number of future feeding times to calculate.
     * @return array
     */
    private function calculateNextFeedingTimes(int $limit = 5): array
    {
        $now = now();
        $nextFeedings = [];
        $dayOffset = 0;

        // Keep searching until we find the desired number of feeding times
        while (count($nextFeedings) < $limit) {
            $nextFeeding = $this->calculateNextFeedingTime($now->copy()->addDays($dayOffset));
            if ($nextFeeding) {
                $nextFeedings[] = $nextFeeding;
            }
            $dayOffset++;
        }

        return $nextFeedings;
    }

    /**
     * Calculate the next feeding time for a specific date.
     *
     * @param \Carbon\Carbon $date The date to check.
     * @param string|null $timezone Optional timezone for the calculation.
     * @return string|null
     */
    private function calculateNextFeedingTime(Carbon $date, ?string $timezone = null): ?string
    {
        if (!$this->resource->is_active) {
            return null;
        }

        $feedingTime = Carbon::parse($this->feeding_time, $timezone);
        $checkDate = Carbon::parse($date, $timezone);

        // Check if this date matches the schedule
        if (
            $this->frequency === 'daily' ||
            ($this->frequency === 'weekly' && in_array(strtolower($checkDate->format('l')), $this->days_of_week ?? [])) ||
            ($this->frequency === 'monthly' && $checkDate->day === $feedingTime->day)
        ) {
            $combinedDateTime = $checkDate->setTimeFrom($feedingTime);

            // Ensure the feeding time is in the future
            if ($combinedDateTime->lt(now())) {
                $combinedDateTime = $combinedDateTime->addDay();
            }

            return $combinedDateTime->toISOString(); // Use ISO format for consistency
        }

        return null;
    }
}
