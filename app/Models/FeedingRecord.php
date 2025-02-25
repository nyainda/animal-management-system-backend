<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class FeedingRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'animal_id',
        'feed_type_id',
        'feed_inventory_id',
        'schedule_id',
        'amount',
        'unit',
        'cost',
        'currency',
        'fed_at',
        'notes',
        'feeding_method',
        'consumption_notes',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function feedType()
    {
        return $this->belongsTo(FeedType::class);
    }

    public function feedInventory()
    {
        return $this->belongsTo(FeedInventory::class);
    }

    public function schedule()
    {
        return $this->belongsTo(FeedingSchedule::class, 'schedule_id');
    }
}
