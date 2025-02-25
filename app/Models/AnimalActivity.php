<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnimalActivity extends Model
{
    use HasUuids;

    protected $fillable = [
        'animal_id',
        'user_id',
        'activity_type',
        'description',
        'notes',         // Add this
        'details',
        'activity_date',
        'breeding_date', // Add this if missing
        'breeding_notes', // Add this if missing
        'is_automatic',
        'auto_type'
    ];

    protected $casts = [
        'details' => 'array',
        'activity_date' => 'datetime',
        'is_automatic' => 'boolean'  // Add this cast
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add these scopes
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }

    public function scopeOfAutoType($query, $type)
    {
        return $query->where('auto_type', $type);
    }
}
