{{-- PSF · product list of the shop pages, first render and every filter
     request (ProductListController answers with this view as html_products).
     Also carries the result count and the active-filter tags, which
     psf-shop.js moves into the toolbar above the list. --}}
@php
    $psfView = psfShopView();
    $psfColumns = ['list' => 'col-12', 'column' => 'col-6', 'grid' => 'col-6 col-md-4'][$psfView];

    // filters that can be taken off one by one
    $psfActiveFilters = [];
    $psfCategoryId = request('sub_sub_category_id') ?: (request('sub_category_id') ?: request('category_id'));
    if (request('data_from') === 'category' && $psfCategoryId && ($psfCategory = \App\Models\Category::find($psfCategoryId))) {
        $psfActiveFilters[] = ['label' => $psfCategory->name, 'href' => request()->is('flash-deals/*') ? url()->current() : route('products')];
    }
    if (request('data_from') === 'brand' && request('brand_id') && ($psfBrand = \App\Models\Brand::find(request('brand_id')))) {
        $psfActiveFilters[] = ['label' => $psfBrand->name, 'href' => request()->is('flash-deals/*') ? url()->current() : route('products')];
    }
    $psfSearch = request('product_name') ?: (request('name') ?: request('search'));
    if ($psfSearch) {
        $psfActiveFilters[] = ['label' => '“' . $psfSearch . '”', 'clear' => 'product_name,name,search'];
    }
    if (is_numeric(request('min_price')) || is_numeric(request('max_price'))) {
        // the price filter is typed in the visitor's currency already
        $psfMoney = fn ($amount) => setCurrencySymbol(amount: (float) $amount, currencyCode: getCurrencyCode(type: 'web'), type: 'web');
        $psfActiveFilters[] = [
            'label' => translate('price') . ' : ' . $psfMoney(request('min_price') ?: 0) . ' – ' . (is_numeric(request('max_price')) ? $psfMoney(request('max_price')) : '∞'),
            'clear' => 'min_price,max_price',
        ];
    }
    foreach (array_filter((array) request('color_ids'), 'is_string') as $psfColor) {
        $psfActiveFilters[] = ['label' => psfColorName($psfColor), 'clear-value' => 'color_ids[]|' . $psfColor];
    }
    foreach (array_filter((array) request('attribute_values'), 'is_string') as $psfAttribute => $psfValue) {
        $psfActiveFilters[] = ['label' => translate($psfAttribute) . ' : ' . $psfValue, 'clear' => 'attribute_values[' . $psfAttribute . ']'];
    }
    if (request('tag_id') && ($psfTag = \App\Models\Tag::find(request('tag_id')))) {
        $psfActiveFilters[] = ['label' => '#' . $psfTag->tag, 'clear' => 'tag_id'];
    }
    if (in_array(request('data_from'), ['best-selling', 'top-rated', 'most-favorite'], true)) {
        $psfActiveFilters[] = ['label' => translate(['best-selling' => 'Best_Selling', 'top-rated' => 'Top_Rated', 'most-favorite' => 'Most_Favorite'][request('data_from')]), 'clear' => 'data_from'];
    }
@endphp

<template class="psf-shop-summary">
    <ul class="filter-tag">
        @foreach ($psfActiveFilters as $psfFilter)
            <li>
                <a href="{{ $psfFilter['href'] ?? 'javascript:void(0);' }}" class="tag-btn psf-filter-remove"
                   @isset($psfFilter['clear']) data-clear="{{ $psfFilter['clear'] }}" @endisset
                   @isset($psfFilter['clear-value']) data-clear-value="{{ $psfFilter['clear-value'] }}" @endisset>
                    {{ $psfFilter['label'] }}
                    <i class="icon feather icon-x tag-close"></i>
                </a>
            </li>
        @endforeach
    </ul>
    <span class="psf-shop-count">
        @if ($products->total() > 0)
            {{ psfTranslate('shop_showing_results', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}
        @else
            {{ translate('no_product_found') }}
        @endif
    </span>
</template>

@if (count($products) > 0)
    <div class="row {{ $psfView === 'list' ? '' : 'gx-xl-4 g-3' }} psf-shop-view-{{ $psfView }}">
        @foreach ($products as $product)
            @if (!empty($product['product_id']))
                @php($product = $product->product)
            @endif
            @if (!empty($product))
                <div class="{{ $psfColumns }} {{ $psfView === 'list' ? '' : 'm-md-b15 m-b30' }}">
                    @if ($psfView === 'list')
                        @include('web-views.products.partials._psf-shop-list-card', ['product' => $product])
                    @else
                        @include('web-views.partials._psf-shop-card', ['product' => $product])
                    @endif
                </div>
            @endif
        @endforeach
    </div>

    <div class="row page mt-0">
        <div class="col-md-6">
            <p class="page-text">
                {{ psfTranslate('shop_showing_results', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}
            </p>
        </div>
        <div class="col-md-6">
            {!! $products->links('web-views.partials._psf-pagination') !!}
        </div>
    </div>
@else
    <div class="psf-shop-empty text-center py-5">
        <i class="flaticon-shopping-bag-1 psf-shop-empty-icon" aria-hidden="true"></i>
        <h5 class="title mt-3">{{ translate('no_product_found') }}</h5>
        <p class="mb-3">{{ translate('Try_another_search_or_remove_a_filter') }}</p>
        <a href="{{ route('products') }}" class="btn btn-secondary btn-sm">{{ translate('View_all_products') }}</a>
    </div>
@endif
