<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'platform' => $this->platform->value,
            'platform_label' => $this->platform->label(),
            'platform_icon' => $this->platform->icon(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'price' => $this->price,
            'price_formatted' => $this->price_formatted,
            'is_free' => $this->is_free,
            'currency' => $this->currency,
            'version' => $this->version,
            'requirements' => $this->requirements,
            'cover_image_url' => $this->cover_image_url,
            'demo_url' => $this->demo_url,
            'download_url' => $this->when($this->isOwnedBy($request->user()), $this->download_url),
            'repository_url' => $this->repository_url,
            'views' => $this->views,
            'sales' => $this->sales,
            'archived' => $this->trashed(),
            'archived_at' => $this->deleted_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'seller' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'tags' => $this->whenLoaded('tags', fn () => $this->tags
                ->map(fn ($tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
                ->values()),
            'features' => $this->whenLoaded('features', fn () => $this->features
                ->map(fn ($feature): array => [
                    'id' => $feature->id,
                    'title' => $feature->title,
                    'description' => $feature->description,
                ])
                ->values()),
            'screenshots' => $this->whenLoaded('screenshots', fn () => $this->screenshots
                ->map(fn ($screenshot): array => [
                    'id' => $screenshot->id,
                    'url' => $screenshot->url,
                    'caption' => $screenshot->caption,
                ])
                ->values()),
        ];
    }

    /**
     * Whether the viewer is the developer who published the app.
     *
     * The download link is only ever shared with the developer. Buyers get
     * it through the protected purchase download route.
     */
    protected function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }
}
