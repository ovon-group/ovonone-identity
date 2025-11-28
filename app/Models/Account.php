<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use App\Models\Traits\HasUuids;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applications' => AsEnumCollection::of(ApplicationEnum::class),
        ];
    }

    public function applicationPayload(): array
    {
        return $this->only([
            'uuid',
            'name',
            'short_name',
            'deleted_at',
        ]);
    }

    public function getApplications(): array
    {
        return $this->applications->toArray();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
