@extends('layouts.front-end.app')

@section('title', $web_config['meta_title'])

{{-- PSF · homepage of the new design (template index-2).
     Every block is fed by the shop or the panel and can be switched off in
     Paramètres PSF → Homepage sections. Texts are language keys (Languages). --}}

@section('content')
    <?php
        $orderSuccessIds = session('order_success_ids');
        $isNewCustomerInSession = session('isNewCustomerInSession');
        session()->forget('order_success_ids');
        session()->forget('isNewCustomerInSession');
    ?>
    @include('web-views.partials._order-success-modal', ['orderSuccessIds' => $orderSuccessIds, 'isNewCustomerInSession' => $isNewCustomerInSession])

    @php($psfHeroBanner = $bannerTypeMainBanner->first())
    @php($psfSocials = collect($web_config['social_media'] ?? []))

    {{-- Hero ------------------------------------------------------------ --}}
    @if (psfHomeSection('hero'))
        @include('web-views.partials._psf-home-hero')
    @endif

    {{-- Category tiles -------------------------------------------------- --}}
    @php($psfTiles = $categories->take(6)->values())
    @if (psfHomeSection('categories') && $psfTiles->count() > 0)
        @php($psfTileCols = [
            1 => [12],
            2 => [6, 6],
            3 => [4, 3, 5],
            4 => [5, 7, 7, 5],
            5 => [4, 3, 5, 6, 6],
            6 => [4, 3, 5, 4, 5, 3],
        ][$psfTiles->count()])
        <div class="content-inner category-section">
            <div class="container">
                <div class="row gx-xl-4 g-3">
                    @foreach ($psfTiles as $psfIndex => $psfCategory)
                        <div class="col-xl-{{ $psfTileCols[$psfIndex] }} col-lg-{{ $psfTiles->count() === 1 ? 12 : 6 }} col-md-6 col-6 wow fadeInUp"
                             data-wow-delay="{{ 0.2 + $psfIndex * 0.1 }}s">
                            <div class="category-product {{ $psfIndex < 3 ? 'left' : 'right' }} product-{{ $psfIndex + 1 }}">
                                <a href="{{ route('category-products', ['slug' => $psfCategory['slug']]) }}">
                                    <img src="{{ getStorageImages(path: $psfCategory->icon_full_url, type: 'category') }}"
                                         alt="{{ $psfCategory['name'] }}" loading="lazy">
                                    <div class="category-badge">{{ $psfCategory['name'] }}</div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <a class="icon-button" href="{{ route('categories') }}" aria-label="{{ translate('view_all_categories') }}">
                <div class="text-row word-rotate-box c-black border-white">
                    <span class="word-rotate">{{ translate('home_categories_badge') }}</span>
                    @include('web-views.partials._psf-icon', ['icon' => 'arrow-down'])
                </div>
            </a>
        </div>
    @endif

    {{-- Trending (featured products) ------------------------------------ --}}
    @php($psfTrending = $featuredProductsList->count() > 0 ? $featuredProductsList : $latestProductsList)
    @if (psfHomeSection('featured_products') && $psfTrending->count() > 0)
        <section class="content-inner-1 overflow-hidden">
            <div class="container">
                <div class="row justify-content-md-between align-items-center">
                    <div class="col-lg-6 col-md-8 col-sm-12">
                        <div class="section-head style-1 m-b30 wow fadeInUp" data-wow-delay="0.2s">
                            <div class="left-content">
                                <h2 class="title">{{ translate('home_trending_title') }}</h2>
                                <p>{{ translate('home_trending_text') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-4 col-sm-12 text-md-end">
                        <a class="btn btn-secondary m-b30" href="{{ route('products', ['data_from' => 'featured', 'page' => 1]) }}">{{ translate('view_all') }}</a>
                    </div>
                </div>
                <div class="swiper-btn-center-lr">
                    <div class="swiper swiper-four">
                        <div class="swiper-wrapper">
                            @foreach ($psfTrending as $psfIndex => $product)
                                <div class="swiper-slide">
                                    @include('web-views.partials._psf-shop-card', ['product' => $product, 'wowDelay' => (0.2 + min($psfIndex, 7) * 0.1) . 's'])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Flash deal ------------------------------------------------------ --}}
    @if (psfHomeSection('flash_deal') && $flashDeal['flashDeal'] && $flashDeal['flashDealProducts'] && count($flashDeal['flashDealProducts']) > 0)
        <section class="content-inner-1 overflow-hidden pt-0">
            <div class="container">
                <div class="row justify-content-md-between align-items-center">
                    <div class="col-lg-7 col-md-8 col-sm-12">
                        <div class="section-head style-1 m-b30 wow fadeInUp" data-wow-delay="0.2s">
                            <div class="left-content">
                                <h2 class="title">{{ $flashDeal['flashDeal']['title'] }}</h2>
                                <div class="psf-flash-countdown psf-countdown countdown-timer" data-end="{{ date('m/d/Y H:i:s', strtotime($flashDeal['flashDeal']['end_date'])) }}">
                                    <div class="clock">
                                        <div class="clock-item"><span class="days">00</span><p>{{ translate('days') }}</p></div>
                                        <div class="clock-item"><span class="hours">00</span><p>{{ translate('hours') }}</p></div>
                                        <div class="clock-item"><span class="minutes">00</span><p>{{ translate('minutes') }}</p></div>
                                        <div class="clock-item"><span class="seconds">00</span><p>{{ translate('seconds') }}</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-4 col-sm-12 text-md-end">
                        <a class="btn btn-secondary m-b30" href="{{ route('flash-deals', [$flashDeal['flashDeal']['id'] ?? 0]) }}">{{ translate('view_all') }}</a>
                    </div>
                </div>
                <div class="row g-xl-4 g-3">
                    @foreach (collect($flashDeal['flashDealProducts'])->take(4) as $psfIndex => $psfDealProduct)
                        {{-- the list holds products; older shop versions hand over the deal row instead --}}
                        @php($psfDealItem = $psfDealProduct->product ?? $psfDealProduct)
                        @if ($psfDealItem)
                            <div class="col-6 col-xl-3 col-lg-3 col-md-4">
                                @include('web-views.partials._psf-shop-card', ['product' => $psfDealItem, 'wowDelay' => (0.2 + $psfIndex * 0.1) . 's'])
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Video + category marquee ---------------------------------------- --}}
    @php($psfVideo = psfHomeVideo())
    @if (psfHomeSection('video') && ($psfVideo || $categories->count() > 0))
        <section class="video-section">
            @if ($psfVideo)
                <div class="video-wrapper bg-parallax psf-video-bg"
                     @if ($psfVideo['background']) style="background-image: url('{{ $psfVideo['background'] }}');" @endif>
                    <div class="container">
                        <div class="d-flex justify-content-center">
                            @if ($psfVideo['url'])
                                <a class="icon-button popup-youtube" href="{{ $psfVideo['url'] }}" aria-label="{{ translate('play_video') }}">
                                    <div class="text-row word-rotate-box border-white c-black">
                                        <span class="word-rotate">{{ translate('home_video_badge') }}</span>
                                        @include('web-views.partials._psf-icon', ['icon' => 'play'])
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if ($categories->count() > 0)
                <div class="dz-features-wrapper overflow-hidden">
                    <ul class="dz-features text-wrapper">
                        {{-- repeated so the strip never runs out while it scrolls --}}
                        @for ($psfRound = 0; $psfRound < max(2, (int) ceil(12 / max(1, $categories->count()))); $psfRound++)
                            @foreach ($categories as $psfCategory)
                                <li class="item"><h2 class="title">{{ $psfCategory['name'] }}</h2></li>
                                <li class="item">@include('web-views.partials._psf-icon', ['icon' => 'star-muted'])</li>
                            @endforeach
                        @endfor
                    </ul>
                </div>
            @endif
        </section>
    @endif

    {{-- Most popular, filtered by category -------------------------------- --}}
    @if (psfHomeSection('popular_products') && $bestSellProduct->count() > 0)
        @php($psfPopular = $bestSellProduct->take(8))
        {{-- "All" + at most 4 tabs: the categories with the most products in this list --}}
        @php($psfPopularCounts = $psfPopular->pluck('category_id')->filter()->countBy())
        @php($psfPopularCategories = $categories->whereIn('id', $psfPopularCounts->keys())
            ->sortByDesc(fn ($category) => $psfPopularCounts[$category['id']] ?? 0)->take(4)->values())
        <section class="content-inner">
            <div class="container">
                <div class="row justify-content-md-between align-items-start">
                    <div class="col-lg-6 col-md-12">
                        <div class="section-head style-1 m-b30 wow fadeInUp" data-wow-delay="0.2s">
                            <div class="left-content">
                                <h2 class="title">{{ translate('home_popular_title') }}</h2>
                            </div>
                        </div>
                    </div>
                    @if ($psfPopularCategories->count() > 1)
                        <div class="col-lg-6 col-md-12">
                            <div class="site-filters clearfix style-1 align-items-center wow fadeInUp ms-lg-auto" data-wow-delay="0.4s">
                                <ul class="filters" data-bs-toggle="buttons">
                                    <li class="btn active" data-filter="*">
                                        <input type="radio" aria-label="{{ translate('all') }}">
                                        <a href="javascript:void(0);">{{ translate('all') }}</a>
                                    </li>
                                    @foreach ($psfPopularCategories as $psfCategory)
                                        <li data-filter=".psf-cat-{{ $psfCategory['id'] }}" class="btn">
                                            <input type="radio" aria-label="{{ $psfCategory['name'] }}">
                                            <a href="javascript:void(0);">{{ $psfCategory['name'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="clearfix">
                    <ul id="masonry" class="row g-xl-4 g-3">
                        @foreach ($psfPopular as $psfIndex => $product)
                            <li class="card-container col-6 col-xl-3 col-lg-3 col-md-4 col-sm-6 psf-cat-{{ $product->category_id }} wow fadeInUp"
                                data-wow-delay="{{ 0.6 + $psfIndex * 0.2 }}s">
                                @include('web-views.partials._psf-shop-card', ['product' => $product])
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    {{-- Section banners (Main Section Banner) ---------------------------- --}}
    @if (psfHomeSection('section_banner') && isset($bannerTypeMainSectionBanner) && $bannerTypeMainSectionBanner)
        <div class="container m-b50 wow fadeInUp" data-wow-delay="0.2s">
            <a href="{{ $bannerTypeMainSectionBanner['url'] ?: 'javascript:void(0);' }}" class="d-block psf-section-banner"
               @if ($bannerTypeMainSectionBanner['url']) target="_blank" rel="noopener" @endif>
                <img src="{{ getStorageImages(path: $bannerTypeMainSectionBanner->photo_full_url, type: 'banner') }}"
                     alt="{{ psfRecordText($bannerTypeMainSectionBanner, 'title') ?: $web_config['company_name'] }}" class="w-100" loading="lazy">
            </a>
        </div>
    @endif

    {{-- Brands ------------------------------------------------------------ --}}
    @if (psfHomeSection('brands') && $web_config['brand_setting'] && $brands->count() > 0)
        <section class="content-inner-3 companies-section overflow-hidden">
            <div class="container">
                <div class="row justify-content-between align-items-end">
                    <div class="col-lg-8 col-md-8 col-sm-12">
                        <div class="section-head style-2 wow fadeInUp m-0" data-wow-delay="0.1s">
                            <h2 class="title text-white">{{ translate('home_brands_title') }}</h2>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 text-md-center m-b30 wow fadeInUp" data-wow-delay="0.2s">
                        <a class="icon-button d-md-inline-block d-none" href="{{ route('brands') }}" aria-label="{{ translate('all_brands') }}">
                            <div class="text-row word-rotate-box c-black border-secondary bg-secondary">
                                <span class="word-rotate">{{ translate('home_brands_badge') }}</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="tag-slider style-1 wow fadeInUp" data-wow-delay="0.2s" id="tagSlider">
                    <div class="item-wrap">
                        {{-- repeated so the loop stays full on wide screens --}}
                        @for ($psfRound = 0; $psfRound < max(2, (int) ceil(8 / $brands->count())); $psfRound++)
                            @foreach ($brands as $psfBrand)
                                @if (!empty($psfBrand['slug']))
                                    <div class="item">
                                        <a href="{{ route('products', ['brand_id' => $psfBrand['id'], 'data_from' => 'brand', 'page' => 1]) }}" class="companies-wrapper">
                                            <div class="companies-media">
                                                <img src="{{ getStorageImages(path: $psfBrand->image_full_url, type: 'brand') }}"
                                                     alt="{{ $psfBrand['name'] }}" loading="lazy">
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Latest réalisations (template "latest posts") ------------------- --}}
    @php($psfWorks = psfGalleryMenu() ? \App\Models\PsfGalleryItem::active()->ordered()->take(6)->get() : collect())
    @if (psfHomeSection('realisations') && $psfWorks->count() > 0)
        <section class="content-inner">
            <div class="container">
                <div class="section-head style-1 wow fadeInUp d-md-flex justify-content-between align-items-center" data-wow-delay="0.1s">
                    <div class="left-content">
                        <h2 class="title">{{ translate('Our_Realisations') }}</h2>
                        <p>{{ translate('home_realisations_text') }}</p>
                    </div>
                    <a class="btn btn-secondary" href="{{ route('psf.gallery.index') }}">{{ translate('view_all') }}</a>
                </div>
                <div class="row blog-shap">
                    @foreach ($psfWorks->take(4) as $psfIndex => $psfWork)
                        <div class="col-lg-6 col-md-6 col-sm-12 m-b30 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="dz-card blog-half style-6 card-{{ $psfIndex + 1 }}">
                                <div class="dz-media">
                                    <img src="{{ $psfWork->image_url }}" alt="{{ $psfWork->alt }}" loading="lazy">
                                </div>
                                <div class="dz-info">
                                    <div class="dz-meta">
                                        <ul>
                                            @if ($psfWork->completed_on)
                                                <li class="post-date">{{ $psfWork->completed_on->translatedFormat('d M Y') }}</li>
                                            @endif
                                            @if ($psfWork->text('location'))
                                                <li>{{ $psfWork->text('location') }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                    <h4 class="dz-title">
                                        <a href="{{ route('psf.gallery.index', array_filter(['category' => $psfWork->category])) }}">{{ $psfWork->text('title') }}</a>
                                    </h4>
                                    <a href="{{ route('psf.gallery.index', array_filter(['category' => $psfWork->category])) }}" class="btn btn-theme text-uppercase">
                                        {{ translate('read_more') }}<i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Photo strip (réalisations images) + social link ------------------ --}}
    @if (psfHomeSection('instagram') && $psfWorks->count() > 0)
        @php($psfFollow = $psfSocials->first(fn ($social) => in_array(strtolower($social->name), ['instagram', 'facebook', 'tiktok'], true)))
        <div class="content-inner py-0 image-wrapper">
            <div class="container-fluid px-0">
                <div class="row gx-0">
                    @foreach ($psfWorks->take(6) as $psfIndex => $psfWork)
                        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-4 col-4 wow fadeIn" data-wow-delay="{{ 0.1 + $psfIndex * 0.1 }}s">
                            <div class="insta-post dz-media dz-img-effect rotate">
                                <a href="{{ route('psf.gallery.index') }}">
                                    <img src="{{ $psfWork->image_url }}" alt="{{ $psfWork->alt }}" loading="lazy">
                                </a>
                            </div>
                        </div>
                    @endforeach
                    @if ($psfFollow)
                        <a href="{{ $psfFollow->link }}" class="instagram-link" target="_blank" rel="noopener">
                            <div class="follow-link wow bounceIn" data-wow-delay="0.1s">
                                <div class="follow-link-icon">
                                    <i class="{{ psfSocialIconClass($psfFollow->name) }}"></i>
                                </div>
                                <div class="follow-link-content">
                                    <p class="m-0">{{ translate('follow_us') }}</p>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>
