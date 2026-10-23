<?php

use App\Enums\OrderStatus;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'paypal.mode' => 'sandbox',
        'paypal.client_id' => 'client-id',
        'paypal.client_secret' => 'client-secret',
        'paypal.webhook_id' => 'webhook-id',
        'paypal.currency' => 'USD',
    ]);
});

/**
 * Fake the PayPal REST API.
 *
 * @param  array<string, mixed>  $overrides
 */
function fakePayPalApi(array $overrides = []): void
{
    Http::fake(array_merge([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'access-token',
            'expires_in' => 3600,
        ]),
        'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-ORDER-1',
            'status' => 'CREATED',
            'links' => [
                [
                    'rel' => 'payer-action',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
                ],
            ],
        ]),
        'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
            'id' => 'CAPTURE-1',
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            ['id' => 'CAPTURE-1', 'status' => 'COMPLETED'],
                        ],
                    ],
                ],
            ],
        ]),
        'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
            'verification_status' => 'SUCCESS',
        ]),
    ], $overrides));
}

/**
 * The headers PayPal sends along with a webhook call.
 *
 * @return array<string, string>
 */
function paypalWebhookHeaders(): array
{
    return [
        'PAYPAL-TRANSMISSION-ID' => 'transmission-id',
        'PAYPAL-TRANSMISSION-TIME' => '2026-01-01T00:00:00Z',
        'PAYPAL-TRANSMISSION-SIG' => 'transmission-signature',
        'PAYPAL-CERT-URL' => 'https://api-m.sandbox.paypal.com/v1/notifications/certs/CERT-123',
        'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
    ];
}

test('buying an app sends the buyer to paypal', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    $response = $this->actingAs($buyer)
        ->post(route('apps.purchase', $product), [], ['X-Inertia' => 'true']);

    $response->assertStatus(409);
    $response->assertHeader(
        'X-Inertia-Location',
        'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
    );

    $order = Order::query()->firstOrFail();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->amount)->toBe(1900);
    expect($order->user_id)->toBe($buyer->id);
    expect($order->paypal_order_id)->toBe('PAYPAL-ORDER-1');
});

test('the amount sent to paypal is the price of the app', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 4950]);

    $this->actingAs($buyer)->post(route('apps.purchase', $product));

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request['purchase_units'][0]['amount']['value'] === '49.50'
            && $request['purchase_units'][0]['amount']['currency_code'] === 'USD';
    });
});

test('the checkout stays closed while paypal is not configured', function () {
    config([
        'paypal.client_id' => null,
        'paypal.client_secret' => null,
    ]);

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    $response = $this->actingAs($buyer)->post(route('apps.purchase', $product));

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('purchases.show', $order));
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->paypal_order_id)->toBeNull();
});

test('a failed paypal call keeps the order pending', function () {
    fakePayPalApi([
        'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'name' => 'INVALID_REQUEST',
            'message' => 'Request is not well-formed.',
        ], 400),
    ]);

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    $response = $this->actingAs($buyer)->post(route('apps.purchase', $product));

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('purchases.show', $order));
    expect($order->status)->toBe(OrderStatus::Pending);
    expect(License::query()->count())->toBe(0);
});

test('the buyer returns from paypal and the order is settled', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']));

    $response->assertRedirect(route('purchases.show', $order));
    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($order->paypal_capture_id)->toBe('CAPTURE-1');
    expect($order->license)->not->toBeNull();
    expect($product->refresh()->sales)->toBe(1);
});

test('a declined capture leaves the order pending', function () {
    fakePayPalApi([
        'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
            'name' => 'UNPROCESSABLE_ENTITY',
            'message' => 'The requested action could not be performed.',
            'details' => [
                ['description' => 'The instrument presented was declined.'],
            ],
        ], 422),
    ]);

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']));

    $response->assertRedirect(route('purchases.show', $order));
    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
    expect(License::query()->count())->toBe(0);
});

test('a second return never issues a second license', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']));
    $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']));

    expect(License::query()->count())->toBe(1);
    expect($product->refresh()->sales)->toBe(1);
});

test('a buyer cannot capture the order of somebody else', function () {
    fakePayPalApi();

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $product = Product::factory()->for($owner)->create(['price' => 2900]);
    $order = Order::factory()->for($owner)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $this->actingAs($intruder)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']))
        ->assertForbidden();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
});

