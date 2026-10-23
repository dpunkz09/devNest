<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Terms of service.
     */
    public function terms(): Response
    {
        $page = Page::where('slug', 'terms')->firstOrFail();

        abort_unless($page->published, 404);

        return Inertia::render('Pages/Show', ['page' => $page]);
    }

    /**
     * Privacy policy.
     */
    public function privacy(): Response
    {
        $page = Page::where('slug', 'privacy')->firstOrFail();

        abort_unless($page->published, 404);

        return Inertia::render('Pages/Show', ['page' => $page]);
    }

    /**
     * Render any published page.
     */
    public function show(Page $page): Response
    {
        abort_unless($page->published, 404);

        return Inertia::render('Pages/Show', [
            'page' => $page,
        ]);
    }
}
