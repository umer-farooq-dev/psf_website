<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PsfGalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PSF — public "Nos réalisations" page (client brief §17).
 */
class PsfGalleryController extends Controller
{
    public function __construct(
        private readonly PsfGalleryItem $galleryItem,
    ) {
    }

    public function index(Request $request): View
    {
        $category = $request['category'] ?? null;

        $items = $this->galleryItem->query()
            ->active()
            ->category($category)
            ->ordered()
            ->paginate(getWebConfig(name: 'psf_gallery_per_page') ?: 12)
            ->appends(['category' => $category]);

        // Only categories that actually have a visible entry are offered,
        // so the filter bar never shows a dead link.
        $usedCategories = $this->galleryItem->query()
            ->active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->all();

        return view('web-views.psf.gallery', [
            'items'      => $items,
            'category'   => $category,
            'categories' => $usedCategories,
        ]);
    }
}
