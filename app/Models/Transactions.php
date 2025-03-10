<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TransactionType;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Support\Str;

class Transactions extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'transaction_type' => TransactionType::class,
        'payment_method' => TransactionPaymentMethod::class,
        'transaction_status' => TransactionStatus::class,
        'attached_documents' => 'array',
        'terms_and_conditions' => 'array',
        'transaction_date' => 'date',
        'delivery_date' => 'datetime',
        'payment_due_date' => 'date',
        'terms_accepted_at' => 'datetime',
    ];

    protected $fillable = [
        'animal_id', 'seller_id', 'buyer_id', 'transaction_type', 'price', 'tax_amount', 'total_amount',
        'currency', 'transaction_date', 'delivery_date', 'details', 'payment_method', 'payment_reference',
        'deposit_amount', 'balance_due', 'payment_due_date', 'transaction_status', 'seller_name',
        'seller_company', 'seller_tax_id', 'seller_contact', 'seller_email', 'seller_phone',
        'seller_address', 'seller_city', 'seller_state', 'seller_country', 'seller_postal_code',
        'seller_identification', 'seller_license_number', 'buyer_name', 'buyer_company', 'buyer_tax_id',
        'buyer_contact', 'buyer_email', 'buyer_phone', 'buyer_address', 'buyer_city', 'buyer_state',
        'buyer_country', 'buyer_postal_code', 'buyer_identification', 'buyer_license_number',
        'invoice_number', 'contract_number', 'terms_accepted', 'terms_accepted_at', 'health_certificate_number',
        'transport_license_number', 'attached_documents', 'location_of_sale', 'terms_and_conditions',
        'special_conditions', 'delivery_instructions', 'insurance_policy_number', 'insurance_amount',
        'created_by', 'updated_by',
    ];

    // Add this boot method to generate UUIDs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function payments()
    {
        return $this->hasMany(TransactionPayment::class, 'transaction_id');
    }
}
