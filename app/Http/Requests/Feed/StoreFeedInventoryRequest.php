<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           // 'feed_type_id' => 'required|uuid|exists:feed_types,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'purchase_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:purchase_date',
            'batch_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic if needed
        });
    }
}
