<?php

namespace App\Http\Resources\Transactions;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     schema="TransactionCollection",
 *     type="object",
 *     title="Transaction Collection",
 *     description="A paginated collection of transactions",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/TransactionResource")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="total", type="integer", example=100, description="Total number of transactions"),
 *         @OA\Property(property="count", type="integer", example=15, description="Number of transactions in current page"),
 *         @OA\Property(property="per_page", type="integer", example=15, description="Items per page"),
 *         @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
 *         @OA\Property(property="total_pages", type="integer", example=7, description="Total number of pages")
 *     )
 * )
 */
class TransactionCollection extends ResourceCollection
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