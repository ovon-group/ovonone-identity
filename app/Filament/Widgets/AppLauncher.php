<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationEnum;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AppLauncher extends Widget
{
    protected string $view = 'filament.widgets.app-launcher';

    public function getViewData(): array
    {
        return [
            'applications' => $this->getApplications(),
        ];
    }

    public function getUrl(ApplicationEnum $application): string
    {
        return $application->getUrl() . '?user=' . Auth::user()->uuid;
    }

    protected function getApplications(): array
    {
        if (Auth::user()->is_internal) {
            return ApplicationEnum::cases();
        }

        return Auth::user()->getApplications();
    }
}
