<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Models\AnimalBirthDetail;
use App\Models\AnimalActivity;
use App\Models\Health;
use App\Models\Task;
use App\Models\Treat;
use App\Models\Breeding;
use App\Models\Note;
use App\Models\FeedType;
use App\Models\YieldRecord;
use App\Models\FeedAnalytic;
use App\Models\FeedInventory;
use App\Models\FeedingSchedule;
use App\Models\FeedingRecord;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasRelationships
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function birthDetail(): HasOne
    {
        return $this->hasOne(AnimalBirthDetail::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AnimalActivity::class)->latest('activity_date');
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class);
    }

    public function taskRecords(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function treats(): HasMany
    {
        return $this->hasMany(Treat::class);
    }

    public function breedings(): HasMany
    {
        return $this->hasMany(Breeding::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function feedTypes(): HasMany
    {
        return $this->hasMany(FeedType::class);
    }

    public function feedAnalytics(): HasMany
    {
        return $this->hasMany(FeedAnalytic::class);
    }

    public function feedInventories(): HasMany
    {
        return $this->hasMany(FeedInventory::class);
    }

    public function feedingSchedules(): HasMany
    {
        return $this->hasMany(FeedingSchedule::class);
    }

    public function feedingRecords(): HasMany
    {
        return $this->hasMany(FeedingRecord::class);
    }
    public function yieldRecords(): HasMany
    {
        return $this->hasMany(YieldRecord::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }



}
