<?php

namespace App\Http\Resources\Suppliers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SupplierResource",
 *     type="object",
 *     title="Supplier Resource",
 *     description="A resource representing a supplier",
 *     @OA\Property(property="id", type="string", format="uuid", example="6ba7b810-9dad-11d1-80b4-00c04fd430c8", description="Unique identifier of the supplier"),
 *     @OA\Property(property="name", type="string", example="Farm Supplies Inc.", description="Name of the supplier"),
 *     @OA\Property(property="email", type="string", format="email", example="contact@farmsupplies.com", description="Email address of the supplier"),
 *     @OA\Property(property="phone", type="string", example="+1234567890", description="Phone number of the supplier"),
 *     @OA\Property(property="website", type="string", nullable=true, example="https://farmsupplies.com", description="Website URL of the supplier"),
 *     @OA\Property(property="tax_number", type="string", nullable=true, example="TAX123456", description="Tax identification number"),
 *     @OA\Property(property="address", type="string", example="123 Farm Road", description="Street address"),
 *     @OA\Property(property="city", type="string", example="Springfield", description="City"),
 *     @OA\Property(property="state", type="string", example="IL", description="State or region"),
 *     @OA\Property(property="postal_code", type="string", example="62701", description="Postal code"),
 *     @OA\Property(property="country", type="string", example="USA", description="Country"),
 *     @OA\Property(property="latitude", type="number", format="float", nullable=true, example=39.7817, description="Latitude coordinate"),
 *     @OA\Property(property="longitude", type="number", format="float", nullable=true, example=-89.6501, description="Longitude coordinate"),
 *     @OA\Property(property="type", type="string", example="vendor", description="Type of supplier"),
 *     @OA\Property(property="product_type", type="string", example="feed", description="Type of products supplied"),
 *     @OA\Property(property="shop_name", type="string", nullable=true, example="Farm Store", description="Name of the supplier's shop"),
 *     @OA\Property(property="business_registration_number", type="string", nullable=true, example="BRN789123", description="Business registration number"),
 *     @OA\Property(property="contract_start_date", type="string", format="date", nullable=true, example="2025-01-01", description="Start date of the contract"),
 *     @OA\Property(property="contract_end_date", type="string", format="date", nullable=true, example="2025-12-31", description="End date of the contract"),
 *     @OA\Property(property="account_holder", type="string", nullable=true, example="Farm Supplies Inc.", description="Bank account holder name"),
 *     @OA\Property(property="account_number", type="string", nullable=true, example="1234567890", description="Bank account number"),
 *     @OA\Property(property="bank_name", type="string", nullable=true, example="First National Bank", description="Name of the bank"),
 *     @OA\Property(property="bank_branch", type="string", nullable=true, example="Springfield Branch", description="Bank branch name"),
 *     @OA\Property(property="swift_code", type="string", nullable=true, example="FNUS33XXX", description="SWIFT code"),
 *     @OA\Property(property="iban", type="string", nullable=true, example="US12345678901234567890", description="IBAN"),
 *     @OA\Property(property="supplier_importance", type="string", example="high", description="Importance level of the supplier"),
 *     @OA\Property(property="inventory_level", type="integer", example=100, description="Current inventory level"),
 *     @OA\Property(property="reorder_point", type="integer", example=20, description="Reorder point for inventory"),
 *     @OA\Property(property="minimum_order_quantity", type="integer", example=50, description="Minimum order quantity"),
 *     @OA\Property(property="lead_time_days", type="integer", example=5, description="Lead time in days"),
 *     @OA\Property(property="payment_terms", type="string", example="Net 30", description="Payment terms"),
 *     @OA\Property(property="credit_limit", type="number", format="float", example=5000.00, description="Credit limit"),
 *     @OA\Property(property="currency", type="string", example="USD", description="Currency used"),
 *     @OA\Property(property="tax_rate", type="number", format="float", example=0.08, description="Tax rate"),
 *     @OA\Property(property="supplier_rating", type="number", format="float", example=4.5, description="Supplier performance rating"),
 *     @OA\Property(property="status", type="string", example="active", description="Supplier status"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Reliable supplier", description="Additional notes"),
 *     @OA\Property(
 *         property="contact",
 *         type="object",
 *         description="Primary contact information",
 *         @OA\Property(property="name", type="string", example="Jane Doe", description="Contact name"),
 *         @OA\Property(property="position", type="string", nullable=true, example="Sales Manager", description="Contact position"),
 *         @OA\Property(property="email", type="string", format="email", nullable=true, example="jane@farmsupplies.com", description="Contact email"),
 *         @OA\Property(property="phone", type="string", nullable=true, example="+1234567891", description="Contact phone")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Timestamp when the supplier was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="Timestamp when the supplier was last updated")
 * )
 */
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