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

class TransactionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the transactions for a specific animal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Animal  $animal
     * @return \App\Http\Resources\Transactions\TransactionCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Animal $animal)
    {
        // Check if user has access to the animal
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
     * Store a newly created transaction for a specific animal.
     *
     * @param  \App\Http\Requests\Transactions\StoreTransactionRequest  $request
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTransactionRequest $request, Animal $animal)
    {
        // Check if user has access to the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $animal) {
            // Set the animal_id automatically from the route parameter
            $validated['animal_id'] = $animal->id;

            // Calculate total amount
            $validated['total_amount'] = $validated['price'] + ($validated['tax_amount'] ?? 0);

            // Calculate balance due
            if (isset($validated['deposit_amount'])) {
                $validated['balance_due'] = $validated['total_amount'] - $validated['deposit_amount'];
            } else {
                $validated['balance_due'] = $validated['total_amount'];
                $validated['deposit_amount'] = 0;
            }

            // Set created_by to authenticated user
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
     * Display the specified transaction for a specific animal.
     *
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\Transactions  $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Animal $animal, Transactions $transaction)
    {
        // Check if user has access to the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Check if the transaction belongs to the specified animal
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
     * Update the specified transaction for a specific animal.
     *
     * @param  \App\Http\Requests\Transactions\UpdateTransactionRequest  $request
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\Transactions  $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTransactionRequest $request, Animal $animal, Transactions $transaction)
    {
        // Check if user has access to the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Check if the transaction belongs to the specified animal
        if ($transaction->animal_id !== $animal->id) {
            return $this->errorResponse('The transaction does not belong to this animal', Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $transaction) {
            // Recalculate total_amount if price or tax changed
            if (isset($validated['price']) || isset($validated['tax_amount'])) {
                $price = $validated['price'] ?? $transaction->price;
                $tax = $validated['tax_amount'] ?? $transaction->tax_amount;
                $validated['total_amount'] = $price + $tax;

                // Recalculate balance due
                $deposit = $validated['deposit_amount'] ?? $transaction->deposit_amount;
                $validated['balance_due'] = $validated['total_amount'] - $deposit;
            } elseif (isset($validated['deposit_amount'])) {
                // Only deposit amount changed
                $validated['balance_due'] = $transaction->total_amount - $validated['deposit_amount'];
            }

            // Update transaction status if needed
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
     * Remove the specified transaction for a specific animal.
     *
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\Transactions  $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Animal $animal, Transactions $transaction)
    {
        // Check if user has access to the animal
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Check if the transaction belongs to the specified animal
        if ($transaction->animal_id !== $animal->id) {
            return $this->errorResponse('The transaction does not belong to this animal', Response::HTTP_NOT_FOUND);
        }

        return DB::transaction(function () use ($transaction) {
            // Delete all related payments first
            $transaction->payments()->delete();

            // Delete the transaction
            $transaction->delete();

            return $this->successResponse(
                null,
                'Transaction deleted successfully',
                Response::HTTP_OK
            );
        });
    }

    /**
 * Get transactions summary/statistics for a specific animal.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \App\Models\Animal  $animal
 * @return \Illuminate\Http\JsonResponse
 */
public function summary(Request $request, Animal $animal)
{
    // Check if user has access to the animal
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
    }

    // Use a single query with aggregates to improve performance
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

    // Get status distribution
    $statusDistribution = Transactions::where('animal_id', $animal->id)
        ->groupBy('transaction_status')
        ->selectRaw('transaction_status, COUNT(*) as count')
        ->pluck('count', 'transaction_status');

    // Get recent transactions with additional useful fields
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

    // Get monthly trends (last 6 months)
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

    // Organize stats into meaningful segments
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
