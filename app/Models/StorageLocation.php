<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class StorageLocation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'location_code',
        'description',
        'storage_conditions',
        'is_active',
    ];

    protected $casts = [
        'storage_conditions' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID when creating a new record
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Str::uuid();
            }
        });
    }

    // Relationships
    public function yieldRecords()
    {
        return $this->hasMany(YieldRecord::class, 'storage_location_id');
    }
}
