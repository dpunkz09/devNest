<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'seo' => [
                'title' => Setting::get('seo.title', config('app.name')),
                'description' => Setting::get('seo.description'),
                'keywords' => Setting::get('seo.keywords'),
                'robots' => Setting::get('seo.robots', 'index, follow'),
                'canonical_url' => Setting::get('seo.canonical_url'),
                'og_title' => Setting::get('seo.og_title'),
                'og_description' => Setting::get('seo.og_description'),
                'og_image' => Setting::get('seo.og_image'),
                'og_type' => Setting::get('seo.og_type', 'website'),
                'twitter_card' => Setting::get('seo.twitter_card', 'summary_large_image'),
                'twitter_site' => Setting::get('seo.twitter_site'),
                'twitter_creator' => Setting::get('seo.twitter_creator'),
            ],
            'headerPages' => $this->navigationPages('header'),
            'footerPages' => $this->navigationPages('footer'),
        ];
    }

    /**
     * Cached list of published pages for a given placement.
     *
     * @return array<int, array{title: string, href: string}>
     */
    protected function navigationPages(string $placement): array
    {
        $cached = cache()->rememberForever('pages:navigation', function (): array {
            return [
                'header' => Page::inHeader()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['slug', 'title'])
                    ->map(fn (Page $page): array => [
                        'title' => $page->title,
                        'href' => route('pages.show', $page),
                    ])
                    ->all(),
                'footer' => Page::inFooter()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['slug', 'title'])
                    ->map(fn (Page $page): array => [
                        'title' => $page->title,
                        'href' => route('pages.show', $page),
                    ])
                    ->all(),
            ];
        });

        return $cached[$placement] ?? [];
    }
}
