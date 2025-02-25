<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
class AnimalBirthDetail extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'animal_id',
        'birth_time',
        'birth_status',
        'colostrum_intake',
        'health_at_birth',
        'milk_feeding',
        'vaccinations',
        'breeder_info',
        'birth_photos',
        'raised_purchased',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'birth_time' => 'datetime',
        'vaccinations' => 'array',
        'birth_photos' => 'array',
        'birth_complications' => 'array',
        'colostrum_intake' => 'integer',
        'birth_weight' => 'decimal:2',
        'weaning_weight' => 'decimal:2',
        'multiple_birth' => 'boolean',
        'birth_order' => 'integer',
        'gestation_length' => 'integer',
        'weaning_date' => 'date'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($birthDetail) {
            Cache::forget("animal_{$birthDetail->animal_id}_birth_details");

            // Create activity record for the animal
            $birthDetail->animal->activities()->create([
                'user_id' => $birthDetail->animal->user_id,
                'activity_type' => 'birth_detail_update',
                'description' => 'Birth details updated',
                'details' => [
                    'birth_status' => $birthDetail->birth_status,
                    'health_at_birth' => $birthDetail->health_at_birth,
                    'birth_complications' => $birthDetail->birth_complications ?? [],
                    'birth_assistance' => $birthDetail->birth_assistance
                ],
                'activity_date' => now()
            ]);
        });

        static::updated(function ($birthDetail) {
            Cache::forget("animal_{$birthDetail->animal_id}_birth_details");
        });

        static::deleted(function ($birthDetail) {
            Cache::forget("animal_{$birthDetail->animal_id}_birth_details");
        });
    }

    /**
     * Get the animal that owns the birth details.
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    /**
     * Get cached birth details by animal ID.
     */
    public static function getCachedByAnimalId($animalId)
    {
        return Cache::remember(
            "animal_{$animalId}_birth_details",
            now()->addMinutes(60),
            function () use ($animalId) {
                return self::where('animal_id', $animalId)->first();
            }
        );
    }

    /**
     * Check if the animal was born as part of a multiple birth.
     */
    public function isMultipleBirth()
    {
        return $this->multiple_birth;
    }

    /**
     * Calculate days until weaning based on weaning_date.
     */
    public function getDaysUntilWeaningAttribute()
    {
        if (!$this->weaning_date) {
            return null;
        }

        $weaningDate = Carbon::parse($this->weaning_date);
        if ($weaningDate->isPast()) {
            return 0;
        }

        return now()->diffInDays($weaningDate);
    }

    /**
     * Get the weight gain from birth to weaning.
     */
    public function getWeaningWeightGainAttribute()
    {
        if (!$this->weaning_weight || !$this->birth_weight) {
            return null;
        }

        return $this->weaning_weight - $this->birth_weight;
    }

    /**
     * Scope for healthy births.
     */
    public function scopeHealthyBirths($query)
    {
        return $query->whereNull('birth_complications')
                    ->where('health_at_birth', 'healthy');
    }

    /**
     * Scope for births needing assistance.
     */
    public function scopeAssistedBirths($query)
    {
        return $query->whereNotNull('birth_assistance');
    }

    /**
     * Scope for multiple births.
     */
    public function scopeMultipleBirths($query)
    {
        return $query->where('multiple_birth', true);
    }

    /**
     * Check if birth had complications.
     */
    public function hadComplications()
    {
        return !empty($this->birth_complications);
    }

    /**
     * Get the daily weight gain until weaning.
     */
    public function getDailyWeightGainAttribute()
    {
        if (!$this->weaning_weight || !$this->birth_weight || !$this->weaning_date) {
            return null;
        }

        $daysToWeaning = Carbon::parse($this->birth_time)->diffInDays($this->weaning_date);
        if ($daysToWeaning === 0) {
            return 0;
        }

        return ($this->weaning_weight - $this->birth_weight) / $daysToWeaning;
    }
}
