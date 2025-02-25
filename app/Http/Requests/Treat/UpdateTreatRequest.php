<?php

namespace App\Http\Requests\Treat;

use App\Enums\AdministrationRoute;
use App\Enums\TreatmentStatus;
use App\Enums\TreatmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateTreatRequest extends FormRequest
{
    public function authorize(): bool
    {
        // We'll handle deeper authorization in the controller
        return true;
    }

    public function rules(): array
    {
        // Fields that should never be updated


        // Only allow updates if treatment is not completed unless user has special permission
        /** @var \App\Models\User $user */
           $user = Auth::user();

            if ($this->treat->status === TreatmentStatus::COMPLETED->value && (!$user || !$user->can('update-completed-treatments'))) {
             return [];
            }

        return [
            'status' => ['sometimes', new Enum(TreatmentStatus::class)],
            'type' => ['sometimes', new Enum(TreatmentType::class)],

            // Treatment details
            'product' => 'sometimes|nullable|string|max:255',
            'batch_number' => 'sometimes|nullable|string|max:255',
            'manufacturer' => 'sometimes|nullable|string|max:255',
            'expiry_date' => 'sometimes|nullable|date',

            // Dosage and administration
            'dosage' => 'sometimes|nullable|numeric|min:0',
            'inventory_used' => 'sometimes|nullable|numeric|min:0',
            'unit' => 'sometimes|nullable|string|max:50',
            'administration_route' => ['sometimes', 'nullable', new Enum(AdministrationRoute::class)],
            'administration_site' => 'sometimes|nullable|string|max:255',

            // Scheduling and follow-up
            'withdrawal_days' => 'sometimes|nullable|integer|min:0',
            'withdrawal_date' => 'sometimes|nullable|date|after_or_equal:treatment_date',
            'next_treatment_date' => 'sometimes|nullable|date|after:treatment_date',
            'requires_followup' => 'sometimes|boolean',

            // Personnel and cost details
            'technician_name' => 'sometimes|nullable|string|max:255',
            'technician_id' => 'sometimes|nullable|uuid',
            'currency' => 'sometimes|nullable|string|size:3',
            'unit_cost' => 'sometimes|nullable|numeric|min:0',
            'total_cost' => 'sometimes|nullable|numeric|min:0',
            'record_transaction' => 'sometimes|boolean',

            // Documentation
            'notes' => 'sometimes|nullable|string',
            'treatment_date' => 'sometimes|date',
            'treatment_time' => 'sometimes|nullable|date_format:H:i',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string',
            'attachment_path' => 'sometimes|nullable|string',

            // Medical information
            'reason' => 'sometimes|nullable|string',
            'diagnosis' => 'sometimes|nullable|string',
            'outcome' => 'sometimes|nullable|string',
            'vital_signs' => 'sometimes|nullable|array',
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

    protected function prepareForValidation()
    {
        // Remove any protected fields from the request
        foreach ($this->protectedFields as $field) {
            $this->request->remove($field);
        }
    }

    /**
     * Fields that should never be updated
     *
     * @var array
     */
    protected $protectedFields = [
        'animal_id',
        'user_id',
        'created_at',
        'verified_by',
        'verified_at',
        'is_verified'
    ];

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
