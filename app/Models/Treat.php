<?php

namespace App\Models;

use App\Enums\AdministrationRoute;
use App\Enums\TreatmentStatus;
use App\Enums\TreatmentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'animal_id',
        'user_id',
        'category_id',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
        'type',
        'product',
        'batch_number',
        'manufacturer',
        'expiry_date',
        'dosage',
        'inventory_used',
        'unit',
        'administration_route',
        'administration_site',
        'withdrawal_days',
        'withdrawal_date',
        'next_treatment_date',
        'requires_followup',
        'technician_name',
        'technician_id',
        'currency',
        'unit_cost',
        'total_cost',
        'record_transaction',
        'notes',
        'treatment_date',
        'treatment_time',
        'tags',
        'attachment_path',
        'reason',
        'diagnosis',
        'outcome',
        'vital_signs',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
        'withdrawal_date' => 'date',
        'next_treatment_date' => 'date',
        'requires_followup' => 'boolean',
        'record_transaction' => 'boolean',
        'treatment_date' => 'date',
        'treatment_time' => 'datetime',
        'tags' => 'array',
        'vital_signs' => 'array',
        'status' => TreatmentStatus::class,
        'type' => TreatmentType::class,
        'administration_route' => AdministrationRoute::class,
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
