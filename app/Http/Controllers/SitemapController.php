<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Render the sitemap of the storefront.
     */
    public function index(): Response
    {
        $urls = collect();

        $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('apps.index'), 'priority' => '0.9', 'changefreq' => 'daily']);
        $urls->push(['loc' => route('terms'), 'priority' => '0.3', 'changefreq' => 'monthly']);
        $urls->push(['loc' => route('privacy'), 'priority' => '0.3', 'changefreq' => 'monthly']);

        foreach (Page::published()->get() as $page) {
            $urls->push([
                'loc' => route('pages.show', $page),
                'priority' => '0.4',
                'changefreq' => 'monthly',
            ]);
        }

        foreach (Product::query()->where('status', 'published')->get() as $product) {
            $urls->push([
                'loc' => route('apps.show', $product),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Render the robots.txt file.
     */
    public function robots(): Response
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Disallow: /purchases/\n";
        $content .= "\n";
        $content .= 'Sitemap: '.route('sitemap')."\n";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
