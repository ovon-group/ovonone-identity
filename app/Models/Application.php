<?php

namespace App\Models;

use App\Models\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory, HasUuids;


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function environments(): HasMany
    {
        return $this->hasMany(ApplicationEnvironment::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withTimestamps();
    }

    public function getProductionUrl()
    {
    }
}
