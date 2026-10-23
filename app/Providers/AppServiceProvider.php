<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureResources();
        $this->configureRateLimiters();
        $this->configureTrustedProxies();
    }

    /**
     * Configure the proxies the application trusts.
     *
     * The addresses of the proxies are read by the middleware itself, the
     * headers that are read from them are set here.
     */
    protected function configureTrustedProxies(): void
    {
        TrustProxies::withHeaders((int) config('trustedproxy.headers'));
    }

    /**
     * Configure the rate limiters used across the storefront.
     *
     * Every limiter keeps its own bucket, so a burst of catalogue browsing
     * never eats into the budget of the checkout routes.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('catalogue', fn (Request $request) => Limit::perMinute(120)
            ->by($this->limiterKey('catalogue', $request)));

        RateLimiter::for('purchase', fn (Request $request) => Limit::perMinute(10)
            ->by($this->limiterKey('purchase', $request)));

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(20)
            ->by($this->limiterKey('checkout', $request)));

        RateLimiter::for('download', fn (Request $request) => Limit::perMinute(60)
            ->by($this->limiterKey('download', $request)));

        RateLimiter::for('listing', fn (Request $request) => Limit::perMinute(60)
            ->by($this->limiterKey('listing', $request)));

        RateLimiter::for('paypal-return', fn (Request $request) => Limit::perMinute(20)
            ->by($this->limiterKey('paypal-return', $request)));

        RateLimiter::for('paypal-webhook', fn (Request $request) => Limit::perMinute(120)
            ->by('paypal-webhook:'.$request->ip()));
    }

    /**
     * Build the key a rate limiter counts against.
     *
     * Signed in visitors are counted per account, guests per IP address.
     */
    protected function limiterKey(string $name, Request $request): string
    {
        $identifier = $request->user()?->getAuthIdentifier() ?? $request->ip();

        return $name.':'.$identifier;
    }

    /**
     * Configure the API resources used to build Inertia props.
     *
     * Inertia resolves resources through their response, which wraps every
     * payload in a "data" key unless wrapping is disabled.
     */
    protected function configureResources(): void
    {
        JsonResource::withoutWrapping();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
