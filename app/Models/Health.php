<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\str;
class Health extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'health_status',
        'vaccination_status',
        'medical_history',
        'neutered_spayed',
        'user_id',
        'animal_id',
        'vet_contact_id',
        'dietary_restrictions',
        'regular_medication',
        'last_vet_visit',
        'insurance_details',
        'exercise_requirements',
        'parasite_prevention',
        'vaccinations',
        'allergies',
        'notes'
    ];

    protected $casts = [
        'medical_history' => 'array',
        'dietary_restrictions' => 'array',
        'neutered_spayed' => 'boolean',
        'regular_medication' => 'array',
        'last_vet_visit' => 'date',
        'exercise_requirements' => 'array',
        'parasite_prevention' => 'array',
        'vaccinations' => 'array',
        'allergies' => 'array',
        'notes' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
