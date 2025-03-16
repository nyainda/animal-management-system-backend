<?php

namespace App\Models;

use App\Traits\GeneratesInternalId;
use App\Traits\AnimalBootTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Animal extends Model
{
    use HasUuids, GeneratesInternalId, AnimalBootTrait;
    use Concerns\HasAttributes;
    use Concerns\HasRelationships;
    use Concerns\HasScopes;
    use Concerns\HasStatus;
    use Concerns\ManagesFamilyTree;
    use Concerns\HandlesCaching;

    protected const CACHE_TTL_MINUTES = 60;
    protected const FAMILY_TREE_FIELDS = ['id', 'name', 'type', 'gender', 'birth_date'];

    protected $fillable = [
        'user_id', 'name', 'type', 'breed', 'status', 'tag_number',
        'gender', 'keywords', 'death_date', 'deceased_reason',
        'is_neutered', 'is_breeding_stock', 'coloring', 'retention_score',
        'weight', 'height', 'body_condition_score', 'horn_length',
        'physical_traits', 'identification_details',
        'birth_date', 'dam_id', 'sire_id', 'birth_weight', 'weight_unit',
        'birth_time', 'birth_status', 'colostrum_intake', 'health_at_birth',
        'milk_feeding', 'vaccinations', 'breeder_info', 'birth_photos',
        'raised_purchased', 'last_breeding_date','internal_id'
    ];

    protected $hidden = [
        'user_id', 'created_at', 'updated_at', 'identification_details'
    ];

    protected $with = ['birthDetail'];

    public function suppliers()
{
    return $this->belongsToMany(Supplier::class)
        ->withPivot(['relationship_type', 'start_date', 'end_date', 'notes'])
        ->withTimestamps();
}

    public static function createWithRelationships(array $data, string $userId): self
    {
        return self::create([...$data, 'user_id' => $userId]);
    }
}



