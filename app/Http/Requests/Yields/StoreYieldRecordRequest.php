<?php

namespace App\Http\Requests\Yields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Animal;
use Illuminate\Contracts\Validation\Validator;
//use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreYieldRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Allow direct creation of related entities
            'product_category' => 'required|array',
            'product_category.name' => 'required|string|max:255',
            'product_category.description' => 'nullable|string',
            'product_category.measurement_unit' => 'required|string',

            'product_grade' => 'required|array',
            'product_grade.name' => 'required|string|max:255',
            'product_grade.description' => 'nullable|string',
            'product_grade.price_modifier' => 'nullable|numeric|min:0',

       'production_method' => 'required|array',
            'production_method.method_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('production_methods', 'method_name')->where(function ($query) {
                    // Get the category ID however you're doing it in your controller:
                    $categoryId = $this->route()->parameter('category'); // Example: From route parameter
                    if (!$categoryId && isset($this->category)){
                        $categoryId = $this->category->id;
                    }
                    if ($categoryId) {
                        return $query->where('product_category_id', $categoryId);
                    }
                    return $query;
                }),
            ],
            'production_method.description' => 'nullable|string',
            'production_method.requires_certification' => 'required|boolean',
            'production_method.is_active' => 'boolean',
           // 'production_method' => 'required|array',
           //'production_method.name' => 'required|string|max:255',

            'collector' => 'nullable|array',
            'collector.name' => 'required_with:collector|string|max:255',
            'collector.contact_info' => 'nullable|string',

            'storage_location' => 'required|array',
            'storage_location.name' => 'required|string|max:255',
            'storage_location.location_code' => 'nullable|string|max:255|unique:storage_locations,location_code', // Unique check
            'storage_location.description' => 'nullable|string',
            'storage_location.storage_conditions' => 'nullable|array', // Validate as an array
            'storage_location.storage_conditions.*' => 'string', // Each item in the array should be a string
            'storage_location.is_active' => 'boolean',

            // Main yield record fields remain the same
            'quantity' => 'required|numeric|min:0.01',
            'price_per_unit' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'production_date' => 'required|date|before_or_equal:today',
            'production_time' => 'required|date_format:H:i',
            'quality_status' => [
                'required',
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
            'product_category.name.required' => 'Product category name is required.',
            'product_grade.required' => 'Product grade information is required.',
            'product_grade.name.required' => 'Product grade name is required.',
            'production_method.required' => 'Production method information is required.',
            'production_method.name.required' => 'Production method name is required.',
            'collector.name.required_with' => 'Collector name is required when collector information is provided.',
            'storage_location.name.required_with' => 'Storage location name is required when storage information is provided.',
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
