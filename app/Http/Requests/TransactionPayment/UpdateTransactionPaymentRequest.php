<?php

namespace App\Http\Requests\TransactionPayment;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TransactionPaymentMethod;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateTransactionPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
