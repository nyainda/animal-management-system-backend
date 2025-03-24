<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transactions;
use App\Models\Animal;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Http\Resources\Transactions\TransactionResource;
use App\Http\Resources\Transactions\TransactionCollection;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class TransactionController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/transactions",
     *     tags={"Transactions"},
     *     summary="List transactions for a specific animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="transaction_type",
     *         in="query",
     *         description="Filter by transaction type",
     *         required=false,
     *         @OA\Schema(type="string", example="sale")
     *     ),
     *     @OA\Parameter(
     *         name="transaction_status",
     *         in="query",
     *         description="Filter by transaction status",
     *         required=false,
     *         @OA\Schema(type="string", example="pending")
     *     ),
     *     @OA\Parameter(
     *         name="buyer_id",
     *         in="query",
     *         description="Filter by buyer ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="seller_id",
     *         in="query",
     *         description="Filter by seller ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of transactions per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TransactionCollection"),
     *             @OA\Property(property="message", type="string", example="Transactions retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     )
     * )
     */
    public function index(Request $request, Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $transactions = Transactions::query()
            ->where('animal_id', $animal->id)
            ->when($request->has('transaction_type'), function ($query) use ($request) {
                return $query->where('transaction_type', $request->transaction_type);
            })
            ->when($request->has('transaction_status'), function ($query) use ($request) {
                return $query->where('transaction_status', $request->transaction_status);
            })
            ->when($request->has('buyer_id'), function ($query) use ($request) {
                return $query->where('buyer_id', $request->buyer_id);
            })
            ->when($request->has('seller_id'), function ($query) use ($request) {
                return $query->where('seller_id', $request->seller_id);
            })
            ->with(['seller', 'buyer', 'animal', 'payments'])
            ->orderBy('transaction_date', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->successResponse(
            new TransactionCollection($transactions),
            'Transactions retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Post(
     *     path="/api/animals/{animal}/transactions",
     *     tags={"Transactions"},
     *     summary="Create a new transaction for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_type", type="string", example="sale", description="Type of transaction"),
     *             @OA\Property(property="price", type="number", format="float", example=1000.00, description="Base price"),
     *             @OA\Property(property="tax_amount", type="number", format="float", nullable=true, example=50.00, description="Tax amount"),
     *             @OA\Property(property="currency", type="string", example="USD", description="Currency"),
     *             @OA\Property(property="transaction_date", type="string", format="date-time", example="2025-03-24T10:00:00Z", description="Transaction date"),
     *             @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true, example="2025-03-26T10:00:00Z", description="Delivery date"),
     *             @OA\Property(property="details", type="string", nullable=true, example="Sale of livestock", description="Transaction details"),
     *             @OA\Property(property="payment_method", type="string", nullable=true, example="credit_card", description="Payment method"),
     *             @OA\Property(property="payment_reference", type="string", nullable=true, example="REF12345", description="Payment reference"),
     *             @OA\Property(property="deposit_amount", type="number", format="float", nullable=true, example=500.00, description="Deposit amount"),
     *             @OA\Property(property="payment_due_date", type="string", format="date-time", nullable=true, example="2025-04-24T10:00:00Z", description="Payment due date"),
     *             @OA\Property(property="seller_id", type="integer", nullable=true, example=1, description="Seller user ID"),
     *             @OA\Property(property="buyer_id", type="integer", nullable=true, example=2, description="Buyer user ID"),
     *             @OA\Property(property="seller_name", type="string", nullable=true, example="John Doe", description="Seller name (non-registered)"),
     *             @OA\Property(property="buyer_name", type="string", nullable=true, example="Jane Doe", description="Buyer name (non-registered)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transaction created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TransactionResource"),
     *             @OA\Property(property="message", type="string", example="Transaction created successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreTransactionRequest $request, Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $animal) {
            $validated['animal_id'] = $animal->id;
            $validated['total_amount'] = $validated['price'] + ($validated['tax_amount'] ?? 0);
            $validated['balance_due'] = isset($validated['deposit_amount'])
                ? $validated['total_amount'] - $validated['deposit_amount']
                : $validated['total_amount'];
            $validated['deposit_amount'] = $validated['deposit_amount'] ?? 0;
            $validated['created_by'] = Auth::id();

            $transaction = Transactions::create($validated);

            return $this->successResponse(
                new TransactionResource($transaction),
                'Transaction created successfully',
                Response::HTTP_CREATED
            );
        });
    }

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/transactions/{transaction}",
     *     tags={"Transactions"},
     *     summary="Show a specific transaction for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         description="ID of the transaction",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TransactionResource"),
     *             @OA\Property(property="message", type="string", example="Transaction retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The transaction does not belong to this animal")
     *         )
     *     )
     * )
     */
    public function show(Animal $animal, Transactions $transaction)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        if ($transaction->animal_id !== $animal->id) {
            return $this->errorResponse('The transaction does not belong to this animal', Response::HTTP_NOT_FOUND);
        }

        $transaction->load(['seller', 'buyer', 'animal', 'payments']);

        return $this->successResponse(
            new TransactionResource($transaction),
            'Transaction retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Put(
     *     path="/api/animals/{animal}/transactions/{transaction}",
     *     tags={"Transactions"},
     *     summary="Update a specific transaction for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         description="ID of the transaction",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_type", type="string", example="purchase", description="Updated transaction type"),
     *             @OA\Property(property="price", type="number", format="float", example=1200.00, description="Updated price"),
     *             @OA\Property(property="tax_amount", type="number", format="float", nullable=true, example=60.00, description="Updated tax amount"),
     *             @OA\Property(property="deposit_amount", type="number", format="float", nullable=true, example=600.00, description="Updated deposit amount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TransactionResource"),
     *             @OA\Property(property="message", type="string", example="Transaction updated successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The transaction does not belong to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateTransactionRequest $request, Animal $animal, Transactions $transaction)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        if ($transaction->animal_id !== $animal->id) {
            return $this->errorResponse('The transaction does not belong to this animal', Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $transaction) {
            if (isset($validated['price']) || isset($validated['tax_amount'])) {
                $price = $validated['price'] ?? $transaction->price;
                $tax = $validated['tax_amount'] ?? $transaction->tax_amount;
                $validated['total_amount'] = $price + $tax;
                $deposit = $validated['deposit_amount'] ?? $transaction->deposit_amount;
                $validated['balance_due'] = $validated['total_amount'] - $deposit;
            } elseif (isset($validated['deposit_amount'])) {
                $validated['balance_due'] = $transaction->total_amount - $validated['deposit_amount'];
            }

            if (isset($validated['deposit_amount']) || isset($validated['total_amount'])) {
                $depositAmount = $validated['deposit_amount'] ?? $transaction->deposit_amount;
                $totalAmount = $validated['total_amount'] ?? $transaction->total_amount;

                if ($depositAmount >= $totalAmount) {
                    $validated['transaction_status'] = 'paid';
                } elseif ($depositAmount > 0) {
                    $validated['transaction_status'] = 'deposit_paid';
                } else {
                    $validated['transaction_status'] = 'pending';
                }
            }

            $transaction->update($validated);

            return $this->successResponse(
                new TransactionResource($transaction),
                'Transaction updated successfully',
                Response::HTTP_OK
            );
        });
    }

    /**
     * @OA\Delete(
     *     path="/api/animals/{animal}/transactions/{transaction}",
     *     tags={"Transactions"},
     *     summary="Delete a specific transaction for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         description="ID of the transaction",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Transaction deleted successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The transaction does not belong to this animal")
     *         )
     *     )
     * )
     */
    public function destroy(Animal $animal, Transactions $transaction)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        if ($transaction->animal_id !== $animal->id) {
            return $this->errorResponse('The transaction does not belong to this animal', Response::HTTP_NOT_FOUND);
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->payments()->delete();
            $transaction->delete();

            return $this->successResponse(
                null,
                'Transaction deleted successfully',
                Response::HTTP_OK
            );
        });
    }

    /**
     * @OA\Get(
     *     path="/api/animals/{animal}/transactions/summary",
     *     tags={"Transactions"},
     *     summary="Get transaction summary for an animal",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="animal",
     *         in="path",
     *         required=true,
     *         description="UUID of the animal",
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="overview",
     *                     type="object",
     *                     @OA\Property(property="total_transactions", type="integer", example=10),
     *                     @OA\Property(property="total_value", type="string", example="10500.00"),
     *                     @OA\Property(property="pending_amount", type="string", example="2500.00"),
     *                     @OA\Property(property="completed_transactions", type="integer", example=5),
     *                     @OA\Property(property="average_transaction_value", type="string", example="1050.00"),
     *                     @OA\Property(property="highest_transaction", type="string", example="2000.00"),
     *                     @OA\Property(property="lowest_transaction", type="string", example="500.00")
     *                 ),
     *                 @OA\Property(
     *                     property="status_distribution",
     *                     type="object",
     *                     @OA\Property(property="pending", type="integer", example=3),
     *                     @OA\Property(property="paid", type="integer", example=5)
     *                 ),
     *                 @OA\Property(
     *                     property="recent_transactions",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="transaction_type", type="string", example="sale"),
     *                         @OA\Property(property="total_amount", type="string", example="1050.00"),
     *                         @OA\Property(property="balance_due", type="string", example="550.00"),
     *                         @OA\Property(property="transaction_date", type="string", example="2025-03-24 10:00:00"),
     *                         @OA\Property(property="transaction_status", type="string", example="deposit_paid"),
     *                         @OA\Property(property="seller_name", type="string", example="John Doe"),
     *                         @OA\Property(property="buyer_name", type="string", example="Jane Doe"),
     *                         @OA\Property(property="payment_progress", type="number", format="float", example=47.6)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="monthly_trends",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="month", type="string", example="2025-03"),
     *                         @OA\Property(property="transaction_count", type="integer", example=2),
     *                         @OA\Property(property="total_amount", type="string", example="2100.00")
     *                     )
     *                 ),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time", example="2025-03-24T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Transaction statistics retrieved successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have access to this animal")
     *         )
     *     )
     * )
     */
    public function summary(Request $request, Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $transactionStats = Transactions::where('animal_id', $animal->id)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN transaction_status = \'pending\' THEN balance_due ELSE 0 END) as pending_amount,
                SUM(CASE WHEN transaction_status = \'completed\' THEN 1 ELSE 0 END) as completed_transactions,
                SUM(total_amount) as total_value,
                AVG(total_amount) as average_transaction_value,
                MIN(total_amount) as min_transaction_value,
                MAX(total_amount) as max_transaction_value
            ')
            ->first();

        $statusDistribution = Transactions::where('animal_id', $animal->id)
            ->groupBy('transaction_status')
            ->selectRaw('transaction_status, COUNT(*) as count')
            ->pluck('count', 'transaction_status');

        $recentTransactions = Transactions::with(['seller', 'buyer', 'animal'])
            ->where('animal_id', $animal->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_type' => $transaction->transaction_type,
                    'total_amount' => number_format($transaction->total_amount, 2),
                    'balance_due' => number_format($transaction->balance_due, 2),
                    'transaction_date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                    'transaction_status' => $transaction->transaction_status,
                    'seller_name' => $transaction->seller_id ? $transaction->seller->name : $transaction->seller_name,
                    'buyer_name' => $transaction->buyer_id ? $transaction->buyer->name : $transaction->buyer_name,
                    'payment_progress' => $transaction->total_amount > 0
                        ? round(($transaction->total_amount - $transaction->balance_due) / $transaction->total_amount * 100, 1)
                        : 0,
                ];
            });

        $monthlyTrends = Transactions::where('animal_id', $animal->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('
                DATE_TRUNC(\'month\', created_at) as month,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($trend) {
                return [
                    'month' => \Carbon\Carbon::parse($trend->month)->format('Y-m'),
                    'transaction_count' => $trend->transaction_count,
                    'total_amount' => number_format($trend->total_amount, 2),
                ];
            });

        $stats = [
            'overview' => [
                'total_transactions' => (int) $transactionStats->total_transactions,
                'total_value' => number_format($transactionStats->total_value ?? 0, 2),
                'pending_amount' => number_format($transactionStats->pending_amount ?? 0, 2),
                'completed_transactions' => (int) $transactionStats->completed_transactions,
                'average_transaction_value' => number_format($transactionStats->average_transaction_value ?? 0, 2),
                'highest_transaction' => number_format($transactionStats->max_transaction_value ?? 0, 2),
                'lowest_transaction' => number_format($transactionStats->min_transaction_value ?? 0, 2),
            ],
            'status_distribution' => $statusDistribution->toArray(),
            'recent_transactions' => $recentTransactions,
            'monthly_trends' => $monthlyTrends,
            'currency' => 'USD',
            'last_updated' => now()->toDateTimeString(),
        ];

        return $this->successResponse(
            $stats,
            'Transaction statistics retrieved successfully',
            Response::HTTP_OK
        );
    }
}