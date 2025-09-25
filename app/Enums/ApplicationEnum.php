<?php

namespace App\Enums;

use App\Models\Client;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Illuminate\Contracts\Support\Htmlable;

enum ApplicationEnum: string implements HasLabel, HasIcon, HasColor
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
                self::Wheel2Web => 'https://app.wheel2web.com',
            },
            'local' => match ($this) {
                self::Protego => 'https://dealer.protegoautocare.test',
                self::Wheel2Web => 'https://wheel2web.test',
            },
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Protego => __('Warranty, national service plans and breakdown recovery solutions'),
            self::Wheel2Web => __('Dealership prep tool to shorten time to market for your stock'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Protego => 'heroicon-o-shield-check',
            self::Wheel2Web => 'solar-wheel-linear',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Protego => 'primary',
            self::Wheel2Web => 'success',
        };
    }

    public function getClient(): ?Client
    {
        return Client::where('name', $this->value)->first();
    }
}
