<?php

namespace App\Http\Resources;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin License
 */
class LicenseResource extends JsonResource
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
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'key' => $this->key,
            'downloads' => $this->downloads,
            'activated_at' => $this->activated_at?->toIso8601String(),
            'revoked' => $this->revoked,
            'revoked_at' => $this->revoked_at?->toIso8601String(),
        ];
    }
}
