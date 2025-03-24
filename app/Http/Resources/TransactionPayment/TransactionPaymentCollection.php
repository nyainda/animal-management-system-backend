<?php

namespace App\Http\Resources\TransactionPayment;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Represents a collection of transaction payments
 */
class TransactionPaymentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => TransactionPaymentResource::collection($this->collection),
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => (int) $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
            ],
        ];
    }
}