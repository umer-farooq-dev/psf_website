@extends('layouts.admin.app')

@section('title', $item ? translate('Edit_Realisation') : translate('Add_a_Realisation'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
            <h2 class="h1 mb-0">
                {{ $item ? translate('Edit_Realisation') : translate('Add_a_Realisation') }}
            </h2>
            <a href="{{ route('admin.psf-gallery.index') }}" class="btn btn-outline-primary">
                {{ translate('Back_to_List') }}
            </a>
        </div>

        <form action="{{ $item ? route('admin.psf-gallery.update', $item->id) : route('admin.psf-gallery.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">
                                {{ translate('Title') }} <span class="text-danger">*</span>
                            </label>
                            @include('admin-views.psf.partials._lang-input', ['name' => 'title', 'values' => old('title', psfRecordTexts($item, 'title')), 'placeholder' => translate('e_g_Installation_sanitaire_villa_Ouaga_2000')])
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="category">{{ translate('category') }}</label>
                            <input type="text" class="form-control" id="category" name="category"
                                   list="psf-gallery-categories"
                                   value="{{ old('category', $item->category ?? '') }}">
                            <datalist id="psf-gallery-categories">
                                @foreach ($categories as $option)
                                    <option value="{{ $option }}">{{ psfGalleryCategoryLabel($option) }}</option>
                                @endforeach
                            </datalist>
                            <small class="text-muted">{{ translate('Choose_one_or_type_a_new_one') }}</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ translate('description') }}</label>
                            @include('admin-views.psf.partials._lang-input', ['name' => 'description', 'values' => old('description', psfRecordTexts($item, 'description')), 'textarea' => 4])
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ translate('Location') }}</label>
                            @include('admin-views.psf.partials._lang-input', ['name' => 'location', 'values' => old('location', psfRecordTexts($item, 'location'))])
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="completed_on">{{ translate('Completed_on') }}</label>
                            <input type="date" class="form-control" id="completed_on" name="completed_on"
                                   value="{{ old('completed_on', $item?->completed_on?->format('Y-m-d') ?? '') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="sort_order">
                                {{ translate('Order') }}
                                <small class="text-muted">({{ translate('lower_shows_first') }})</small>
                            </label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order"
                                   min="0" max="9999" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="image">
                                {{ translate('image') }}
                                <small class="text-muted">(jpg, png, webp — {{ translate('Max_5_MB') }} — {{ translate('recommended_size') }} 600 × 500 px)</small>
                            </label>
                            <input type="file" class="form-control" id="image" name="image"
                                   accept="image/jpeg,image/png,image/webp">
                            @if ($item?->image_url)
                                <div class="mt-2">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->alt }}"
                                         class="rounded border" width="140" height="140"
                                         style="object-fit: cover;">
                                    <div class="fs-12 text-muted mt-1">
                                        {{ translate('Upload_a_new_file_to_replace_it') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                {{ translate('Image_Alt_Text') }}
                            </label>
                            @include('admin-views.psf.partials._lang-input', ['name' => 'image_alt_text', 'values' => old('image_alt_text', psfRecordTexts($item, 'image_alt_text'))])
                            <small class="text-muted">
                                {{ translate('Describes_the_image_for_search_engines_and_screen_readers') }}
                            </small>

                            <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-3 mt-3">
                                <div>
                                    <div class="fw-medium text-dark fs-14 mb-1">{{ translate('Visible_on_the_site') }}</div>
                                    <p class="mb-0 fs-12">{{ translate('Turn_off_to_hide_without_deleting') }}</p>
                                </div>
                                <label class="switcher" for="status">
                                    <input class="switcher_input" type="checkbox" value="1" name="status" id="status"
                                        {{ old('status', $item->status ?? true) ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.psf-gallery.index') }}" class="btn btn-outline-primary">
                    {{ translate('cancel') }}
                </a>
                <button type="submit" class="btn btn--primary">
                    {{ $item ? translate('Update') : translate('save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
