<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        isolateTestEnvironment();
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Test Environment
|--------------------------------------------------------------------------
|
| The suite runs against the configuration of the local environment, which
| holds real credentials. Those are cleared before every test, so a test can
| never reach the live PayPal API by accident. Tests that exercise the
| checkout set the credentials they need and fake the API calls.
|
*/

function isolateTestEnvironment(): void
{
    config([
        'paypal.mode' => 'sandbox',
        'paypal.client_id' => null,
        'paypal.client_secret' => null,
        'paypal.webhook_id' => null,
        'paypal.currency' => 'USD',
        'paypal.base_url' => null,
        'inertia.ssr.enabled' => false,
    ]);

    Http::preventStrayRequests();
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
