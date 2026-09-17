@extends('layouts.front-end.app')

@section('title', $deal['title'] ?: translate('flash_Deal_Products'))

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $deal['title'] }} · {{ $web_config['company_name'] }}"/>
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $deal['title'] }} · {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/nouislider/nouislider.min.css') }}">
@endpush

@section('content')
    @php
        $psfDealUrl = route('flash-deals', ['id' => $deal['id']]);
        $psfDealEnds = date('m/d/Y H:i:s', strtotime($deal['end_date']));
    @endphp
    @include('web-views.products.partials._psf-shop-page', [
        'bannerTitle'   => $deal['title'] ?: translate('flash_Deal_Products'),
        'breadcrumbs'   => [[translate('nav_shop'), route('products')], [$deal['title'] ?: translate('flash_Deal_Products'), null]],
        'categories'    => $productCategories,
        'showDataFrom'  => false,
        'categoryRoute' => fn ($category) => route('flash-deals', ['id' => $deal['id'], ([0 => 'category_id', 1 => 'sub_category_id', 2 => 'sub_sub_category_id'][$category['position']] ?? 'category_id') => $category['id'], 'data_from' => 'category', 'page' => 1]),
        'brandRoute'    => fn ($brand) => route('flash-deals', ['id' => $deal['id'], 'brand_id' => $brand['id'], 'data_from' => 'brand', 'page' => 1]),
        'resetUrl'      => $psfDealUrl,
        'beforeList'    => view('web-views.products.partials._psf-deal-countdown', ['deal' => $deal, 'endsAt' => $psfDealEnds])->render(),
    ])
@endsection

@push('script')
    <script src="{{ psfDesignAsset('assets/vendor/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ psfDesignAsset('assets/js/psf-shop.js') }}"></script>
@endpush
