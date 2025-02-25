<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Http\Resources\Suppliers\SupplierResource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Exception;

class SupplierController extends Controller
{
    use ApiResponse;

    public function index(Animal $animal)
    {
        if (!Str::isUuid($animal->id)) {
            return $this->errorResponse(
                'Invalid animal ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        $suppliers = $animal->suppliers()
            ->with(['contacts', 'category'])
            ->paginate(10);

        return $this->successResponse(
            SupplierResource::collection($suppliers),
            'Suppliers retrieved successfully'
        );
    }

    public function store(StoreSupplierRequest $request, Animal $animal)
    {
        if (!Str::isUuid($animal->id)) {
            return $this->errorResponse(
                'Invalid animal ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $supplier = DB::transaction(function () use ($request, $animal) {
                $validated = $request->validated();
                $supplierId = Str::uuid();

                $supplier = Supplier::create([
                    'id' => $supplierId,
                    ...$validated,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                if (!empty($validated['contact_name'])) {
                    $supplier->contacts()->create([
                        'id' => Str::uuid(),
                        'name' => $validated['contact_name'],
                        'position' => $validated['contact_position'] ?? null,
                        'email' => $validated['contact_email'] ?? null,
                        'phone' => $validated['contact_phone'] ?? null,
                        'is_primary' => true,
                    ]);
                }

                if (!empty($validated['additional_contacts'])) {
                    foreach ($validated['additional_contacts'] as $contact) {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            ...$contact,
                            'is_primary' => false,
                        ]);
                    }
                }

                $animal->suppliers()->attach($supplier->id, [
                    'id' => Str::uuid(),
                    'relationship_type' => $validated['relationship_type'] ?? 'primary',
                    'start_date' => $validated['start_date'] ?? now(),
                    'end_date' => $validated['end_date'] ?? null,
                    'notes' => $validated['relationship_notes'] ?? null
                ]);

                $supplier->load(['contacts', 'category', 'animals']);
                return $supplier;
            });

            return $this->successResponse(
                new SupplierResource($supplier),
                'Supplier created successfully',
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to create supplier',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    public function show(Animal $animal, Supplier $supplier)
    {
        if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
            return $this->errorResponse(
                'Invalid ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
            return $this->errorResponse(
                'This supplier is not associated with the specified animal',
                Response::HTTP_NOT_FOUND
            );
        }

        $supplier->load(['contacts', 'category', 'animals']);
        return $this->successResponse(
            new SupplierResource($supplier),
            'Supplier retrieved successfully'
        );
    }

    public function update(StoreSupplierRequest $request, Animal $animal, Supplier $supplier)
    {
        if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
            return $this->errorResponse(
                'Invalid ID format',
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($animal->user_id !== Auth::id()) {
            return $this->errorResponse(
                'You do not have access to this animal',
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
            return $this->errorResponse(
                'This supplier is not associated with the specified animal',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $updatedSupplier = DB::transaction(function () use ($request, $animal, $supplier) {
                $validated = $request->validated();

                $supplier->update([
                    ...$validated,
                    'updated_by' => Auth::id(),
                ]);

                if (!empty($validated['contact_name'])) {
                    $primaryContact = $supplier->contacts()
                        ->where('is_primary', true)
                        ->first();

                    if ($primaryContact) {
                        $primaryContact->update([
                            'name' => $validated['contact_name'],
                            'position' => $validated['contact_position'] ?? $primaryContact->position,
                            'email' => $validated['contact_email'] ?? $primaryContact->email,
                            'phone' => $validated['contact_phone'] ?? $primaryContact->phone,
                        ]);
                    } else {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            'name' => $validated['contact_name'],
                            'position' => $validated['contact_position'] ?? null,
                            'email' => $validated['contact_email'] ?? null,
                            'phone' => $validated['contact_phone'] ?? null,
                            'is_primary' => true,
                        ]);
                    }
                }

                if (!empty($validated['additional_contacts'])) {
                    $supplier->contacts()
                        ->where('is_primary', false)
                        ->delete();

                    foreach ($validated['additional_contacts'] as $contact) {
                        $supplier->contacts()->create([
                            'id' => Str::uuid(),
                            ...$contact,
                            'is_primary' => false,
                        ]);
                    }
                }

                if (isset($validated['relationship_type']) ||
                    isset($validated['start_date']) ||
                    isset($validated['end_date']) ||
                    isset($validated['relationship_notes'])) {
                    $animal->suppliers()->updateExistingPivot($supplier->id, [
                        'relationship_type' => $validated['relationship_type'] ?? null,
                        'start_date' => $validated['start_date'] ?? null,
                        'end_date' => $validated['end_date'] ?? null,
                        'notes' => $validated['relationship_notes'] ?? null
                    ]);
                }

                $supplier->load(['contacts', 'category', 'animals']);
                return $supplier;
            });

            return $this->successResponse(
                new SupplierResource($updatedSupplier),
                'Supplier updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Failed to update supplier',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    // In your SupplierController
public function destroy(Animal $animal, Supplier $supplier, Request $request)
{
    if (!Str::isUuid($animal->id) || !Str::isUuid($supplier->id)) {
        return $this->errorResponse(
            'Invalid ID format',
            Response::HTTP_BAD_REQUEST
        );
    }

    if ($animal->user_id !== Auth::id()) {
        return $this->errorResponse(
            'You do not have access to this animal',
            Response::HTTP_FORBIDDEN
        );
    }

    if (!$animal->suppliers()->where('suppliers.id', $supplier->id)->exists()) {
        return $this->errorResponse(
            'This supplier is not associated with the specified animal',
            Response::HTTP_NOT_FOUND
        );
    }

    try {
        $animalCount = $supplier->animals()->count();

        DB::transaction(function () use ($animal, $supplier, $request, $animalCount) {
            // Always remove the relationship
            $animal->suppliers()->detach($supplier->id);

            // If force delete is requested or this is the only animal, delete everything
            if ($request->query('force_delete', false) || $animalCount <= 1) {
                $supplier->contacts()->delete();
                $supplier->delete();
            }
        });

        return $this->successResponse(
            null,
            'Supplier successfully removed'
        );
    } catch (Exception $e) {
        return $this->errorResponse(
            'Failed to delete supplier',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [$e->getMessage()]
        );
    }
}
}
