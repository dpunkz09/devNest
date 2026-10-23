<?php

namespace App\Actions;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SaveProduct
{
    /**
     * Create or update a product together with its features, tags and screenshots.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $screenshots
     * @param  array<int, string|null>  $captions
     */
    public function handle(
        ?Product $product,
        User $owner,
        array $data,
        ?UploadedFile $coverImage = null,
        array $screenshots = [],
        array $captions = [],
    ): Product {
        return DB::transaction(function () use ($product, $owner, $data, $coverImage, $screenshots, $captions): Product {
            $attributes = $this->attributes($product, $data);

            if ($coverImage !== null) {
                $attributes['cover_image'] = $this->storeCoverImage($product, $coverImage);
            }

            if ($product === null) {
                $product = $owner->products()->create($attributes);
            } else {
                $product->update($attributes);
            }

            $this->syncFeatures($product, $data['features'] ?? []);
            $this->syncTags($product, $data['tags'] ?? null);
            $this->storeScreenshots($product, $screenshots, $captions);

            return $product->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributes(?Product $product, array $data): array
    {
        $status = ProductStatus::from($data['status']);

        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $data['slug'] ?? null, $product),
            'tagline' => $data['tagline'],
            'description' => $data['description'],
            'platform' => $data['platform'],
            'status' => $status,
            'price' => (int) round(((float) $data['price']) * 100),
            'currency' => $product instanceof Product ? $product->currency : 'USD',
            'version' => $data['version'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'demo_url' => $data['demo_url'] ?? null,
            'download_url' => $data['download_url'] ?? null,
            'repository_url' => $data['repository_url'] ?? null,
            'published_at' => $status === ProductStatus::Published
                ? $this->publishedAt($product)
                : null,
        ];
    }

    /**
     * The moment the app was published, kept for apps that were published before.
     */
    protected function publishedAt(?Product $product): Carbon
    {
        if ($product instanceof Product && $product->published_at instanceof Carbon) {
            return $product->published_at;
        }

        return Carbon::now();
    }

    /**
     * Build a slug that no other product is already using.
     */
    protected function uniqueSlug(string $name, ?string $slug, ?Product $product): string
    {
        $base = Str::slug($slug ?: $name) ?: 'app';
        $candidate = $base;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $candidate)
            ->when($product, fn ($query) => $query->where('id', '!=', $product->id))
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    /**
     * Replace the cover image of the product and remove the previous file.
     */
    protected function storeCoverImage(?Product $product, UploadedFile $coverImage): string
    {
        if ($product?->cover_image) {
            Storage::disk('public')->delete($product->cover_image);
        }

        return $this->storeFile($coverImage, 'products/covers');
    }

    /**
     * Write an uploaded file to the public disk and return its path.
     */
    protected function storeFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException("The uploaded file could not be stored in [{$directory}].");
        }

        return $path;
    }

    /**
     * Replace the feature list of the product.
     *
     * @param  array<int, array<string, mixed>>  $features
     */
    protected function syncFeatures(Product $product, array $features): void
    {
        $product->features()->delete();

        $position = 0;

        foreach (array_values($features) as $feature) {
            $title = trim((string) ($feature['title'] ?? ''));

            if ($title === '') {
                continue;
            }

            $product->features()->create([
                'title' => $title,
                'description' => $feature['description'] ?? null,
                'position' => $position++,
            ]);
        }
    }

    /**
     * Attach the comma separated tag list to the product.
     */
    protected function syncTags(Product $product, ?string $tags): void
    {
        $names = collect(explode(',', (string) $tags))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values();

        $tagIds = $names
            ->map(function (string $name): int {
                $slug = Str::slug($name);

                return Tag::query()->firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            })
            ->all();

        $product->tags()->sync($tagIds);
    }

    /**
     * Append the uploaded screenshots to the product.
     *
     * @param  array<int, UploadedFile>  $screenshots
     * @param  array<int, string|null>  $captions
     */
    protected function storeScreenshots(Product $product, array $screenshots, array $captions): void
    {
        $position = $product->screenshots()->count();
        $captions = array_values($captions);

        foreach (array_values($screenshots) as $index => $screenshot) {
            $product->screenshots()->create([
                'path' => $this->storeFile($screenshot, 'products/screenshots'),
                'caption' => $captions[$index] ?? null,
                'position' => $position++,
            ]);
        }
    }
}
