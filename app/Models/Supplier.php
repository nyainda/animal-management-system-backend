<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'email',
        'phone',
        'website',
        'tax_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'type',
        'product_type',
        'shop_name',
        'business_registration_number',
        'contract_start_date',
        'contract_end_date',
        'account_holder',
        'account_number',
        'bank_name',
        'bank_branch',
        'swift_code',
        'iban',
        'supplier_importance',
        'inventory_level',
        'reorder_point',
        'minimum_order_quantity',
        'lead_time_days',
        'payment_terms',
        'credit_limit',
        'currency',
        'tax_rate',
        'supplier_rating',
        'total_orders',
        'fulfilled_orders',
        'delayed_orders',
        'quality_incidents',
        'status',
        'notes',
        'meta_data',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'credit_limit' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'supplier_rating' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
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
    public function category()
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id');
    }

    public function contacts()
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function animals()
    {
        return $this->belongsToMany(Animal::class, 'animal_supplier')
            ->withPivot(['relationship_type', 'start_date', 'end_date', 'notes'])
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
