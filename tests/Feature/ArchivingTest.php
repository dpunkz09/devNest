<?php

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('an archived app disappears from the catalogue', function () {
    $product = Product::factory()->create(['name' => 'Pixel Tracker']);

    $product->delete();

    $this->get(route('apps.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Index')
            ->has('products.data', 0)
        );

    $this->get(route('apps.show', $product))->assertNotFound();
});

test('the purchase of a buyer survives the archiving of the app', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Pixel Tracker']);
    $order = Order::factory()->for($buyer)->for($product)->paid()->create();
    License::factory()->for($order)->for($product)->for($buyer)->create();

    $product->delete();

    $this->actingAs($buyer)->get(route('purchases.show', $order))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Purchases/Show')
            ->where('order.product.name', 'Pixel Tracker')
        );

    expect(License::query()->count())->toBe(1);
});

test('the download of a bought app keeps working after the archiving', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['download_url' => 'https://example.com/app.zip']);
    $order = Order::factory()->for($buyer)->for($product)->paid()->create();
    License::factory()->for($order)->for($product)->for($buyer)->create();

    $product->delete();

    $this->actingAs($buyer)->get(route('purchases.download', $order))
        ->assertRedirect('https://example.com/app.zip');
});

test('an app with sales cannot be deleted for good', function () {
    $product = Product::factory()->create();
    Order::factory()->for($product)->create();

    expect(fn () => $product->forceDelete())->toThrow(RuntimeException::class);

    expect(Product::withTrashed()->count())->toBe(1);
});

test('an archived app can be restored by its developer', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create();
    $product->delete();

    $this->actingAs($developer)
        ->post(route('dashboard.products.restore', $product))
        ->assertRedirect(route('dashboard.products.index'));

    expect($product->fresh()->trashed())->toBeFalse();
});

test('the dashboard lists the archived apps of the developer', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create(['name' => 'Old App']);
    $product->delete();

    $this->actingAs($developer)->get(route('dashboard.products.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard/Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Old App')
            ->where('products.data.0.archived', true)
        );
});

test('the sales of an archived app stay in the dashboard', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create();
    Order::factory()->for($product)->create(['status' => 'paid']);

    $product->delete();

    $this->actingAs($developer)->get(route('dashboard.orders.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard/Orders/Index')
            ->has('orders.data', 1)
        );
});

test('the overview keeps counting the sales of an archived app', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create();
    Order::factory()->for($product)->create(['status' => 'paid', 'amount' => 1900]);

    $product->delete();

    $this->actingAs($developer)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('stats.products', 1)
            ->where('stats.published', 0)
            ->where('stats.sales', 1)
            ->where('stats.revenue', 1900)
        );
});

test('an account with purchases is closed instead of removed', function () {
    $buyer = User::factory()->create(['email' => 'buyer@example.com']);
    $product = Product::factory()->create();
    Order::factory()->for($buyer)->for($product)->paid()->create();

    $this->actingAs($buyer)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    $user = $buyer->fresh();

    expect($user)->not->toBeNull();
    expect($user->email)->not->toBe('buyer@example.com');
    expect($user->name)->toBe('Deleted user');
    expect($user->orders()->count())->toBe(1);
});

test('closing an account archives the apps of the seller', function () {
    $seller = User::factory()->create();
    $product = Product::factory()->for($seller)->create();
    Order::factory()->for($product)->create(['status' => 'paid']);

    $this->actingAs($seller)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    expect(Product::query()->count())->toBe(0);
    expect(Product::withTrashed()->count())->toBe(1);
    expect($seller->fresh())->not->toBeNull();
});

test('an account without purchases is removed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    expect($user->fresh())->toBeNull();
});
