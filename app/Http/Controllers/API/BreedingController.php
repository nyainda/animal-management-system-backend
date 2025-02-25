<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Breeding;
use App\Models\Animal;
use App\Http\Requests\Breeding\StoreBreedingRequest;
use App\Http\Requests\Breeding\UpdateBreedingRequest;
use App\Http\Resources\BreedingResource;

class BreedingController extends Controller
{
    public function index(Animal $animal)
    {
        return BreedingResource::collection(
            $animal->breedings()->latest()->paginate()
        );
    }

    public function store(Animal $animal, StoreBreedingRequest $request)
    {
        $breeding = $animal->breedings()->create([
            'user_id' => auth()->id(),
            ...$request->validated()
        ]);

        return new BreedingResource($breeding);
    }

    public function show(Animal $animal, Breeding $breeding)
    {
        return new BreedingResource($breeding);
    }

    public function update(Animal $animal, Breeding $breeding, UpdateBreedingRequest $request)
    {
        $breeding->update($request->validated());
        return new BreedingResource($breeding);
    }

    public function destroy(Animal $animal, Breeding $breeding)
    {
        $breeding->delete();
        return response()->noContent();
    }
}
