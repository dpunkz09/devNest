<?php

use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductScreenshot;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

test('guests cannot reach the product dashboard', function () {
    $this->get(route('dashboard.products.index'))->assertRedirect(route('login'));
});

test('a developer only sees their own apps', function () {
    $user = User::factory()->create();
    Product::factory()->for($user)->create(['name' => 'Mine']);
    Product::factory()->create(['name' => 'Somebody Else']);

    $response = $this->actingAs($user)->get(route('dashboard.products.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Dashboard/Products/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Mine')
    );
});

test('an app can be created', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('dashboard.products.store'), [
        'name' => 'Pixel Tracker',
        'slug' => 'pixel-tracker',
        'tagline' => 'Track every pixel of your screen',
        'description' => 'A long description of the app.',
        'platform' => AppPlatform::Web->value,
        'status' => ProductStatus::Published->value,
        'price' => '19.99',
        'version' => '1.0.0',
        'requirements' => 'Any modern browser',
        'demo_url' => 'https://example.com/demo',
        'download_url' => 'https://example.com/download',
        'repository_url' => 'https://github.com/example/app',
        'tags' => 'design, productivity',
        'features' => [
            ['title' => 'Offline mode', 'description' => 'Works without a network.'],
            ['title' => 'Shared workspaces', 'description' => null],
        ],
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        'screenshots' => [UploadedFile::fake()->image('screen.png')],
        'screenshot_captions' => ['The main dashboard'],
    ]);

    $product = Product::query()->firstOrFail();

    $response->assertRedirect(route('dashboard.products.edit', $product));
    expect($product->name)->toBe('Pixel Tracker');
    expect($product->slug)->toBe('pixel-tracker');
    expect($product->platform)->toBe(AppPlatform::Web);
    expect($product->status)->toBe(ProductStatus::Published);
    expect($product->price)->toBe(1999);
    expect($product->features)->toHaveCount(2);
    expect($product->tags)->toHaveCount(2);
    expect($product->screenshots)->toHaveCount(1);
    expect($product->cover_image)->not->toBeNull();

    Storage::disk('public')->assertExists($product->cover_image);
});

test('the slug is generated from the name when it is omitted', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('dashboard.products.store'), [
        'name' => 'Pixel Tracker',
        'tagline' => 'Track every pixel',
        'description' => 'A long description of the app.',
        'platform' => AppPlatform::Web->value,
        'status' => ProductStatus::Draft->value,
        'price' => '0',
    ]);

    expect(Product::query()->firstOrFail()->slug)->toBe('pixel-tracker');
});

test('creating an app validates the payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('dashboard.products.store'), ['name' => ''])
        ->assertSessionHasErrors(['name', 'tagline', 'description', 'platform', 'status', 'price']);
});

test('a developer can update their own app', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('dashboard.products.update', $product), [
        'name' => 'New Name',
        'tagline' => 'A new tagline',
        'description' => 'A new description.',
        'platform' => AppPlatform::Android->value,
        'status' => ProductStatus::Draft->value,
        'price' => '5',
    ]);

    $response->assertRedirect(route('dashboard.products.edit', $product));
    expect($product->refresh()->name)->toBe('New Name');
    expect($product->platform)->toBe(AppPlatform::Android);
    expect($product->price)->toBe(500);
});

test('a developer cannot update an app they do not own', function () {
    $product = Product::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->put(route('dashboard.products.update', $product), [
            'name' => 'Hijacked',
            'tagline' => 'A new tagline',
            'description' => 'A new description.',
            'platform' => AppPlatform::Web->value,
            'status' => ProductStatus::Published->value,
            'price' => '1',
        ])
        ->assertForbidden();

    expect($product->refresh()->name)->not->toBe('Hijacked');
});

test('a developer can delete their own app', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('dashboard.products.destroy', $product))
        ->assertRedirect(route('dashboard.products.index'));

    expect(Product::query()->count())->toBe(0);
});

test('a developer cannot delete an app they do not own', function () {
    $product = Product::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete(route('dashboard.products.destroy', $product))
        ->assertForbidden();

    expect(Product::query()->count())->toBe(1);
});

test('a screenshot can be removed from an app', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();
    $screenshot = ProductScreenshot::factory()->for($product)->create();

    $this->actingAs($user)
        ->delete(route('dashboard.products.screenshots.destroy', [$product, $screenshot]))
        ->assertRedirect();

    expect($product->screenshots()->count())->toBe(0);
});

test('a screenshot cannot be removed from an app owned by somebody else', function () {
    $product = Product::factory()->create();
    $screenshot = ProductScreenshot::factory()->for($product)->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->delete(route('dashboard.products.screenshots.destroy', [$product, $screenshot]))
        ->assertForbidden();

    expect($product->screenshots()->count())->toBe(1);
});

test('the sales list only contains orders for the apps of the current user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();

    $user->orders()->create([
        'product_id' => $product->id,
        'reference' => 'ORD-OWNED00001',
        'amount' => 1900,
        'currency' => 'USD',
        'status' => 'paid',
    ]);

    $this->actingAs($user)->get(route('dashboard.orders.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard/Orders/Index')
            ->has('orders.data', 1)
        );
});

test('a developer can review a sale of their own app', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create();
    $order = $user->orders()->create([
        'product_id' => $product->id,
        'reference' => 'ORD-OWNED00002',
        'amount' => 1900,
        'currency' => 'USD',
        'status' => 'paid',
    ]);

    $this->actingAs($user)->get(route('dashboard.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard/Orders/Show')
            ->where('order.reference', 'ORD-OWNED00002')
        );
});

test('a developer cannot review the sale of somebody else app', function () {
    $order = Order::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->get(route('dashboard.orders.show', $order))
        ->assertForbidden();
});

test('the dashboard summarises the apps and the sales of the current user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->for($user)->create(['price' => 1900]);

    $user->orders()->create([
        'product_id' => $product->id,
        'reference' => 'ORD-OWNED00003',
        'amount' => 1900,
        'currency' => 'USD',
        'status' => 'paid',
    ]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('stats.products', 1)
            ->where('stats.published', 1)
            ->where('stats.sales', 1)
            ->where('stats.revenue', 1900)
            ->has('recentOrders', 1)
            ->has('topProducts', 1)
        );
});

test('a new tag is created when it does not exist yet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('dashboard.products.store'), [
        'name' => 'Tagged App',
        'tagline' => 'With a tag',
        'description' => 'A long description of the app.',
        'platform' => AppPlatform::Web->value,
        'status' => ProductStatus::Draft->value,
        'price' => '0',
        'tags' => 'Brand New Tag',
    ]);

    expect(Tag::query()->where('slug', 'brand-new-tag')->exists())->toBeTrue();
});
