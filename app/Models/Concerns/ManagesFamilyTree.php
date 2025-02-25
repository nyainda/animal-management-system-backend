<?php

namespace App\Models\Concerns;

use App\Models\Animal;
use App\Models\AnimalRelationship;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

trait ManagesFamilyTree
{
    protected static array $familyTreeFields = ['id', 'name', 'type', 'gender', 'birth_date'];

    public function relationships(): HasMany
    {
        return $this->hasMany(AnimalRelationship::class, 'animal_id');
    }

    public function damRelationship(): HasOne
    {
        return $this->hasOne(AnimalRelationship::class)
            ->where('relationship_type', 'dam')
            ->select(['id', 'animal_id', 'related_animal_id']);
    }

    public function dam(): ?Animal
    {
        return $this->damRelationship->relatedAnimal ?? null;
    }

    public function sireRelationship(): HasOne
    {
        return $this->hasOne(AnimalRelationship::class)
            ->where('relationship_type', 'sire')
            ->select(['id', 'animal_id', 'related_animal_id']);
    }

    public function sire(): ?Animal
    {
        return $this->sireRelationship->relatedAnimal ?? null;
    }

    public function offspringRelationships(): HasMany
    {
        return $this->hasMany(AnimalRelationship::class, 'related_animal_id')
            ->whereIn('relationship_type', ['dam', 'sire'])
            ->select(['id', 'animal_id', 'related_animal_id', 'relationship_type']);
    }

    public function offspring(): Collection
    {
        return $this->offspringRelationships
            ->map(fn($rel) => $rel->animal);
    }

    public function setDam(Animal $dam, array $details = []): AnimalRelationship
    {
        return $this->createRelationship($dam, 'dam', $details);
    }

    public function setSire(Animal $sire, array $details = []): AnimalRelationship
    {
        return $this->createRelationship($sire, 'sire', $details);
    }

    public function addOffspring(Animal $offspring, string $parentType = 'dam', array $details = []): AnimalRelationship
    {
        return $offspring->{"set{$parentType}"}($this, $details);
    }

    public function getFamilyTree(int $generations = 2, int $currentGen = 0): ?array
    {
        if ($currentGen >= $generations) {
            return null;
        }

        return [
            'animal' => $this->only(static::$familyTreeFields),
            'dam' => $this->dam?->getFamilyTree($generations, $currentGen + 1),
            'sire' => $this->sire?->getFamilyTree($generations, $currentGen + 1),
            'offspring' => $currentGen === 0
                ? $this->offspring->map->only(static::$familyTreeFields)
                : []
        ];
    }

    protected function createRelationship(Animal $related, string $type, array $details): AnimalRelationship
    {
        return AnimalRelationship::create([
            'animal_id' => $this->id,
            'related_animal_id' => $related->id,
            'relationship_type' => $type,
            'breeding_date' => $details['breeding_date'] ?? null,
            'breeding_notes' => $details['breeding_notes'] ?? null
        ]);
    }

    public static function getFamilyTreeFields(): array
    {
        return static::$familyTreeFields;
    }
}
