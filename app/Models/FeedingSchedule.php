<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FeedingSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'animal_id',
        'feed_type_id',
        'feeding_time',
        'portion_size',
        'portion_unit',
        'frequency',
        'days_of_week',
        'special_instructions',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to handle UUID generation and input validation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID if not provided
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }

            // Validate feeding_time format
            if (!empty($model->feeding_time) && !Carbon::createFromFormat('H:i:s', $model->feeding_time)) {
                throw new \InvalidArgumentException('Invalid feeding_time format. Expected HH:MM:SS.');
            }

            // Validate frequency
            if (!in_array($model->frequency, ['daily', 'weekly', 'monthly'], true)) {
                throw new \InvalidArgumentException('Invalid frequency. Allowed values: daily, weekly, monthly.');
            }

            // Validate days_of_week for weekly frequency
            if ($model->frequency === 'weekly' && empty($model->days_of_week)) {
                throw new \InvalidArgumentException('days_of_week is required for weekly frequency.');
            }
        });
    }

    /**
     * Calculate the next feeding times based on the schedule.
     *
     * @param int $days Number of days to look ahead (default: 7)
     * @return array
     */
    public function calculateNextFeedingTimes(int $days = 7): array
    {
        if (!$this->is_active || empty($this->feeding_time)) {
            return [];
        }

        $now = Carbon::now();
        $endDate = $now->copy()->addDays($days);
        $feedingTimes = [];

        // Map day names to Carbon day numbers (0 = Sunday, 1 = Monday, etc.)
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        // Helper function to calculate feeding times based on frequency
        $calculateFeedingTime = function ($currentDate) use ($now) {
            $feedingDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $this->feeding_time);
            return $feedingDateTime->gt($now) ? $feedingDateTime->format('Y-m-d H:i:s') : null;
        };

        $currentDate = $now->copy();

        while ($currentDate->lte($endDate)) {
            switch ($this->frequency) {
                case 'daily':
                    $time = $calculateFeedingTime($currentDate);
                    if ($time) {
                        $feedingTimes[] = $time;
                    }
                    break;

                case 'weekly':
                    if (!empty($this->days_of_week)) {
                        $currentDayName = strtolower($currentDate->format('l'));
                        if (in_array($currentDayName, $this->days_of_week)) {
                            $time = $calculateFeedingTime($currentDate);
                            if ($time) {
                                $feedingTimes[] = $time;
                            }
                        }
                    }
                    break;

                case 'monthly':
                    // Assuming monthly means same date each month
                    if ($currentDate->day === $now->day) {
                        $time = $calculateFeedingTime($currentDate);
                        if ($time) {
                            $feedingTimes[] = $time;
                        }
                    }
                    break;

                default:
                    break;
            }

            $currentDate->addDay();
        }

        return $feedingTimes;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function feedType()
    {
        return $this->belongsTo(FeedType::class);
    }

    public function feedingRecords()
    {
        return $this->hasMany(FeedingRecord::class);
    }
}
