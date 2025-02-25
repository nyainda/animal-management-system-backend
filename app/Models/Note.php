<?php

namespace App\Models;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Note\Status;
use App\Enums\Note\Priority;
class Note extends Model
{

    use HasFactory, HasUuids, SoftDeletes;
    // Define the table associated with the model
    protected $table = 'notes';

    // Define the primary key
    protected $primaryKey = 'id';

    // Specify if the primary key is a UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'content',
        'category',
        'keywords',
        'file_path',
        'add_to_calendar',
        'priority',
        'status',
        'due_date',
        'animal_id',
        'user_id',
    ];

    protected $casts = [
        'keywords' => 'array',
        'add_to_calendar' => 'boolean',
        'due_date' => 'datetime',
        'status' => Status::class,
        'priority' => Priority::class,
    ];

    /**
     * Get the animal that owns the note.
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
