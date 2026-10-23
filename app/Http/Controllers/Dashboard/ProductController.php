<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\SaveProduct;
use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * List the apps owned by the current user.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', Rule::enum(ProductStatus::class)],
        ]);

        $products = $request->user()
            ->products()
            ->withTrashed()
            ->with(['user', 'tags'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product): ProductResource => new ProductResource($product));

        return Inertia::render('Dashboard/Products/Index', [
            'products' => $products,
            'filters' => [
                'q' => $filters['q'] ?? null,
                'status' => $filters['status'] ?? null,
            ],
            'statuses' => ProductStatus::selectOptions(),
        ]);
    }

    /**
     * Show the form used to publish a new app.
     */
    public function create(): Response
    {
        return Inertia::render('Dashboard/Products/Create', [
            'platforms' => AppPlatform::selectOptions(),
            'statuses' => ProductStatus::selectOptions(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Persist a new app.
     */
    public function store(StoreProductRequest $request, SaveProduct $saveProduct): RedirectResponse
    {
        $product = $saveProduct->handle(
            null,
            $request->user(),
            $request->validated(),
            $request->file('cover_image'),
            $request->file('screenshots') ?? [],
            $request->input('screenshot_captions') ?? [],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => "“{$product->name}” has been created."]);

        return to_route('dashboard.products.edit', $product);
    }

    /**
     * Show the form used to edit an existing app.
     */
    public function edit(Request $request, Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load(['user', 'tags', 'features', 'screenshots']);

        return Inertia::render('Dashboard/Products/Edit', [
            'product' => new ProductResource($product),
            'platforms' => AppPlatform::selectOptions(),
            'statuses' => ProductStatus::selectOptions(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Persist the changes made to an app.
     */
    public function update(UpdateProductRequest $request, Product $product, SaveProduct $saveProduct): RedirectResponse
    {
        $this->authorize('update', $product);

        $product = $saveProduct->handle(
            $product,
            $request->user(),
            $request->validated(),
            $request->file('cover_image'),
            $request->file('screenshots') ?? [],
            $request->input('screenshot_captions') ?? [],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => "“{$product->name}” has been updated."]);

        return to_route('dashboard.products.edit', $product);
    }

    /**
     * Archive an app.
     *
     * The listing is hidden from the catalogue, but the sales it made and the
     * licenses of its buyers are kept.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "“{$name}” has been archived."]);

        return to_route('dashboard.products.index');
    }

    /**
     * Bring an archived app back online.
     */
    public function restore(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => "“{$product->name}” has been restored."]);

        return to_route('dashboard.products.index');
    }

    /**
     * Remove a single screenshot from an app.
     */
    public function destroyScreenshot(Request $request, Product $product, int $screenshot): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->screenshots()->whereKey($screenshot)->first()?->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Screenshot removed.']);

        return back();
    }
}
