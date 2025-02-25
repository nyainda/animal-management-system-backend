<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class FeedInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'feed_inventory';

    protected $fillable = [
        'user_id',
        'animal_id',
        'feed_type_id',
        'quantity',
        'unit',
        'unit_price',
        'currency',
        'purchase_date',
        'expiry_date',
        'batch_number',
        'supplier',
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

    public function feedingRecords()
    {
        return $this->hasMany(FeedingRecord::class);
    }
}
