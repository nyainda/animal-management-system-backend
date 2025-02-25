<?php

namespace App\Models\Concerns;

use App\Enums\AnimalStatus;
use Illuminate\Support\Carbon;

trait HasAttributes
{
    protected function initializeHasAttributes()
    {
        $this->casts = [
            'status' => AnimalStatus::class,
            'keywords' => 'array',
            'physical_traits' => 'array',
            'identification_details' => 'array',
            'birth_date' => 'date',
            'death_date' => 'date',
            'birth_time' => 'datetime',
            'last_breeding_date' => 'date',
            'is_neutered' => 'boolean',
            'is_breeding_stock' => 'boolean',
            'retention_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'body_condition_score' => 'decimal:1',
            'horn_length' => 'decimal:2',
            'birth_weight' => 'decimal:2',
            'colostrum_intake' => 'integer',
            'vaccinations' => 'array',
            'birth_photos' => 'array'
        ];

        $this->appends = ['age', 'is_deceased', 'next_checkup_date'];
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getIsDeceasedAttribute(): bool
    {
        return (bool) $this->death_date;
    }

    public function getNextCheckupDateAttribute(): ?Carbon
    {
        $lastMedical = $this->activities()
            ->where('activity_type', 'medical')
            ->first();

        return $lastMedical?->details['next_checkup_due']
            ? Carbon::parse($lastMedical->details['next_checkup_due'])
            : null;
    }
}









