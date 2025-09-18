<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ApplicationEnum: string implements HasLabel
{
    case Protego = 'protego';
    case Wheel2Web = 'wheel2web';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Protego => __('Protego'),
            self::Wheel2Web => __('Wheel2Web'),
        };
    }
}
