<?php

namespace App\Providers;

use App\Models\Client;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Responses\SimpleViewResponse;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
//        $this->app->bind(AuthorizationViewResponse::class, SimpleViewResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Str::macro('shortName', function (string $string) {
            return Str::of($string)->headline()->title()->explode(' ')->first();
        });

        Passport::authorizationView(fn() => null);

        Passport::useClientModel(Client::class);

        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::minutes(120));
    }
}
