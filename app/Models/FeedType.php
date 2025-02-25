<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class FeedType extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'animal_id',
        'name',
        'description',
        'category',
        'recommended_storage',
        'shelf_life_days',
        'nutritional_info',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function feedInventories()
    {
        return $this->hasMany(FeedInventory::class);
    }

    public function feedingSchedules()
    {
        return $this->hasMany(FeedingSchedule::class);
    }

    public function feedingRecords()
    {
        return $this->hasMany(FeedingRecord::class);
    }

    public function feedAnalytics()
    {
        return $this->hasMany(FeedAnalytic::class);
    }
}
