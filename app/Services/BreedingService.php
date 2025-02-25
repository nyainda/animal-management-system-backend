<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Breeding;

class BreedingService
{
    public function getByAnimal(Animal $animal)
    {
        return Breeding::where('animal_id', $animal->id)
            ->with(['animal', 'mate', 'user'])
            ->get();
    }

    public function create(array $data)
    {
        return Breeding::create($data);
    }

    public function update(string $id, array $data)
    {
        $breeding = Breeding::findOrFail($id);
        $breeding->update($data);
        return $breeding;
    }

    public function delete(string $id)
    {
        $breeding = Breeding::findOrFail($id);
        $breeding->delete();
    }
}
