<?php

namespace App\Repositories;

use App\Models\Breeding;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BreedingRepository
{
    public function __construct(
        private readonly Breeding $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model
            ->with(['animal', 'mate', 'user'])
            ->latest()
            ->get();
    }

    public function find(string $id): ?Model
    {
        return $this->model
            ->with(['animal', 'mate', 'user'])
            ->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create(array_merge($data, [
            'user_id' => auth()->id()
        ]));
    }

    public function update(Model $breeding, array $data): Model
    {
        $breeding->update($data);
        return $breeding->fresh(['animal', 'mate', 'user']);
    }

    public function delete(Model $breeding): bool
    {
        return $breeding->delete();
    }

    public function getBreedingHistory(string $animalId): Collection
    {
        return $this->model
            ->where('animal_id', $animalId)
            ->orWhere('mate_id', $animalId)
            ->with(['animal', 'mate'])
            ->latest()
            ->get();
    }
}
