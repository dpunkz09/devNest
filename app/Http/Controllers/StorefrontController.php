<?php

namespace App\Http\Controllers;

use App\Enums\AppPlatform;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    /**
     * Landing page of the storefront.
     */
    public function index(): Response
    {
        $featured = Product::query()
            ->published()
            ->with('user')
            ->orderByDesc('sales')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $latest = Product::query()
            ->published()
            ->with('user')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $featuredIds = $featured->pluck('id');

        return Inertia::render('Storefront/Home', [
            'featured' => ProductResource::collection($featured),
            'latest' => ProductResource::collection($latest->whereNotIn('id', $featuredIds)->values()),
            'platforms' => AppPlatform::selectOptions(),
            'tags' => Tag::query()->orderBy('name')->limit(12)->get(),
            'stats' => [
                'apps' => Product::query()->published()->count(),
                'platforms' => count(AppPlatform::cases()),
            ],
        ]);
    }
}
