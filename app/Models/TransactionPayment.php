<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PaymentMethod;
use Illuminate\Support\Str;

class TransactionPayment extends Model
{
    use SoftDeletes;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'payment_date' => 'datetime',
    ];

    protected $fillable = [
        'transaction_id', 'recorded_by', 'amount', 'payment_method', 'payment_reference',
        'payment_date', 'payment_status', 'notes',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id');
    }
}