@endsection

@push('script')
    @if ($orderSuccessIds)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalEl = document.getElementById('order_successfully');
                if (!modalEl) {
                    return;
                }
                const orderModal = new bootstrap.Modal(modalEl, {backdrop: 'static', keyboard: false});
                orderModal.show();
                document.querySelectorAll('.copy-order-id').forEach(function (copyBtn) {
                    copyBtn.addEventListener('click', function () {
                        const orderTextEl = this.closest('tr')?.querySelector('.order-id-text') || this.parentElement.querySelector('.order-id-text');
                        const orderText = orderTextEl?.textContent.trim();
                        if (orderText && navigator.clipboard) {
                            navigator.clipboard.writeText(orderText).then(function () {
                                toastr.success(@json(translate('order_id_copied_successfully')));
                            });
                        }
                    });
                });
                document.getElementById('modal-close-btn')?.addEventListener('click', function () {
                    setTimeout(function () { orderModal.hide(); }, 600);
                });
            });
        </script>
    @endif

    {{-- popup banner: once per visit, same rule as before --}}
    @if (Request::is('/') && \Illuminate\Support\Facades\Cookie::has('popup_banner') == false && empty($orderSuccessIds))
        <script>
            $(function () {
                if (document.getElementById('popup-modal')) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('popup-modal')).show();
                }
            });
        </script>
        @php(\Illuminate\Support\Facades\Cookie::queue('popup_banner', 'off', 1))
    @endif
@endpush
