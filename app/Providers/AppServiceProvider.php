<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        Str::macro('shortName', function (string $string) {
            return Str::of($string)->headline()->title()->explode(' ')->first();
        });

        Passport::authorizationView(fn() => null);

        Passport::useClientModel(Client::class);

        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::minutes(120));
    }
}
