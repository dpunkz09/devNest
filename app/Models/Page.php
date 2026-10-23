<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $format
 * @property string|null $content
 * @property string $location
 * @property int $sort_order
 * @property Carbon|null $published_at
 */
#[Fillable(['slug', 'title', 'format', 'content', 'location', 'sort_order', 'published_at'])]
class Page extends Model
{
    /**
     * @var list<string>
     */
    protected $appends = ['published', 'rendered_content'];

    /**
     * Flush the cached navigation lists whenever a page changes.
     */
    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget('pages:navigation'));
        static::deleted(fn () => cache()->forget('pages:navigation'));
    }

    /**
     * Route model binding uses the slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The rendered HTML content.
     *
     * Markdown is converted to HTML on the fly; HTML content is returned as
     * is.
     */
    public function getRenderedContentAttribute(): string
    {
        if ($this->content === null || $this->content === '') {
            return '';
        }

        if ($this->format === 'markdown') {
            return Str::markdown($this->content, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }

        return $this->content;
    }

    /**
     * Only the pages that are published.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Pages that belong in the header.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInHeader(Builder $query): Builder
    {
        return $query->published()->whereIn('location', ['header', 'both']);
    }

    /**
     * Pages that belong in the footer.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInFooter(Builder $query): Builder
    {
        return $query->published()->whereIn('location', ['footer', 'both']);
    }

    /**
     * Whether the page is published.
     */
    public function getPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * Generate a unique slug from a title.
     */
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);

        if ($slug === '') {
            $slug = 'page';
        }

        $original = $slug;
        $counter = 2;

        while (self::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
