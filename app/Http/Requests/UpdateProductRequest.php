<?php

namespace App\Http\Requests;

use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'alpha_dash'],
            'tagline' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'platform' => ['required', Rule::enum(AppPlatform::class)],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'version' => ['nullable', 'string', 'max:40'],
            'requirements' => ['nullable', 'string', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'download_url' => ['nullable', 'url', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'features' => ['nullable', 'array', 'max:20'],
            'features.*.title' => ['required', 'string', 'max:120'],
            'features.*.description' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'screenshots' => ['nullable', 'array', 'max:10'],
            'screenshots.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'screenshot_captions' => ['nullable', 'array', 'max:10'],
            'screenshot_captions.*' => ['nullable', 'string', 'max:160'],
        ];
    }
}
