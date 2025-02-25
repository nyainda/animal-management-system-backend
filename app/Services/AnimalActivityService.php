<?php
// app/Services/AnimalActivityService.php
namespace App\Services;

use App\Models\Animal;
use Carbon\Carbon;

class AnimalActivityService
{
    /**
     * Generate birthday activities for animals
     */
    public function generateBirthdayActivities()
    {
        $today = Carbon::now();

        Animal::whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->with('user')
            ->chunkById(100, function ($animals) {
                foreach ($animals as $animal) {
                    $this->createBirthdayActivity($animal);
                }
            });
    }

    /**
     * Create birthday activity for a single animal
     */
    protected function createBirthdayActivity(Animal $animal)
    {
        return $animal->activities()->create([
            'user_id' => $animal->user_id,
            'activity_type' => 'birthday',
            'description' => sprintf(
                '%s turns %d years old today',
                $animal->name,
                $animal->age
            ),
            'details' => [
                'age' => $animal->age,
                'birth_date' => $animal->birth_date->format('Y-m-d'),
                'type' => $animal->type,
                'breed' => $animal->breed,
                'current_stats' => [
                    'weight' => $animal->weight,
                    'weight_unit' => $animal->weight_unit,
                    'body_condition_score' => $animal->body_condition_score,
                    'health_status' => $animal->health_at_birth
                ]
            ],
            'activity_date' => now()
        ]);
    }
}