test('a pending order can be paid from the purchase page', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 1900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->actingAs($buyer)
        ->post(route('purchases.pay', $order), [], ['X-Inertia' => 'true']);

    $response->assertStatus(409);
    $response->assertHeader(
        'X-Inertia-Location',
        'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
    );
});

test('the webhook settles a paid order', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => [
            'id' => 'CAPTURE-1',
            'supplementary_data' => [
                'related_ids' => ['order_id' => 'PAYPAL-ORDER-1'],
            ],
            'purchase_units' => [
                ['custom_id' => (string) $order->id],
            ],
        ],
    ], paypalWebhookHeaders());

    $response->assertOk();
    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($order->license)->not->toBeNull();
});

test('the webhook ignores a call with an invalid signature', function () {
    fakePayPalApi([
        'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
            'verification_status' => 'FAILURE',
        ]),
    ]);

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => [
            'id' => 'CAPTURE-1',
            'supplementary_data' => [
                'related_ids' => ['order_id' => 'PAYPAL-ORDER-1'],
            ],
            'purchase_units' => [
                ['custom_id' => (string) $order->id],
            ],
        ],
    ], paypalWebhookHeaders());

    $response->assertStatus(400);
    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
    expect(License::query()->count())->toBe(0);
});

test('the webhook falls back to the local order when the paypal id is unknown', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => [
            'id' => 'CAPTURE-1',
            'supplementary_data' => [
                'related_ids' => ['order_id' => 'PAYPAL-ORDER-UNKNOWN'],
            ],
            'purchase_units' => [
                ['custom_id' => (string) $order->id],
            ],
        ],
    ], paypalWebhookHeaders());

    $response->assertOk();
    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($order->license)->not->toBeNull();
});

test('the return from paypal is rate limited', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    foreach (range(1, 20) as $attempt) {
        $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']));
    }

    $this->actingAs($buyer)->get(route('paypal.return', ['token' => 'PAYPAL-ORDER-1']))
        ->assertStatus(429);
});

test('the simulated payment endpoint is gone', function () {
    $buyer = User::factory()->create();
    $order = Order::factory()->for($buyer)->create();

    $this->actingAs($buyer)->post(route('purchases.pay', $order));

    $this->actingAs($buyer)->post('/purchases/'.$order->id.'/complete')->assertNotFound();
});

test('a refunded webhook revokes the license and removes the sale', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => [
            'id' => 'CAPTURE-1',
            'supplementary_data' => ['related_ids' => ['order_id' => 'PAYPAL-ORDER-1']],
            'purchase_units' => [['custom_id' => (string) $order->id]],
        ],
    ], paypalWebhookHeaders())->assertOk();

    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
    expect($product->refresh()->sales)->toBe(1);

    $response = $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
        'resource' => [
            'id' => 'REFUND-1',
            'refunds' => [['id' => 'REFUND-1']],
            'supplementary_data' => ['related_ids' => ['order_id' => 'PAYPAL-ORDER-1']],
            'purchase_units' => [['custom_id' => (string) $order->id]],
        ],
    ], paypalWebhookHeaders());

    $response->assertOk();
    expect($order->refresh()->status)->toBe(OrderStatus::Refunded);
    expect($order->license->refresh()->revoked)->toBeTrue();
    expect($product->refresh()->sales)->toBe(0);
});

test('a reversed capture cancels a pending order', function () {
    fakePayPalApi();

    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 2900]);
    $order = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 2900,
        'paypal_order_id' => 'PAYPAL-ORDER-1',
    ]);

    $response = $this->postJson(route('paypal.webhook'), [
        'event_type' => 'PAYMENT.CAPTURE.REVERSED',
        'resource' => [
            'id' => 'CAPTURE-1',
            'supplementary_data' => ['related_ids' => ['order_id' => 'PAYPAL-ORDER-1']],
            'purchase_units' => [['custom_id' => (string) $order->id]],
        ],
    ], paypalWebhookHeaders());

    $response->assertOk();
    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    expect(License::query()->count())->toBe(0);
});

test('the prune command removes stale pending orders', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['price' => 1900]);

    $stale = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 1900,
        'status' => OrderStatus::Pending,
        'created_at' => now()->subDays(10),
    ]);

    $fresh = Order::factory()->for($buyer)->for($product)->create([
        'amount' => 1900,
        'status' => OrderStatus::Pending,
    ]);

    $this->artisan('orders:prune', ['--pending' => 7])->assertSuccessful();

    expect(Order::query()->find($stale->id))->toBeNull();
    expect(Order::query()->find($fresh->id))->not->toBeNull();
});
