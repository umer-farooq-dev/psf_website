@extends('layouts.front-end.app')

@php
    $psfShopUrl = route('products');
    $psfBreadcrumbs = [];
    $psfBannerTitle = $pageTitleContent;

    $psfCategoryId = request('sub_sub_category_id') ?: (request('sub_category_id') ?: request('category_id'));
    if (request('data_from') === 'category' && $psfCategoryId && ($psfCategory = \App\Models\Category::find($psfCategoryId))) {
        $psfBannerTitle = $psfCategory->name;
        $psfBreadcrumbs[] = [translate('nav_shop'), $psfShopUrl];
        foreach ([$psfCategory->parent_id ? \App\Models\Category::find($psfCategory->parent_id) : null] as $psfParent) {
            if ($psfParent && $psfParent->parent_id && ($psfGrandParent = \App\Models\Category::find($psfParent->parent_id))) {
                $psfBreadcrumbs[] = [$psfGrandParent->name, route('category-products', ['slug' => $psfGrandParent->slug])];
            }
            if ($psfParent) {
                $psfBreadcrumbs[] = [$psfParent->name, route('category-products', ['slug' => $psfParent->slug])];
            }
        }
        $psfBreadcrumbs[] = [$psfCategory->name, null];
    } elseif (request('data_from') === 'brand' && request('brand_id') && ($psfBrand = \App\Models\Brand::find(request('brand_id')))) {
        $psfBannerTitle = $psfBrand->name;
        $psfBreadcrumbs[] = [translate('nav_shop'), $psfShopUrl];
        $psfBreadcrumbs[] = [$psfBrand->name, null];
    } else {
        if (url()->current() !== $psfShopUrl) {
            $psfBreadcrumbs[] = [translate('nav_shop'), $psfShopUrl];
        }
        $psfBreadcrumbs[] = [$pageTitleContent, null];
    }
@endphp

@section('title', $psfBannerTitle)

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $psfBannerTitle }} · {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $psfBannerTitle }} · {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/nouislider/nouislider.min.css') }}">
@endpush

@section('content')
    @include('web-views.products.partials._psf-shop-page', [
        'bannerTitle'   => $psfBannerTitle,
        'breadcrumbs'   => $psfBreadcrumbs,
        'showDataFrom'  => in_array((string) request('data_from'), ['', 'default', 'latest', 'best-selling', 'top-rated', 'most-favorite'], true)
                            && url()->current() === $psfShopUrl,
        'categoryRoute' => fn ($category) => route('category-products', ['slug' => $category['slug'], 'offer_type' => ($data['offer_type'] ?? '')]),
        'brandRoute'    => fn ($brand) => route('brand-products', ['slug' => $brand['slug'], 'offer_type' => ($data['offer_type'] ?? '')]),
        'resetUrl'      => url()->current() === $psfShopUrl ? $psfShopUrl : url()->current(),
    ])
@endsection

@push('script')
    <script src="{{ psfDesignAsset('assets/vendor/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ psfDesignAsset('assets/js/psf-shop.js') }}"></script>
@endpush
