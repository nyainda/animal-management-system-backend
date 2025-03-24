<?php

namespace App\Http\Resources\TransactionPayment;

use Illuminate\Http\Resources\Json\JsonResource;
// use App\Http\Resources\User\UserBasicResource;

/**
 * @OA\Schema(
 *     schema="TransactionPaymentResource",
 *     type="object",
 *     title="Transaction Payment Resource",
 *     description="A resource representing a transaction payment",
 *     @OA\Property(property="id", type="integer", example=1, description="Unique identifier of the payment"),
 *     @OA\Property(property="transaction_id", type="integer", example=1, description="ID of the associated transaction"),
 *     @OA\Property(property="amount", type="number", format="float", example=500.00, description="Payment amount"),
 *     @OA\Property(property="payment_method", type="string", example="credit_card", description="Payment method"),
 *     @OA\Property(property="payment_reference", type="string", nullable=true, example="REF123", description="Payment reference"),
 *     @OA\Property(property="payment_date", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Payment date and time"),
 *     @OA\Property(property="payment_status", type="string", example="completed", description="Payment status"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="Initial deposit", description="Payment notes"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-24T12:00:00Z", description="Last update timestamp")
 * )
 */
class TransactionPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}