<?php

use App\Actions\CreateOrder;
use App\Enums\OrderStatus;
use App\Exceptions\CannotPurchaseOwnProduct;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests cannot purchase an app', function () {
    $product = Product::factory()->create();

    $this->post(route('apps.purchase', $product))->assertRedirect(route('login'));
});

test('buying an app creates a pending order', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    $response = $this->actingAs($buyer)->post(route('apps.purchase', $product));

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('purchases.show', $order));
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->amount)->toBe(1900);
    expect($order->user_id)->toBe($buyer->id);
});

test('buying a free app settles the order straight away', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->free()->create();

    $this->actingAs($buyer)->post(route('apps.purchase', $product));

    $order = Order::query()->firstOrFail();

    expect($order->status)->toBe(OrderStatus::Paid);
    expect(License::query()->count())->toBe(1);
});

test('a second purchase reuses the existing pending order', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($buyer)->post(route('apps.purchase', $product));
    $this->actingAs($buyer)->post(route('apps.purchase', $product));

    expect(Order::query()->count())->toBe(1);
});

test('a draft app cannot be purchased', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->draft()->create();

    $this->actingAs($buyer)->post(route('apps.purchase', $product))->assertNotFound();
});

test('purchasing is rate limited', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    foreach (range(1, 10) as $attempt) {
        $this->actingAs($buyer)->post(route('apps.purchase', $product))->assertRedirect();
    }

    $this->actingAs($buyer)->post(route('apps.purchase', $product))->assertStatus(429);
});

test('a developer cannot purchase their own app', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create(['price' => 1900]);

    $response = $this->actingAs($developer)->post(route('apps.purchase', $product));

    $response->assertRedirect(route('apps.show', $product));
    expect(Order::query()->count())->toBe(0);
    expect(License::query()->count())->toBe(0);
});

test('placing an order for your own app is rejected', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create();

    expect(fn () => app(CreateOrder::class)->handle($developer, $product))
        ->toThrow(CannotPurchaseOwnProduct::class);
});

test('a buyer can review their own purchase', function () {
    $buyer = User::factory()->create();
    $order = Order::factory()->for($buyer)->create();

    $response = $this->actingAs($buyer)->get(route('purchases.show', $order));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Purchases/Show')
        ->where('order.reference', $order->reference)
    );
});

test('a buyer cannot review somebody else purchase', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('purchases.show', $order))->assertForbidden();
});

test('the purchase list only contains the orders of the current user', function () {
    $buyer = User::factory()->create();
    Order::factory()->for($buyer)->create();
    Order::factory()->create();

    $response = $this->actingAs($buyer)->get(route('purchases.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Purchases/Index')
        ->has('orders.data', 1)
    );
});

test('a paid order redirects the buyer to the download link', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['download_url' => 'https://example.com/app.zip']);
    $order = Order::factory()->for($buyer)->for($product)->paid()->create();
    License::factory()->for($order)->for($product)->for($buyer)->create();

    $this->actingAs($buyer)->get(route('purchases.download', $order))
        ->assertRedirect('https://example.com/app.zip');
});

test('an unpaid order cannot be downloaded', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['download_url' => 'https://example.com/app.zip']);
    $order = Order::factory()->for($buyer)->for($product)->create();

    $this->actingAs($buyer)->get(route('purchases.download', $order))
        ->assertRedirect();
});
