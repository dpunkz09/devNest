<?php

namespace Database\Factories;

use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(implode(' ', Arr::wrap(fake()->unique()->words(3))));

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'tagline' => fake()->sentence(8),
            'description' => fake()->paragraphs(3, true),
            'platform' => fake()->randomElement(AppPlatform::cases()),
            'status' => ProductStatus::Published,
            'price' => fake()->randomElement([0, 1900, 2900, 4900, 7900]),
            'currency' => 'USD',
            'version' => '1.'.fake()->numberBetween(0, 5).'.0',
            'requirements' => fake()->randomElement([
                'Windows 10 or later, 4 GB RAM',
                'macOS 12 or later, 2 GB RAM',
                'Android 9.0 or later',
                'Any modern browser',
            ]),
            'demo_url' => 'https://example.com/demo',
            'download_url' => 'https://example.com/download',
            'repository_url' => 'https://github.com/example/app',
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the product is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the product is available for free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
        ]);
    }
}
