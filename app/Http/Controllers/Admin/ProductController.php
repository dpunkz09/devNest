<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * List every app, including the archived ones.
     */
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with(['user', 'tags'])
            ->withTrashed()
            ->orderByDesc('id')
            ->paginate(25);

        return Inertia::render('Admin/Products/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Archive an app.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        AuditLog::record('product.archived', $product);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The app was archived.']);

        return back();
    }

    /**
     * Restore an archived app.
     */
    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        AuditLog::record('product.restored', $product);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The app was restored.']);

        return back();
    }
}
