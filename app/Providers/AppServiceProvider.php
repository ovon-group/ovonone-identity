<?php

namespace App\Providers;

use App\Enums\ApplicationEnum;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        Str::macro('shortName', function (string $string) {
            return Str::of($string)->headline()->title()->explode(' ')->first();
        });

        Passport::authorizationView('auth.denied');

        Passport::useClientModel(Client::class);

        Passport::tokensExpireIn(CarbonInterval::minutes(60));

        Passport::tokensCan(
            collect(ApplicationEnum::cases())
                ->mapWithKeys(fn (ApplicationEnum $application) => [
                    "application:{$application->value}" => "Access data for {$application->getLabel()}"]
                )
                ->toArray()
        );
    }
}
