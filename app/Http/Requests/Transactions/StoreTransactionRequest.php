<?php

namespace App\Http\Requests\Transactions;

use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    public function rules(): array
    {
        return [
            'animal_id' => ['required', 'uuid', 'exists:animals,id'],
            'seller_id' => ['nullable', 'uuid', 'exists:users,id'],
            'buyer_id' => ['nullable', 'uuid', 'exists:users,id'],
            'farm_id' => ['nullable', 'uuid', 'exists:farms,id'],

            'transaction_type' => ['required', new Enum(TransactionType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'transaction_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'details' => ['nullable', 'string'],

            'payment_method' => ['nullable', new Enum(PaymentMethod::class)],
            'payment_reference' => ['nullable', 'string'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'balance_due' => ['nullable', 'numeric', 'min:0'],
            'payment_due_date' => ['nullable', 'date', 'after:transaction_date'],

            'transaction_status' => ['required', new Enum(TransactionStatus::class)],

            // Seller Information
            'seller_name' => ['required_without:seller_id', 'nullable', 'string'],
            'seller_company' => ['nullable', 'string'],
            'seller_tax_id' => ['nullable', 'string'],
            'seller_contact' => ['nullable', 'string'],
            'seller_email' => ['nullable', 'email'],
            'seller_phone' => ['nullable', 'string'],
            'seller_address' => ['nullable', 'string'],
            'seller_city' => ['nullable', 'string'],
            'seller_state' => ['nullable', 'string'],
            'seller_country' => ['nullable', 'string'],
            'seller_postal_code' => ['nullable', 'string'],
            'seller_identification' => ['nullable', 'string'],
            'seller_license_number' => ['nullable', 'string'],

            // Buyer Information
            'buyer_name' => ['required_without:buyer_id', 'nullable', 'string'],
            'buyer_company' => ['nullable', 'string'],
            'buyer_tax_id' => ['nullable', 'string'],
            'buyer_contact' => ['nullable', 'string'],
            'buyer_email' => ['nullable', 'email'],
            'buyer_phone' => ['nullable', 'string'],
            'buyer_address' => ['nullable', 'string'],
            'buyer_city' => ['nullable', 'string'],
            'buyer_state' => ['nullable', 'string'],
            'buyer_country' => ['nullable', 'string'],
            'buyer_postal_code' => ['nullable', 'string'],
            'buyer_identification' => ['nullable', 'string'],
            'buyer_license_number' => ['nullable', 'string'],

            // Documentation
            'invoice_number' => ['nullable', 'string', 'unique:transactions,invoice_number'],
            'contract_number' => ['nullable', 'string', 'unique:transactions,contract_number'],
            'terms_accepted' => ['nullable', 'boolean'],
            'terms_accepted_at' => ['nullable', 'date'],
            'health_certificate_number' => ['nullable', 'string'],
            'transport_license_number' => ['nullable', 'string'],
            'attached_documents' => ['nullable', 'array'],
            'attached_documents.*' => ['string'],

            // Additional Information
            'location_of_sale' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'array'],
            'special_conditions' => ['nullable', 'string'],
            'delivery_instructions' => ['nullable', 'string'],
            'insurance_policy_number' => ['nullable', 'string'],
            'insurance_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}






