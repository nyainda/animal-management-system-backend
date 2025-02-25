<?php

namespace App\Http\Resources\Treat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'treat_id' => $this->id,
            'animal_id' => $this->animal_id,
            'user_id' => $this->user_id,
             'status' => $this->status,
           //'is_verified' => $this->is_verified,
           // 'verified_by' => $this->verified_by,
           // 'verified_at' => $this->verified_at,
            'type' => $this->type,
            'product' => $this->product,
            'batch_number' => $this->batch_number,
            'manufacturer' => $this->manufacturer,
            'expiry_date' => $this->expiry_date,
            'dosage' => $this->dosage,
            'inventory_used' => $this->inventory_used,
            'unit' => $this->unit,
            'administration_route' => $this->administration_route,
            'administration_site' => $this->administration_site,
            'withdrawal_days' => $this->withdrawal_days,
            'withdrawal_date' => $this->withdrawal_date,
            'next_treatment_date' => $this->next_treatment_date,
            'requires_followup' => $this->requires_followup,
            'technician_name' => $this->technician_name,
            'technician_id' => $this->technician_id,
            'currency' => $this->currency,
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->total_cost,
            'record_transaction' => $this->record_transaction,
            'notes' => $this->notes,
            'treatment_date' => $this->treatment_date,
            'treatment_time' => $this->treatment_time,
            'tags' => $this->tags,
            'attachment_path' => $this->attachment_path,
            'reason' => $this->reason,
            'diagnosis' => $this->diagnosis,
            'outcome' => $this->outcome,
            'vital_signs' => $this->vital_signs,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
