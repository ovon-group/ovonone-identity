<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\ApplicationEnvironment;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AppLauncher extends Widget
{
    protected string $view = 'filament.widgets.app-launcher';

    public function getViewData(): array
    {
        return [
            'applications' => Auth::user()->getApplications(),
        ];
    }

    public function getUrl(ApplicationEnvironment $applicationEnvironment): string
    {
        return $applicationEnvironment->url . '?user=' . Auth::user()->uuid;
    }
}
