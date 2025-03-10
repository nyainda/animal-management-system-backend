<?php

namespace App\Http\Requests\Transactions;

use App\Enums\TransactionType;
use App\Enums\TransactionPaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'transaction_type' => ['required', new Enum(TransactionType::class)],
            'price' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'transaction_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'details' => 'nullable|string',

            // Payment Information
            'payment_method' => ['nullable', new Enum(TransactionPaymentMethod::class)],
            'payment_reference' => 'nullable|string|max:255',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_due_date' => 'nullable|date',
            'transaction_status' => ['nullable', new Enum(TransactionStatus::class)],

            // Seller Information (only required if seller_id is null)
            'seller_name' => 'required_without:seller_id|nullable|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_tax_id' => 'nullable|string|max:255',
            'seller_contact' => 'nullable|string|max:255',
            'seller_email' => 'nullable|email|max:255',
            'seller_phone' => 'nullable|string|max:255',
            'seller_address' => 'nullable|string|max:255',
            'seller_city' => 'nullable|string|max:255',
            'seller_state' => 'nullable|string|max:255',
            'seller_country' => 'nullable|string|max:255',
            'seller_postal_code' => 'nullable|string|max:255',
            'seller_identification' => 'nullable|string|max:255',
            'seller_license_number' => 'nullable|string|max:255',

            // Buyer Information (only required if buyer_id is null)
            'buyer_name' => 'required_without:buyer_id|nullable|string|max:255',
            'buyer_company' => 'nullable|string|max:255',
            'buyer_tax_id' => 'nullable|string|max:255',
            'buyer_contact' => 'nullable|string|max:255',
            'buyer_email' => 'nullable|email|max:255',
            'buyer_phone' => 'nullable|string|max:255',
            'buyer_address' => 'nullable|string|max:255',
            'buyer_city' => 'nullable|string|max:255',
            'buyer_state' => 'nullable|string|max:255',
            'buyer_country' => 'nullable|string|max:255',
            'buyer_postal_code' => 'nullable|string|max:255',
            'buyer_identification' => 'nullable|string|max:255',
            'buyer_license_number' => 'nullable|string|max:255',

            // Documentation
            'invoice_number' => 'nullable|string|max:255|unique:transactions,invoice_number',
            'contract_number' => 'nullable|string|max:255|unique:transactions,contract_number',
            'terms_accepted' => 'nullable|boolean',
            'terms_accepted_at' => 'nullable|date',
            'health_certificate_number' => 'nullable|string|max:255',
            'transport_license_number' => 'nullable|string|max:255',
            'attached_documents' => 'nullable|json',

            // Additional Information
            'location_of_sale' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|json',
            'special_conditions' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_amount' => 'nullable|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
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
