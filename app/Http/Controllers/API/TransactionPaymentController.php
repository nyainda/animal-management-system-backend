<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Transactions;
use App\Models\TransactionPayment;
use App\Http\Requests\Transactions\StoreTransactionPaymentRequest;
use App\Http\Requests\TransactionPayment\UpdateTransactionPaymentRequest;
use App\Http\Resources\TransactionPayment\TransactionPaymentResource;
use App\Http\Resources\TransactionPayment\TransactionPaymentCollection;
use App\Traits\ApiResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionPaymentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of all payments for a specific animal.
     *
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $payments = TransactionPayment::whereIn('transaction_id', function ($query) use ($animal) {
                $query->select('id')
                      ->from('transactions')
                      ->where('animal_id', $animal->id);
            })
            ->orderBy('payment_date', 'desc')
            ->paginate(request()->per_page ?? 15);

        return $this->successResponse(
            new TransactionPaymentCollection($payments),
            'Payment list retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Store a new payment for a specific animal.
     *
     * @param  \App\Http\Requests\Transactions\StoreTransactionPaymentRequest  $request
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTransactionPaymentRequest $request, Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $animal) {
            // If transaction_id is not provided, try to find the most recent unpaid transaction
            if (!isset($validated['transaction_id'])) {
                $transaction = Transactions::where('animal_id', $animal->id)
                    ->whereIn('transaction_status', ['pending', 'deposit_paid'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$transaction) {
                    return $this->errorResponse('No unpaid transaction found for this animal', Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $validated['transaction_id'] = $transaction->id;
            } else {
                // Verify the transaction belongs to the animal
                $transaction = Transactions::where('id', $validated['transaction_id'])
                    ->where('animal_id', $animal->id)
                    ->firstOrFail();
            }

            $validated['recorded_by'] = Auth::id();

            $payment = TransactionPayment::create($validated);

            // Update the transaction's deposit_amount and balance_due
            $transaction->deposit_amount = ($transaction->deposit_amount ?? 0) + $validated['amount'];
            $transaction->balance_due = $transaction->total_amount - $transaction->deposit_amount;

            if ($transaction->balance_due <= 0) {
                $transaction->transaction_status = 'completed'; // or 'paid' if it exists in your enum
            } elseif ($transaction->deposit_amount > 0) {
                $transaction->transaction_status = 'deposit_paid'; // changed from 'partially_paid'
            } else {
                $transaction->transaction_status = 'pending';
            }

            $transaction->save();

            return $this->successResponse(
                new TransactionPaymentResource($payment),
                'Payment created successfully',
                Response::HTTP_CREATED
            );
        });
    }

    /**
     * Display the specified payment for a specific animal.
     *
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\TransactionPayment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Animal $animal, TransactionPayment $payment)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Verify the payment belongs to a transaction for this animal
        if (!Transactions::where('id', $payment->transaction_id)
                ->where('animal_id', $animal->id)
                ->exists()) {
            return $this->errorResponse('Payment not found for this animal', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            new TransactionPaymentResource($payment),
            'Payment retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified payment for a specific animal.
     *
     * @param  \App\Http\Requests\TransactionPayment\UpdateTransactionPaymentRequest  $request
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\TransactionPayment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTransactionPaymentRequest $request, Animal $animal, TransactionPayment $payment)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Verify the payment belongs to a transaction for this animal
        $transaction = Transactions::where('id', $payment->transaction_id)
            ->where('animal_id', $animal->id)
            ->firstOrFail();

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $transaction, $payment) {
            if (isset($validated['amount']) && $validated['amount'] != $payment->amount) {
                $amountDifference = $validated['amount'] - $payment->amount;
                $transaction->deposit_amount += $amountDifference;
                $transaction->balance_due -= $amountDifference;

                if ($transaction->balance_due <= 0) {
                    $transaction->transaction_status = 'paid';
                } elseif ($transaction->deposit_amount > 0) {
                    $transaction->transaction_status = 'partially_paid';
                } else {
                    $transaction->transaction_status = 'pending';
                }

                $transaction->save();
            }

            $payment->update($validated);

            return $this->successResponse(
                new TransactionPaymentResource($payment),
                'Payment updated successfully',
                Response::HTTP_OK
            );
        });
    }

    /**
     * Remove the specified payment for a specific animal.
     *
     * @param  \App\Models\Animal  $animal
     * @param  \App\Models\TransactionPayment  $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Animal $animal, TransactionPayment $payment)
    {
        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
        }

        // Verify the payment belongs to a transaction for this animal
        $transaction = Transactions::where('id', $payment->transaction_id)
            ->where('animal_id', $animal->id)
            ->firstOrFail();

        return DB::transaction(function () use ($transaction, $payment) {
            $transaction->deposit_amount -= $payment->amount;
            $transaction->balance_due += $payment->amount;

            if ($transaction->deposit_amount <= 0) {
                $transaction->transaction_status = 'pending';
                $transaction->deposit_amount = 0;
            } else {
                $transaction->transaction_status = 'deposit_paid';
            }

            $transaction->save();

            $payment->delete();

            return $this->successResponse(
                null,
                'Payment deleted successfully',
                Response::HTTP_OK
            );
        });
    }

    /**
 * Get payment summary/statistics for a specific animal.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \App\Models\Animal  $animal
 * @return \Illuminate\Http\JsonResponse
 */
public function summary(Request $request, Animal $animal)
{
    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse('You do not have access to this animal', Response::HTTP_FORBIDDEN);
    }

    // Base query for payments related to this animal's transactions
    $paymentQuery = TransactionPayment::whereIn('transaction_id', function ($query) use ($animal) {
        $query->select('id')
              ->from('transactions')
              ->where('animal_id', $animal->id);
    });

    // Aggregate statistics
    $paymentStats = $paymentQuery->clone()
        ->selectRaw('
            COUNT(*) as total_payments,
            SUM(amount) as total_paid,
            AVG(amount) as average_payment,
            MIN(amount) as min_payment,
            MAX(amount) as max_payment
        ')
        ->first();

    // Payment method distribution
    $methodDistribution = $paymentQuery->clone()
        ->groupBy('payment_method')
        ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->payment_method => [
                'count' => $item->count,
                'total' => number_format($item->total, 2)
            ]];
        });

    // Recent payments
    $recentPayments = $paymentQuery->clone()
        ->with(['transaction' => function ($query) {
            $query->select('id', 'transaction_type', 'total_amount');
        }])
        ->orderBy('payment_date', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => number_format($payment->amount, 2),
                'payment_date' => $payment->payment_date->format('Y-m-d H:i:s'),
                'payment_method' => $payment->payment_method,
                'transaction_type' => $payment->transaction->transaction_type,
                'transaction_amount' => number_format($payment->transaction->total_amount, 2),
            ];
        });

    // Monthly payment trends (last 6 months)
    $monthlyTrends = $paymentQuery->clone()
        ->where('payment_date', '>=', now()->subMonths(6))
        ->selectRaw('
            DATE_TRUNC(\'month\', payment_date) as month,
            COUNT(*) as payment_count,
            SUM(amount) as total_amount
        ')
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($trend) {
            return [
                'month' => \Carbon\Carbon::parse($trend->month)->format('Y-m'),
                'payment_count' => $trend->payment_count,
                'total_amount' => number_format($trend->total_amount, 2),
            ];
        });

    // Organize stats
    $stats = [
        'overview' => [
            'total_payments' => (int) ($paymentStats->total_payments ?? 0),
            'total_paid' => number_format($paymentStats->total_paid ?? 0, 2),
            'average_payment' => number_format($paymentStats->average_payment ?? 0, 2),
            'min_payment' => number_format($paymentStats->min_payment ?? 0, 2),
            'max_payment' => number_format($paymentStats->max_payment ?? 0, 2),
        ],
        'payment_methods' => $methodDistribution->toArray(),
        'recent_payments' => $recentPayments,
        'monthly_trends' => $monthlyTrends,
        'currency' => 'USD',
        'last_updated' => now()->toDateTimeString(),
    ];

    return $this->successResponse(
        $stats,
        'Payment statistics retrieved successfully',
        Response::HTTP_OK
    );
}
}
