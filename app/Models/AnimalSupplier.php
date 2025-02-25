<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AnimalSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'animal_supplier';

    protected $fillable = [
        'animal_id',
        'supplier_id',
        'relationship_type',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // UUID configuration
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

