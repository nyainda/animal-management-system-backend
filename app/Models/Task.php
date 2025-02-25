<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Task extends Model
{
    use HasFactory;
    use HasUuids;
    // Define the table associated with the model
    protected $table = 'tasks';

    // Define the primary key
    protected $primaryKey = 'id';

    // Specify if the primary key is a UUID
    public $incrementing = false;
    protected $keyType = 'string';

    // Fields that can be mass-assigned
    protected $fillable = [
        'animal_id',
        'user_id',
        'title',
        'task_type',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'duration',
        'description',
        'health_notes',
        'location',
        'priority',
        'status',
        'repeats',
        'repeat_frequency',
        'end_repeat_date',
    ];

    // Define relationships
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'animal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
