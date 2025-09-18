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

    public function getUrl(): string
    {
        return match (app()->environment()) {
            'production' => match ($this) {
                self::Protego => 'https://dealer.protegoautocare.com',
                self::Wheel2Web => 'https://wheel2web.com',
            },
            'local' => match ($this) {
                self::Protego => 'https://dealer.protegoautocare.test',
                self::Wheel2Web => 'https://wheel2web.test',
            },
        };
    }
}
