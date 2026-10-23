<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * List every page.
     */
    public function index(Request $request): Response
    {
        $pages = Page::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(25);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for a new page.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => null,
        ]);
    }

    /**
     * Store a new page.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $validated['slug'] = $validated['slug'] ?: Page::generateSlug($validated['title']);
        $validated['published_at'] = $request->boolean('published') ? now() : null;

        unset($validated['published']);

        $page = Page::create($validated);

        AuditLog::record('page.created', $page);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The page was created.']);

        return to_route('admin.pages.index');
    }

    /**
     * Show the form for an existing page.
     */
    public function edit(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page,
        ]);
    }

    /**
     * Update an existing page.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validated($request, $page);

        $validated['published_at'] = $request->boolean('published')
            ? ($page->published_at ?? now())
            : null;

        unset($validated['published']);

        $page->update($validated);

        AuditLog::record('page.updated', $page);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The page was updated.']);

        return to_route('admin.pages.index');
    }

    /**
     * Remove a page.
     */
    public function destroy(Page $page): RedirectResponse
    {
        AuditLog::record('page.deleted', $page, ['title' => $page->title]);

        $page->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The page was deleted.']);

        return back();
    }

    /**
     * Validate the incoming page data.
     *
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', 'unique:pages,slug,'.($page->id ?? 'NULL')],
            'format' => ['required', 'in:html,markdown'],
            'content' => ['nullable', 'string', 'max:100000'],
            'location' => ['required', 'in:header,footer,both,hidden'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['boolean'],
        ]);
    }
}
