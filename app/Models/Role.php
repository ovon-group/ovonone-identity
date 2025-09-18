<?php

namespace App\Models;

use App\Enums\ApplicationEnum;
use Filament\Models\Contracts\HasName;

class Role extends \Spatie\Permission\Models\Role implements HasName
{
    protected function casts(): array
    {
        return [
            'app' => ApplicationEnum::class,
        ];
    }

    public function getFilamentName(): string
    {
        return $this->app->getLabel().': '.$this->name;
    }
}
