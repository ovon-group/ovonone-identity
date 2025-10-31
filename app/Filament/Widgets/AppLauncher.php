<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationEnum;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AppLauncher extends Widget
{
    protected string $view = 'filament.widgets.app-launcher';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'applications' => $this->getApplications(),
        ];
    }

    public function getUrl(ApplicationEnum $application): string
    {
        return $application->getUrl().'?user='.Auth::user()->uuid;
    }

    protected function getApplications(): array
    {
        $user = Auth::user();

        return collect(ApplicationEnum::cases())
            ->map(function (ApplicationEnum $application) use ($user) {
                return [
                    'application' => $application,
                    'canAccess' => $user->canAccessApplication($application),
                ];
            })
            ->toArray();
    }
}
