<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeedInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'purchase_date' => 'sometimes|date',
            'expiry_date' => 'nullable|date|after:purchase_date',
            'batch_number' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
        ];
    }
}
