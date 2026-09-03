<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PsfGalleryItem;
use App\Services\PsfGalleryService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * PSF — admin CRUD for "Nos réalisations" (client brief §17).
 */
class PsfGalleryController extends BaseController
{
    public function __construct(
        private readonly PsfGalleryItem    $galleryItem,
        private readonly PsfGalleryService $service,
    ) {
    }

    public function index(?Request $request = null, ?string $type = null): View
    {
        $search = $request['searchValue'] ?? null;
        $category = $request['category'] ?? null;

        $items = $this->galleryItem->query()
            ->category($category)
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            }))
            ->ordered()
            ->paginate(getWebConfig(name: 'pagination_limit') ?: 25)
            ->appends(['searchValue' => $search, 'category' => $category]);

        return view('admin-views.psf.gallery.list', [
            'items'      => $items,
            'search'     => $search,
            'category'   => $category,
            'categories' => psfGalleryCategories(),
            'total'      => $this->galleryItem->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin-views.psf.gallery.form', [
            'item'       => null,
            'categories' => psfGalleryCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->service->rules(), $this->service->messages());

        $item = new PsfGalleryItem($this->service->payload($request));
        $item->image = $this->service->storeImage($request);
        $item->save();

        Toastr::success(translate('added_successfully'));

        return redirect()->route('admin.psf-gallery.index');
    }

    public function edit(string|int $id): View|RedirectResponse
    {
        $item = $this->galleryItem->find($id);
        if (!$item) {
            Toastr::error(translate('No_realisation_found'));
            return redirect()->route('admin.psf-gallery.index');
        }

        return view('admin-views.psf.gallery.form', [
            'item'       => $item,
            'categories' => psfGalleryCategories(),
        ]);
    }

    public function update(Request $request, string|int $id): RedirectResponse
    {
        $item = $this->galleryItem->find($id);
        if (!$item) {
            Toastr::error(translate('No_realisation_found'));
            return redirect()->route('admin.psf-gallery.index');
        }

        $request->validate($this->service->rules(), $this->service->messages());

        $item->fill($this->service->payload($request));

        $uploaded = $this->service->storeImage($request);
        if ($uploaded) {
            $old = $item->image;
            $item->image = $uploaded;
            $this->service->removeImage($old);
        }
        $item->save();

        Toastr::success(translate('updated_successfully'));

        return back();
    }

    /**
     * Show/hide one entry without deleting it.
     */
    public function updateStatus(string|int $id): RedirectResponse
    {
        $item = $this->galleryItem->find($id);
        if ($item) {
            $item->status = !$item->status;
            $item->save();
            Toastr::success(translate('status_updated_successfully'));
        }

        return back();
    }

    public function delete(string|int $id): RedirectResponse
    {
        $item = $this->galleryItem->find($id);
        if ($item) {
            $this->service->removeImage($item->image);
            $item->delete();
            Toastr::success(translate('deleted_successfully'));
        }

        return redirect()->route('admin.psf-gallery.index');
    }
}
