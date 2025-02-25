<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ProductGrade extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_category_id',
        'grade_name',  // Changed from 'name' to match migration
        'description',
        'price_modifier',
        'is_active'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean'
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

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function yieldRecords()
    {
        return $this->hasMany(YieldRecord::class);
    }
}
