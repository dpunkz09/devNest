<?php

use App\Enums\AppPlatform;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('guests can browse the catalogue', function () {
    Product::factory()->create(['name' => 'Pixel Tracker']);

    $response = $this->get(route('apps.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Catalog/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Pixel Tracker')
    );
});

test('draft and archived apps are hidden from the catalogue', function () {
    Product::factory()->draft()->create(['name' => 'Hidden Draft']);
    Product::factory()->create(['name' => 'Visible App', 'status' => 'archived']);

    $response = $this->get(route('apps.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Catalog/Index')
        ->has('products.data', 0)
    );
});

test('the catalogue can be filtered by platform', function () {
    Product::factory()->create(['name' => 'Web App', 'platform' => AppPlatform::Web]);
    Product::factory()->create(['name' => 'Android App', 'platform' => AppPlatform::Android]);

    $response = $this->get(route('apps.index', ['platform' => 'android']));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Android App')
        ->where('filters.platform', 'android')
    );
});

test('the catalogue can be filtered by search term and tag', function () {
    $tag = Tag::factory()->create(['name' => 'Productivity', 'slug' => 'productivity']);

    Product::factory()->create(['name' => 'Alpha Notes']);
    Product::factory()->create(['name' => 'Beta Tracker'])->tags()->attach($tag);

    $this->get(route('apps.index', ['q' => 'Alpha']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Alpha Notes')
        );

    $this->get(route('apps.index', ['tag' => 'productivity']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Beta Tracker')
        );
});

test('a published app can be shown publicly', function () {
    $product = Product::factory()->create(['name' => 'Pixel Tracker']);

    $response = $this->get(route('apps.show', $product));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Catalog/Show')
        ->where('product.name', 'Pixel Tracker')
        ->where('owned', false)
    );
});

test('the seller email address is never exposed publicly', function () {
    $seller = User::factory()->create(['email' => 'seller@example.com']);
    $product = Product::factory()->for($seller)->create();

    $response = $this->get(route('apps.show', $product));

    $response->assertOk();
    $response->assertDontSee('seller@example.com');
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('product.seller.name', $seller->name)
        ->missing('product.owner')
    );
});

test('a draft app returns a 404 for guests', function () {
    $product = Product::factory()->draft()->create();

    $this->get(route('apps.show', $product))->assertNotFound();
});

test('the download link is never exposed to visitors', function () {
    $product = Product::factory()->create(['download_url' => 'https://example.com/secret-app.zip']);

    $response = $this->get(route('apps.show', $product));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Catalog/Show')
        ->missing('product.download_url')
    );
});

test('the catalogue does not leak download links', function () {
    Product::factory()->create(['download_url' => 'https://example.com/secret-app.zip']);

    $this->get(route('apps.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Catalog/Index')
            ->missing('products.data.0.download_url')
        );
});

test('a signed in visitor does not receive the download link', function () {
    $buyer = User::factory()->create();
    $product = Product::factory()->create(['download_url' => 'https://example.com/secret-app.zip']);

    $this->actingAs($buyer)->get(route('apps.show', $product))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('product.download_url')
        );
});

test('the developer still receives the download link', function () {
    $developer = User::factory()->create();
    $product = Product::factory()->for($developer)->create(['download_url' => 'https://example.com/secret-app.zip']);

    $this->actingAs($developer)->get(route('apps.show', $product))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('product.download_url', 'https://example.com/secret-app.zip')
        );
});

test('the storefront landing page lists published apps', function () {
    Product::factory()->create(['name' => 'Featured App']);
    Product::factory()->draft()->create(['name' => 'Hidden Draft']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Storefront/Home')
        ->has('featured', 1)
        ->where('featured.0.name', 'Featured App')
        ->where('stats.apps', 1)
    );
});

test('a view is counted once per visitor', function () {
    $product = Product::factory()->create();
    $visitor = User::factory()->create();

    $this->actingAs($visitor)->get(route('apps.show', $product));
    $this->actingAs($visitor)->get(route('apps.show', $product));

    expect($product->refresh()->views)->toBe(1);
});

test('every visitor adds a view of its own', function () {
    $product = Product::factory()->create();

    $this->actingAs(User::factory()->create())->get(route('apps.show', $product));
    $this->actingAs(User::factory()->create())->get(route('apps.show', $product));

    expect($product->refresh()->views)->toBe(2);
});
