{{-- PSF · shop page body shared by every product list (all products,
     category, brand, search, offers, flash deal), template "shop-list".
     $products, $categories, $activeBrands
     $bannerTitle, $breadcrumbs : page banner
     $showDataFrom              : show the best selling / top rated… filter
     $categoryRoute, $brandRoute, $resetUrl : see _psf-shop-filters --}}
@php
    $psfShopSettings = psfShopSettings();
    $psfView = psfShopView();
    $psfPerPage = psfProductsPerPage();
    $psfSortOptions = [
        'latest'          => translate('sort_latest'),
        'low-high'        => translate('sort_price_low_high'),
        'high-low'        => translate('sort_price_high_low'),
        'rating-high-low' => translate('sort_rating_high_low'),
        'rating-low-high' => translate('sort_rating_low_high'),
        'a-z'             => translate('sort_name_a_z'),
        'z-a'             => translate('sort_name_z_a'),
    ];
@endphp

<div class="page-content bg-light">
    @include('web-views.partials._psf-page-banner', ['bannerTitle' => $bannerTitle, 'breadcrumbs' => $breadcrumbs])

    @isset($beforeList)
        {!! $beforeList !!}
    @endisset

    <section class="content-inner-3 pt-3 psf-shop" id="psf-shop" data-url="{{ url()->current() }}">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">
                    <div class="sticky-xl-top">
                        <a href="javascript:void(0);" class="panel-close-btn" aria-label="{{ translate('close') }}">
                            @include('web-views.partials._psf-icon', ['icon' => 'close'])
                        </a>
                        <div class="shop-filter mt-xl-2 mt-0">
                            @include('web-views.products.partials._psf-shop-filters')
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="filter-wrapper">
                        <div class="filter-left-area psf-shop-summary-target">
                            <ul class="filter-tag"></ul>
                            <span class="psf-shop-count"></span>
                        </div>
                        <div class="filter-right-area">
                            <a href="javascript:void(0);" class="panel-btn">
                                @include('web-views.partials._psf-icon', ['icon' => 'filter'])
                                {{ translate('Filter') }}
                            </a>
                            <div class="form-group">
                                <select class="default-select psf-shop-select" name="sort_by" aria-label="{{ translate('sort_by') }}">
                                    @foreach ($psfSortOptions as $sortValue => $sortLabel)
                                        <option value="{{ $sortValue }}" {{ (request('sort_by') ?: 'latest') === $sortValue ? 'selected' : '' }}>{{ $sortLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group Category">
                                <select class="default-select psf-shop-select" name="per_page" aria-label="{{ translate('Products_per_page') }}">
                                    @foreach ($psfShopSettings['per_page'] as $perPageValue)
                                        <option value="{{ $perPageValue }}" {{ $psfPerPage === $perPageValue ? 'selected' : '' }}>{{ psfTranslate('shop_per_page_option', ['count' => $perPageValue]) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="shop-tab">
                                <ul class="nav" role="tablist" id="dz-shop-tab">
                                    @foreach (PSF_SHOP_VIEWS as $viewKey)
                                        <li class="nav-item" role="presentation">
                                            <a href="javascript:void(0);" class="nav-link psf-shop-view {{ $psfView === $viewKey ? 'active' : '' }}"
                                               data-view="{{ $viewKey }}" role="tab" aria-selected="{{ $psfView === $viewKey ? 'true' : 'false' }}"
                                               aria-label="{{ translate('shop_view_' . $viewKey) }}" title="{{ translate('shop_view_' . $viewKey) }}">
                                                @include('web-views.partials._psf-icon', ['icon' => 'view-' . $viewKey])
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div id="ajax-products-view" class="psf-shop-results" aria-live="polite">
                        @include('web-views.products._ajax-products', ['products' => $products])
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
