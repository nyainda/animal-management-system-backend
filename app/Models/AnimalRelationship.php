<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnimalRelationship extends Model
{
    use HasUuids;

    protected $fillable = [
        'animal_id',
        'related_animal_id',
        'relationship_type',
        'breeding_date',
        'breeding_notes'
    ];

    protected $casts = [
        'breeding_date' => 'date'
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function relatedAnimal()
    {
        return $this->belongsTo(Animal::class, 'related_animal_id');
    }
}
