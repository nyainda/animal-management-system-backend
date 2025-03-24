<?php

namespace App\Http\Resources\TransactionPayment;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     schema="TransactionPaymentCollection",
 *     type="object",
 *     title="Transaction Payment Collection",
 *     description="A paginated collection of transaction payments",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/TransactionPaymentResource")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="total", type="integer", example=50, description="Total number of payments"),
 *         @OA\Property(property="count", type="integer", example=15, description="Number of payments in current page"),
 *         @OA\Property(property="per_page", type="integer", example=15, description="Items per page"),
 *         @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
 *         @OA\Property(property="total_pages", type="integer", example=4, description="Total number of pages")
 *     )
 * )
 */
class TransactionPaymentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages' => $this->resource->lastPage(),
            ],
        ];
    }
}