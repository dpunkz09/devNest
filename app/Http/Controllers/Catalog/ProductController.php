<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Browse the published catalogue.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', Rule::enum(AppPlatform::class)],
            'tag' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', 'string', Rule::in(['newest', 'popular', 'price_low', 'price_high', 'name'])],
        ]);

        $products = Product::query()
            ->published()
            ->with(['user', 'tags'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('tagline', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['platform'] ?? null, fn ($query, string $platform) => $query->where('platform', $platform))
            ->when($filters['tag'] ?? null, fn ($query, string $tag) => $query->whereHas('tags', fn ($query) => $query->where('slug', $tag)))
            ->when(
                $filters['sort'] ?? null,
                fn ($query, string $sort) => match ($sort) {
                    'popular' => $query->orderByDesc('sales')->orderByDesc('views'),
                    'price_low' => $query->orderBy('price')->orderBy('id'),
                    'price_high' => $query->orderByDesc('price')->orderBy('id'),
                    'name' => $query->orderBy('name')->orderBy('id'),
                    default => $query->orderByDesc('published_at')->orderByDesc('id'),
                },
            )
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product): ProductResource => new ProductResource($product));

        return Inertia::render('Catalog/Index', [
            'products' => $products,
            'filters' => [
                'q' => $filters['q'] ?? null,
                'platform' => $filters['platform'] ?? null,
                'tag' => $filters['tag'] ?? null,
                'sort' => $filters['sort'] ?? 'newest',
            ],
            'platforms' => AppPlatform::selectOptions(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Showcase a single app.
     */
    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->status === ProductStatus::Published, 404);

        $product->load(['user', 'tags', 'screenshots', 'features']);
        $this->countView($request, $product);

        $related = Product::query()
            ->published()
            ->where('id', '!=', $product->id)
            ->where('platform', $product->platform)
            ->with(['user', 'tags'])
            ->orderByDesc('sales')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return Inertia::render('Catalog/Show', [
            'product' => new ProductResource($product),
            'related' => ProductResource::collection($related),
            'owned' => $request->user()
                ? $request->user()->orders()->where('product_id', $product->id)->exists()
                : false,
        ]);
    }

    /**
     * Count a view once per visitor per day.
     *
     * Without this a reload loop or a bot could push an app to the top of the
     * most popular list, so every visitor only counts a single time.
     */
    protected function countView(Request $request, Product $product): void
    {
        $visitor = $request->user()?->getAuthIdentifier() ?? $request->session()->getId();

        $key = 'product-view:'.$product->id.':'.sha1((string) $visitor);

        if (Cache::add($key, true, now()->addDay())) {
            $product->increment('views');
        }
    }
}
