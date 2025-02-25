<?php

namespace App\Http\Requests\Feed;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

           // 'feed_type_id' => 'required|uuid',
           'feed_inventory_id' => 'required|uuid',
          //  'schedule_id' => 'nullable|uuid',
            'amount' => 'required|numeric',
            'unit' => 'required|string',
            'cost' => 'required|numeric',
            'currency' => 'required|string',
            'fed_at' => 'required|date',
            'notes' => 'nullable|string',
            'feeding_method' => 'nullable|string',
            'consumption_notes' => 'nullable|string',
        ];
    }
}
