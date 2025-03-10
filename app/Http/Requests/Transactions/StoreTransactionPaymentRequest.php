<?php

namespace App\Http\Requests\Transactions;

use App\Enums\TransactionPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add custom authorization logic here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
{
    return [
       // 'transaction_id' => 'required|exists:transactions,id',
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => ['required', new Enum(TransactionPaymentMethod::class)],
        'payment_reference' => 'nullable|string|max:255',
        'payment_date' => 'required|date',
        'payment_status' => 'required|string|max:255',
        'notes' => 'nullable|string',
    ];
}

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $formattedErrors = [];

        foreach ($errors->messages() as $key => $messages) {
            foreach ($messages as $message) {
                $formattedErrors[$key] = $message;
            }
        }

        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors,
            ], 422)
        );
    }
}
