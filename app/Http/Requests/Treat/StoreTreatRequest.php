<?php

namespace App\Http\Requests\Treat;

use App\Enums\AdministrationRoute;
use App\Enums\TreatmentStatus;
use App\Enums\TreatmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreTreatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(TreatmentType::class)],
            'status' => ['required', new Enum(TreatmentStatus::class)],

            // Treatment details
            'product' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',

            // Dosage and administration
            'dosage' => 'nullable|numeric|min:0',
            'inventory_used' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'administration_route' => ['nullable', new Enum(AdministrationRoute::class)],
            'administration_site' => 'nullable|string|max:255',

            // Scheduling and follow-up
            'withdrawal_days' => 'nullable|integer|min:0',
            'withdrawal_date' => 'nullable|date|after_or_equal:treatment_date',
            'next_treatment_date' => 'nullable|date|after:treatment_date',
            'requires_followup' => 'boolean',

            // Personnel and cost details
            'technician_name' => 'nullable|string|max:255',
            'technician_id' => 'nullable|uuid',
            'currency' => 'nullable|string|size:3',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'record_transaction' => 'boolean',

            // Documentation
            'notes' => 'nullable|string',
            'treatment_date' => 'required|date',
            'treatment_time' => 'nullable|date_format:H:i',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'attachment_path' => 'nullable|string',

            // Medical information
            'reason' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'outcome' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.*' => 'numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'withdrawal_date.after_or_equal' => 'The withdrawal date must be after or equal to the treatment date.',
            'next_treatment_date.after' => 'The next treatment date must be after the treatment date.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
