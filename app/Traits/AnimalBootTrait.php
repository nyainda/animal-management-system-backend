<?php
// app/Traits/AnimalBootTrait.php
namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait AnimalBootTrait
{
    protected static function bootAnimalBootTrait()
    {
        static::created(function ($animal) {
            static::handleRegistrationActivity($animal);
            static::handleBirthActivity($animal);
            static::handleWeightActivity($animal);
            static::handleHealthActivity($animal);
            Cache::forget("animals_list_user_{$animal->user_id}");
        });

        static::updated(function ($animal) {
            Cache::forget("animal_{$animal->id}_user_{$animal->user_id}");
        });

        static::deleted(function ($animal) {
            Cache::forget("animal_{$animal->id}_user_{$animal->user_id}");
            Cache::forget("animals_list_user_{$animal->user_id}");
        });
    }

    /**
     * Handle registration activity creation
     */
    protected static function handleRegistrationActivity($animal)
    {
        $animal->activities()->create([
            'user_id' => $animal->user_id,
            'activity_type' => 'registration',
            'description' => 'Animal registered in the system',
            'details' => [
                'internal_id' => $animal->internal_id,
                'type' => $animal->type,
                'breed' => $animal->breed
            ],
            'activity_date' => now()
        ]);
    }

    /**
     * Handle birth activity creation
     */
    protected static function handleBirthActivity($animal)
    {
        if ($animal->birth_date) {
            $animal->activities()->create([
                'user_id' => $animal->user_id,
                'activity_type' => 'birth',
                'description' => 'Birth record',
                'details' => [
                    'birth_weight' => $animal->birth_weight,
                    'birth_status' => $animal->birth_status,
                    'colostrum_intake' => $animal->colostrum_intake,
                    'health_at_birth' => $animal->health_at_birth
                ],
                'activity_date' => $animal->birth_date
            ]);
        }
    }

    /**
     * Handle weight activity creation
     */
    protected static function handleWeightActivity($animal)
    {
        if ($animal->weight) {
            $animal->activities()->create([
                'user_id' => $animal->user_id,
                'activity_type' => 'weight_check',
                'description' => 'Initial weight recording',
                'details' => [
                    'weight' => $animal->weight,
                    'weight_unit' => $animal->weight_unit
                ],
                'activity_date' => now()
            ]);
        }
    }

    /**
     * Handle health activity creation
     */
    protected static function handleHealthActivity($animal)
    {
        if ($animal->vaccinations || $animal->health_at_birth) {
            $animal->activities()->create([
                'user_id' => $animal->user_id,
                'activity_type' => 'medical',
                'description' => 'Initial health assessment',
                'details' => [
                    'health_status' => $animal->health_at_birth ?? 'Not specified',
                    'vaccinations' => $animal->vaccinations ?? [],
                    'body_condition_score' => $animal->body_condition_score ?? null,
                    'notes' => 'Initial medical record created during registration',
                    'next_checkup_due' => now()->addMonths(3)->format('Y-m-d'),
                    'veterinarian' => null,
                    'medical_alerts' => []
                ],
                'activity_date' => now()
            ]);
        }
    }
}
