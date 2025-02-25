<?php

namespace App\Http\Requests\Suppliers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('suppliers', 'email')->ignore($this->supplier)],
            'phone' => ['nullable', 'string', Rule::unique('suppliers', 'phone')->ignore($this->supplier)],
            'website' => 'nullable|url',
            'tax_number' => ['nullable', 'string', Rule::unique('suppliers', 'tax_number')->ignore($this->supplier)],
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'type' => 'required|in:feed,medication,equipment,service,other',
            'product_type' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'business_registration_number' => ['nullable', 'string', Rule::unique('suppliers', 'business_registration_number')->ignore($this->supplier)],
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'account_holder' => 'nullable|string',
            'account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_branch' => 'nullable|string',
            'swift_code' => 'nullable|string',
            'iban' => 'nullable|string',
            'supplier_importance' => 'nullable|in:low,medium,high,critical',
            'inventory_level' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'minimum_order_quantity' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'payment_terms' => 'nullable|in:immediate,net15,net30,net60,net90',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'tax_rate' => 'nullable|numeric|between:0,100',
            'supplier_rating' => 'nullable|numeric|between:0,5',
            'status' => 'nullable|in:active,inactive,suspended,blacklisted',
            'notes' => 'nullable|string',
            'meta_data' => ['nullable', function ($attribute, $value, $fail) {
                if (!is_null($value)) {
                    if (is_string($value)) {
                        json_decode($value);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('The meta data must be a valid JSON string.');
                        }
                    } elseif (!is_array($value)) {
                        $fail('The meta data must be either a JSON string or an array.');
                    }
                }
            }],
            'contact_name' => 'nullable|string|max:255',
            'contact_position' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'animal_ids' => 'nullable|array',
            'animal_ids.*' => 'exists:animals,id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('meta_data') && is_array($this->meta_data)) {
            $this->merge([
                'meta_data' => json_encode($this->meta_data)
            ]);
        }
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
