<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\YieldRecord;
use Illuminate\Support\Facades\{DB,Log,Cache, Auth};
use App\Models\ProductCategory;
use App\Models\ProductGrade;
use App\Models\ProductionMethod;
use App\Models\Collector;
use App\Models\StorageLocation;
class ProductionRecordService
{
    public function store(array $validated, Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have access to this animal'], 403);
        }

        DB::beginTransaction();
        try {
            // Create or find related entities
            $category = $this->createOrFindCategory($validated['product_category']);
            $grade = $this->createOrFindGrade($validated['product_grade'], $category->id);
            $method = $this->createOrFindMethod($validated['production_method'], $category->id);
            $collector = $this->createOrFindCollector($validated['collector'] ?? null);
            $storage = $this->createOrFindStorage($validated['storage_location'] ?? null);

            // Prepare yield record data
            $yieldData = [
                'animal_id' => $animal->id,
                'user_id' => Auth::id(),
                'product_category_id' => $category->id,
                'product_grade_id' => $grade->id,
                'production_method_id' => $method->id,
                'collector_id' => $collector?->id,
                'storage_location_id' => $storage?->id,
                'quantity' => $validated['quantity'],
                'total_price' => $validated['total_price'],
                'price_per_unit' => $validated['price_per_unit'],
                'production_date' => $validated['production_date'],
                'production_time' => $validated['production_time'],
                'quality_status' => $validated['quality_status'],
                'quality_notes' => $validated['quality_notes'] ?? null,
                'trace_number' => $validated['trace_number'] ?? null,
                'weather_conditions' => $validated['weather_conditions'] ?? null,
                'storage_conditions' => $validated['storage_conditions'] ?? null,
                'is_organic' => $validated['is_organic'] ?? false,
                'certification_number' => $validated['certification_number'] ?? null,
                'additional_attributes' => $validated['additional_attributes'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ];

            // Additional validation for lactating animals
            if ($animal->status === 'LACTATING') {
                $yieldData = $this->validateLactationRecord($yieldData);
            }

            // Create the yield record
            $record = YieldRecord::create($yieldData);

            // Update animal production stats
            $this->updateAnimalProductionStats($animal, $record);

            // Commit the transaction
            DB::commit();

            // Load relationships and return the fully populated record
            return YieldRecord::with([
                'storageLocation',
                'productionMethod',
                'productGrade',
                'productCategory',
                'collector'
            ])->find($record->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording production: ' . $e->getMessage());
            return response()->json(['error' => 'Error recording production: ' . $e->getMessage()], 500);
        }
    }

    private function createOrFindCategory(array $data)
    {
        return ProductCategory::firstOrCreate(
            ['name' => $data['name']],
            [
                'measurement_unit' => $data['measurement_unit'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]
        );
    }



private function createOrFindMethod(array $data,  $categoryId)
{
    return ProductionMethod::firstOrCreate(
        ['method_name' => $data['method_name'], 'product_category_id' => $categoryId],
        [
            'description' => $data['description'] ?? null,
            'requires_certification' => $data['requires_certification'],
            'is_active' => $data['is_active'] ?? true,
        ]
    );
}

private function createOrFindCollector(?array $data)
{
    if (!$data) return null;
    return Collector::firstOrCreate(
        ['name' => $data['name']],
        [
            'contact_info' => $data['contact_info'] ?? null,
            'is_active' => true,
        ]
    );
}

private function createOrFindGrade(array $data, $categoryId)
{
    return ProductGrade::firstOrCreate(
        ['grade_name' => $data['name'], 'product_category_id' => $categoryId],
        [
            'description' => $data['description'] ?? null,
            'price_modifier' => $data['price_modifier'] ?? 1.00,
            'is_active' => true,
        ]
    );
}

    private function createOrFindStorage(?array $data)
    {
        if (!$data) return null;
        return StorageLocation::firstOrCreate(
            ['name' => $data['name']],
            [
                'is_active' => true,
                'location_code' => $data['location_code'] ?? $this->generateLocationCode(),
                'description' => $data['description'] ?? null,
                'storage_conditions' => $data['storage_conditions'] ?? null,
            ]
        );
    }

/**
     * Show a specific production record with relations
     */
    public function show(Animal $animal, YieldRecord $production)
    {
        if ($animal->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have access to this animal'], 403);
        }

        if ($production->animal_id !== $animal->id) {
            return response()->json(['error' => 'Production record does not belong to this animal'], 404);
        }

        return YieldRecord::with([
            'storageLocation',
            'productionMethod',
            'productGrade',
            'productCategory',
            'collector'
        ])->find($production->id);
    }

    /**
     * Update a production record and its relations
     */
    public function update(array $validated, Animal $animal, YieldRecord $production)
    {
        if ($animal->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have access to this animal'], 403);
        }

        if ($production->animal_id !== $animal->id) {
            return response()->json(['error' => 'Production record does not belong to this animal'], 404);
        }

        DB::beginTransaction();
        try {
            // Update related entities if provided
            if (isset($validated['product_category'])) {
                $category = $this->createOrFindCategory($validated['product_category']);
                $production->product_category_id = $category->id;
            }

            if (isset($validated['product_grade'])) {
                $grade = $this->createOrFindGrade(
                    $validated['product_grade'],
                    $production->product_category_id
                );
                $production->product_grade_id = $grade->id;
            }

            if (isset($validated['production_method'])) {
                $method = $this->createOrFindMethod(
                    $validated['production_method'],
                    $production->product_category_id
                );
                $production->production_method_id = $method->id;
            }

            if (isset($validated['collector'])) {
                $collector = $this->createOrFindCollector($validated['collector']);
                $production->collector_id = $collector?->id;
            }

            if (isset($validated['storage_location'])) {
                $storage = $this->createOrFindStorage($validated['storage_location']);
                $production->storage_location_id = $storage?->id;
            }

            // Update main fields
            $production->fill([
                'quantity' => $validated['quantity'] ?? $production->quantity,
                'total_price' => $validated['total_price'] ?? $production->total_price,
                'price_per_unit' => $validated['price_per_unit'] ?? $production->price_per_unit,
                'production_date' => $validated['production_date'] ?? $production->production_date,
                'production_time' => $validated['production_time'] ?? $production->production_time,
                'quality_status' => $validated['quality_status'] ?? $production->quality_status,
                'quality_notes' => $validated['quality_notes'] ?? $production->quality_notes,
                'trace_number' => $validated['trace_number'] ?? $production->trace_number,
                'weather_conditions' => $validated['weather_conditions'] ?? $production->weather_conditions,
                'storage_conditions' => $validated['storage_conditions'] ?? $production->storage_conditions,
                'is_organic' => $validated['is_organic'] ?? $production->is_organic,
                'certification_number' => $validated['certification_number'] ?? $production->certification_number,
                'additional_attributes' => $validated['additional_attributes'] ?? $production->additional_attributes,
                'notes' => $validated['notes'] ?? $production->notes,
            ]);

            $production->save();

            // Update animal production stats
            $this->updateAnimalProductionStats($animal, $production);

            DB::commit();

            return YieldRecord::with([
                'storageLocation',
                'productionMethod',
                'productGrade',
                'productCategory',
                'collector'
            ])->find($production->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating production record: ' . $e->getMessage());
            return response()->json(['error' => 'Error updating production record: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a production record
     */
    public function destroy(Animal $animal, YieldRecord $production)
    {
        if ($animal->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have access to this animal'], 403);
        }

        if ($production->animal_id !== $animal->id) {
            return response()->json(['error' => 'Production record does not belong to this animal'], 404);
        }

        DB::beginTransaction();
        try {
            $production->delete();

            // Update animal production stats after deletion
            //$this->updateAnimalProductionStats($animal, null);

            DB::commit();
            return response()->json(['message' => 'Production record deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting production record: ' . $e->getMessage());
            return response()->json(['error' => 'Error deleting production record: ' . $e->getMessage()], 500);
        }
    }

    private function generateLocationCode(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($characters), 0, 8);
    }

    private function validateLactationRecord(array $data): array
    {
        // Additional validation for lactation records
        return $data;
    }



    private function updateAnimalProductionStats(Animal $animal, YieldRecord $record)
    {
        // Update animal's production-related statistics
    }

    public function getProductionStatistics(Animal $animal, $period = 'all')
{
    if ($animal->user_id !== Auth::id()) {
        return response()->json(['error' => 'You do not have access to this animal'], 403);
    }

    $query = YieldRecord::where('animal_id', $animal->id);

    // Filter by period if specified
    if ($period !== 'all') {
        $startDate = now()->sub($period);
        $query->where('production_date', '>=', $startDate);
    }

    $records = $query->get();

    if ($records->isEmpty()) {
        return response()->json(['error' => 'No production records found'], 404);
    }

    // Calculate statistics
    $totalProduction = $records->sum('quantity');
    $averageProduction = $records->avg('quantity');
    $qualityDistribution = $records->groupBy('quality_status')->map->count();
    $productionTrends = $records->groupBy(function ($record) {
        return $record->production_date->format('Y-m');
    })->map->sum('quantity');
    $topProductionMethods = $records->groupBy('productionMethod.method_name')->map->count();
    $topProductGrades = $records->groupBy('productGrade.grade_name')->map->count();
    $organicVsNonOrganic = $records->groupBy('is_organic')->map->count();

    return [
        'total_production' => $totalProduction,
        'average_production' => $averageProduction,
        'quality_distribution' => $qualityDistribution,
        'production_trends' => $productionTrends,
        'top_production_methods' => $topProductionMethods,
        'top_product_grades' => $topProductGrades,
        'organic_vs_non_organic' => $organicVsNonOrganic,
    ];
}
}
