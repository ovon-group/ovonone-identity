<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends \Spatie\Permission\Models\Role implements HasName
{
    public function applicationEnvironment(): BelongsTo
    {
        return $this->belongsTo(ApplicationEnvironment::class);
    }

    public function getFilamentName(): string
    {
        return sprintf(
            '%s %s: %s',
            $this->applicationEnvironment->application->name,
            $this->applicationEnvironment->name,
            $this->name
        );
    }
}
