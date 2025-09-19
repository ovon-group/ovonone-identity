<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use App\Models\Traits\HasUuids;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([AccountObserver::class])]
class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasFactory;
    use HasUuids;

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

    public function applicationPayload()
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'short_name' => $this->short_name,

        ];
    }

    public function getApplications()
    {
        return $this->applications;
    }
}
