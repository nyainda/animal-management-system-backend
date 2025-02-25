<?php
// app/Models/YieldRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class YieldRecord extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'animal_id',
        'user_id',
        'product_category_id',
        'product_grade_id',
        'production_method_id',
        'collector_id',
        'storage_location_id',
        'quantity',
        'price_per_unit',
        'total_price',
        'production_date',
        'production_time',
        'quality_status',
        'quality_notes',
        'trace_number',
        'weather_conditions',
        'storage_conditions',
        'is_organic',
        'certification_number',
        'additional_attributes',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'production_date' => 'date',
        'production_time' => 'datetime',
        'weather_conditions' => 'array',
        'storage_conditions' => 'array',
        'is_organic' => 'boolean',
        'additional_attributes' => 'array'

    ];

    protected $dates = ['deleted_at'];

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

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productGrade(): BelongsTo
    {
        return $this->belongsTo(ProductGrade::class);
    }

    public function productionMethod(): BelongsTo
    {
        return $this->belongsTo(ProductionMethod::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }

    public function storageLocation(): BelongsTo
    {
        return $this->belongsTo(StorageLocation::class);
    }
}

