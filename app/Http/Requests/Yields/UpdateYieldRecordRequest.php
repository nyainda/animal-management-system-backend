<?php

namespace App\Http\Requests\Yields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateYieldRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Product Category nested validation
            'product_category' => 'sometimes|array',
            'product_category.name' => 'required_with:product_category|string|max:255',
            'product_category.description' => 'nullable|string',
            'product_category.measurement_unit' => 'required_with:product_category|string',

            // Product Grade nested validation
            'product_grade' => 'sometimes|array',
            'product_grade.name' => 'required_with:product_grade|string|max:255',
            'product_grade.description' => 'nullable|string',
            'product_grade.price_modifier' => 'nullable|numeric|min:0',

            // Production Method nested validation
            'production_method' => 'sometimes|array',
            'production_method.method_name' => [
                'required_with:production_method',
                'string',
                'max:255',
                Rule::unique('production_methods', 'method_name')
                    ->where(function ($query) {
                        $categoryId = $this->route()->parameter('category');
                        if (!$categoryId && isset($this->category)) {
                            $categoryId = $this->category->id;
                        }
                        return $categoryId ? $query->where('product_category_id', $categoryId) : $query;
                    })
                    ->ignore($this->route('production_method')),
            ],
            'production_method.description' => 'nullable|string',
            'production_method.requires_certification' => 'required_with:production_method|boolean',
            'production_method.is_active' => 'boolean',

            // Collector nested validation
            'collector' => 'nullable|array',
            'collector.name' => 'required_with:collector|string|max:255',
            'collector.contact_info' => 'nullable|string',

            // Storage Location nested validation
            'storage_location' => 'sometimes|array',
            'storage_location.name' => 'required_with:storage_location|string|max:255',
            'storage_location.location_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('storage_locations', 'location_code')->ignore($this->route('storage_location')),
            ],
            'storage_location.description' => 'nullable|string',
            'storage_location.storage_conditions' => 'nullable|array',
            'storage_location.storage_conditions.*' => 'string',
            'storage_location.is_active' => 'boolean',
            'storage_location.storage_conditions.temperature' => 'nullable|numeric|between:-30,40', // Changed from string to numeric
            'storage_location.storage_conditions.humidity' => 'nullable|numeric|between:0,100', // Changed from string to numeric
            // Main yield record fields
            'quantity' => 'sometimes|numeric|min:0.01',
            'price_per_unit' => 'sometimes|numeric|min:0',
            'total_price' => 'sometimes|numeric|min:0',
            'production_date' => 'sometimes|date|before_or_equal:today',
            'production_time' => 'sometimes|date_format:H:i',
            'quality_status' => [
                'sometimes',
                Rule::in(['Passed', 'Failed', 'Under Review'])
            ],
            'quality_notes' => 'nullable|string|max:1000',
            'trace_number' => 'nullable|string|max:50',
            'weather_conditions' => 'nullable|array',
            'weather_conditions.temperature' => 'nullable|numeric|between:-50,60',
            'weather_conditions.humidity' => 'nullable|numeric|between:0,100',
            'storage_conditions' => 'nullable|array',
            'storage_conditions.temperature' => 'nullable|numeric|between:-30,40',
            'storage_conditions.humidity' => 'nullable|numeric|between:0,100',
            'is_organic' => 'boolean',
            'certification_number' => 'nullable|string|max:50',
            'additional_attributes' => 'nullable|array',
            'additional_attributes.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'product_category.required' => 'Product category information is required.',
            'product_category.name.required_with' => 'Product category name is required when updating category.',
            'product_grade.name.required_with' => 'Product grade name is required when updating grade.',
            'production_method.method_name.required_with' => 'Production method name is required when updating method.',
            'collector.name.required_with' => 'Collector name is required when updating collector information.',
            'storage_location.name.required_with' => 'Storage location name is required when updating storage information.',
            'quantity.min' => 'The quantity must be greater than zero.',
            'price_per_unit.min' => 'The price per unit must be greater than or equal to zero.',
            'production_date.before_or_equal' => 'The production date cannot be in the future.',
            'weather_conditions.temperature.between' => 'The weather temperature must be between -50°C and 60°C.',
            'weather_conditions.humidity.between' => 'The weather humidity must be between 0% and 100%.',
            'storage_conditions.temperature.between' => 'The storage temperature must be between -30°C and 40°C.',
            'storage_conditions.humidity.between' => 'The storage humidity must be between 0% and 100%.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->transformJsonStrings('weather_conditions');
        $this->transformJsonStrings('storage_conditions');
        $this->transformJsonStrings('additional_attributes');

        if ($this->has('is_organic')) {
            $this->merge([
                'is_organic' => filter_var($this->is_organic, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ]);
        }
    }

    private function transformJsonStrings($field)
    {
        if ($this->has($field) && is_string($this->$field)) {
            try {
                $decoded = json_decode($this->$field, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge([$field => $decoded]);
                }
            } catch (\Exception $e) {
                // If JSON decode fails, let validation handle it
            }
        }
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
