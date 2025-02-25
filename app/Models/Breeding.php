<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\Breeding\BreedingStatus;
use App\Enums\Breeding\PregnancyStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Breeding extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'animal_id',
        //'mate_id',
        'user_id',
        'breeding_status',
        'heat_date',
        'breeding_date',
        'due_date',
        'pregnancy_status',
        'offspring_count',
        'offspring_details',
        'remarks',
        'health_notes',
    ];

    protected $casts = [
        'breeding_status' => BreedingStatus::class,
        'pregnancy_status' => PregnancyStatus::class,
        'heat_date' => 'date',
        'breeding_date' => 'date',
        'due_date' => 'date',
        'offspring_details' => 'array',
        'health_notes' => 'array',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function mate()
    {
        return $this->belongsTo(Animal::class, 'mate_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
