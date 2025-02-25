<?php

namespace App\Http\Resources\Suppliers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Basic Information
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'tax_number' => $this->tax_number,

            // Address Information
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            // Business Information
            'type' => $this->type,
            'product_type' => $this->product_type,
            'shop_name' => $this->shop_name,
            'business_registration_number' => $this->business_registration_number,
            'contract_start_date' => $this->contract_start_date,
            'contract_end_date' => $this->contract_end_date,

            // Banking Information
            'account_holder' => $this->account_holder,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'swift_code' => $this->swift_code,
            'iban' => $this->iban,

            // Operational Information
            'supplier_importance' => $this->supplier_importance,
            'inventory_level' => $this->inventory_level,
            'reorder_point' => $this->reorder_point,
            'minimum_order_quantity' => $this->minimum_order_quantity,
            'lead_time_days' => $this->lead_time_days,

            // Financial Information
            'payment_terms' => $this->payment_terms,
            'credit_limit' => $this->credit_limit,
            'currency' => $this->currency,
            'tax_rate' => $this->tax_rate,

            // Performance Metrics
            'supplier_rating' => $this->supplier_rating,

            // Status and Notes
            'status' => $this->status,
            'notes' => $this->notes,

            // Contact Information
            'contact' => [
                'name' => $this->contact_name,
                'position' => $this->contact_position,
                'email' => $this->contact_email,
                'phone' => $this->contact_phone,
            ],

            // Relationships
           // 'animals' => AnimalResource::collection($this->whenLoaded('animals')),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
