<?php
// TransactionResource.php
namespace App\Http\Resources\Transactions;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TransactionPayment\TransactionPaymentResource;
use App\Http\Resources\User\UserBasicResource;
use App\Http\Resources\Animal\AnimalBasicResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            // Uncomment these lines if you need to include related resources and make sure they're properly loaded
            // 'animal' => $this->whenLoaded('animal', function() {
            //     return new AnimalBasicResource($this->animal);
            // }),
            // 'seller' => $this->when($this->seller_id, function() {
            //     return $this->whenLoaded('seller', function() {
            //         return new UserBasicResource($this->seller);
            //     });
            // }),
            // 'buyer' => $this->when($this->buyer_id, function() {
            //     return $this->whenLoaded('buyer', function() {
            //         return new UserBasicResource($this->buyer);
            //     });
            // }),
            // 'created_by' => $this->when($this->created_by, function() {
            //     return $this->whenLoaded('creator', function() {
            //         return new UserBasicResource($this->creator);
            //     });
            // }),

            // Transaction Details
            'transaction_type' => $this->transaction_type,
            'price' => $this->price,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'transaction_date' => $this->transaction_date,
            'delivery_date' => $this->delivery_date,
            'details' => $this->details,

            // Payment Information
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'deposit_amount' => $this->deposit_amount,
            'balance_due' => $this->balance_due,
            'payment_due_date' => $this->payment_due_date,
            'transaction_status' => $this->transaction_status,

            // Seller Information (for non-registered sellers)
            'seller_name' => $this->seller_name,
            'seller_company' => $this->seller_company,
            'seller_tax_id' => $this->seller_tax_id,
            'seller_contact' => $this->seller_contact,
            'seller_email' => $this->seller_email,
            'seller_phone' => $this->seller_phone,
            'seller_address' => $this->seller_address,
            'seller_city' => $this->seller_city,
            'seller_state' => $this->seller_state,
            'seller_country' => $this->seller_country,
            'seller_postal_code' => $this->seller_postal_code,
            'seller_identification' => $this->seller_identification,
            'seller_license_number' => $this->seller_license_number,

            // Buyer Information (for non-registered buyers)
            'buyer_name' => $this->buyer_name,
            'buyer_company' => $this->buyer_company,
            'buyer_tax_id' => $this->buyer_tax_id,
            'buyer_contact' => $this->buyer_contact,
            'buyer_email' => $this->buyer_email,
            'buyer_phone' => $this->buyer_phone,
            'buyer_address' => $this->buyer_address,
            'buyer_city' => $this->buyer_city,
            'buyer_state' => $this->buyer_state,
            'buyer_country' => $this->buyer_country,
            'buyer_postal_code' => $this->buyer_postal_code,
            'buyer_identification' => $this->buyer_identification,
            'buyer_license_number' => $this->buyer_license_number,

            // Documentation
            'invoice_number' => $this->invoice_number,
            'contract_number' => $this->contract_number,
            'terms_accepted' => $this->terms_accepted,
            'terms_accepted_at' => $this->terms_accepted_at,
            'health_certificate_number' => $this->health_certificate_number,
            'transport_license_number' => $this->transport_license_number,
            'attached_documents' => $this->attached_documents ? json_decode($this->attached_documents) : null,

            // Additional Information
            'location_of_sale' => $this->location_of_sale,
            'terms_and_conditions' => $this->terms_and_conditions ? json_decode($this->terms_and_conditions) : null,
            'special_conditions' => $this->special_conditions,
            'delivery_instructions' => $this->delivery_instructions,
            'insurance_policy_number' => $this->insurance_policy_number,
            'insurance_amount' => $this->insurance_amount,

            // Payments
            'payments' => $this->whenLoaded('payments', function() {
                return TransactionPaymentResource::collection($this->payments);
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
