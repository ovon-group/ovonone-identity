<?php

namespace App\Models;

use App\Models\Traits\HasUuids;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([AccountObserver::class])]
class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public function applicationPayload(): array
    {
        return $this->only([
            'uuid',
            'name',
            'short_name',
            'deleted_at',
        ]);
    }

//    public function applicationEnvironments(): BelongsToMany
//    {
//        return $this->belongsToMany(ApplicationEnvironment::class)->withTimestamps();
//    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
