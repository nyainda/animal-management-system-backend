<?php


namespace App\Http\Controllers\API;

use App\Models\Animal;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDamRequest;
use App\Http\Requests\StoreSireRequest;
use App\Http\Requests\StoreOffspringRequest;
use App\Http\Controllers\Controller;

class AnimalRelationshipController extends Controller
{


    public function familyTree(Animal $animal, Request $request)
    {
        $generations = $request->validate(['generations' => 'nullable|integer|min:1|max:5'])['generations'] ?? 2;

        return response()->json([
            'data' => $animal->getFamilyTree($generations)
        ]);
    }


public function setDam(Animal $animal, StoreDamRequest $request)
{
    /** @var Animal $dam */
    $dam = Animal::findOrFail($request->related_animal_id);
    $relationship = $animal->setDam($dam, $request->validated());

    return response()->json([
        'message' => 'Dam relationship established successfully',
        'data' => $relationship
    ], 201);
}

public function setSire(Animal $animal, StoreSireRequest $request)
{
    /** @var Animal $sire */
    $sire = Animal::findOrFail($request->related_animal_id);
    $relationship = $animal->setSire($sire, $request->validated());

    return response()->json([
        'message' => 'Sire relationship established successfully',
        'data' => $relationship
    ], 201);
}

public function addOffspring(Animal $animal, StoreOffspringRequest $request)
{
    /** @var Animal $offspring */
    $offspring = Animal::findOrFail($request->offspring_id);
    $relationship = $animal->addOffspring($offspring, $request->parent_type, $request->validated());

    return response()->json([
        'message' => 'Offspring relationship added successfully',
        'data' => $relationship
    ], 201);
}
}
