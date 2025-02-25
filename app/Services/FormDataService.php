<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductCategory;
use App\Models\ProductGrade;
use App\Models\ProductionMethod;
use App\Models\Collector;
use App\Models\StorageLocation;

class FormDataService
{
    public function getFormData(Animal $animal)
    {
        if ($animal->user_id !== Auth::id()) {
            return response()->json(['error' => 'You do not have access to this animal'], 403);
        }

        if (!$this->canRecordProduction($animal)) {
            return response()->json(['error' => 'Cannot record production for ' . strtolower($animal->status) . ' animal'], 403);
        }

        return [
            'animal' => $animal,
            'productCategories' => $this->getAvailableProductCategories($animal),
            'productGrades' => $this->getAvailableGrades($animal),
            'productionMethods' => $this->getAvailableMethods($animal),
            'collectors' => $this->getAvailableCollectors(),
            'storageLocations' => $this->getAvailableStorageLocations(),
        ];
    }

    private function canRecordProduction(Animal $animal): bool
    {
        // Logic to determine if production can be recorded for the animal
        return true; // Placeholder
    }

    private function getAvailableProductCategories(Animal $animal)
    {
        // Fetch product categories based on animal type/status
        return ProductCategory::where('is_active', true)->get();
    }

    private function getAvailableGrades(Animal $animal)
    {
        // Fetch grades based on animal type/status
        return ProductGrade::where('is_active', true)->get();
    }

    private function getAvailableMethods(Animal $animal)
    {
        // Fetch production methods based on animal type/status
        return ProductionMethod::where('is_active', true)->get();
    }

    private function getAvailableCollectors()
    {
        return Collector::where('is_active', true)->get();
    }

    private function getAvailableStorageLocations()
    {
        return StorageLocation::where('is_active', true)->get();
    }
}
