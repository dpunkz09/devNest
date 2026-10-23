<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'product_id' => $this->product_id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'refunded' => $this->refunded,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'customer' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'license' => $this->whenLoaded('license', fn () => $this->license
                ? new LicenseResource($this->license)
                : null),
        ];
    }
}
