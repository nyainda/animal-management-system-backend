<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class ProductCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'measurement_unit',
        'description',
        'is_active'
    ];

    protected $casts = [
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

    public function yieldRecords(): HasMany
    {
        return $this->hasMany(YieldRecord::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ProductGrade::class);
    }
}
