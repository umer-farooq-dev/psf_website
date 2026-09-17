{{-- PSF · Pixio header (index-2 "style-2"). Every label is a translation key and
     every link, phone, logo and menu entry comes from the panel. --}}
@php($psfCategories = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 11))
@php($psfPhones = psfContactPhones())
@php($psfLanguages = collect($web_config['language'] ?? [])->where('status', 1))
@php($psfCurrencyModel = getWebConfig(name: 'currency_model'))
@php($psfDirection = session()->get('direction') === 'rtl' ? 'rtl' : 'ltr')
@php($psfAnnouncement = getWebConfig(name: 'announcement'))

@if (isset($psfAnnouncement) && ($psfAnnouncement['status'] ?? 0) == 1)
    <div class="psf-announcement" style="--psf-announcement-bg: {{ psfHexToRgb($psfAnnouncement['color'] ?? '') ? $psfAnnouncement['color'] : 'var(--psf-primary)' }}; --psf-announcement-text: {{ psfHexToRgb($psfAnnouncement['text_color'] ?? '') ? $psfAnnouncement['text_color'] : '#ffffff' }};">
        <div class="container d-flex align-items-center justify-content-center gap-3">
            <span>{{ $psfAnnouncement['announcement'] ?? '' }}</span>
            <button type="button" class="psf-announcement-close" aria-label="{{ translate('close') }}">&times;</button>
        </div>
    </div>
@endif

<header class="site-header mo-left header style-2">

    {{-- top bar: logo, support phone, search --}}
    <div class="header-info-bar">
        <div class="container clearfix">
            <div class="logo-header logo-dark">
                <a href="{{ route('home') }}">
                    <img src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{ $web_config['company_name'] }}">
                </a>
            </div>

            @if (count($psfPhones) > 0 || $psfLanguages->count() > 1 || $psfCurrencyModel === 'multi_currency')
                <div class="extra-nav d-md-flex d-none m-l15">
                    <div class="extra-cell">
                        <ul class="navbar-nav header-right m-0 flex-row align-items-center">
                            @if (count($psfPhones) > 0)
                                <li class="nav-item info-box">
                                    <a class="nav-link" href="tel:{{ preg_replace('/[^\d+]/', '', $psfPhones[0]['number']) }}">
                                        <div class="dz-icon">
                                            <i class="fa-solid fa-headset"></i>
                                        </div>
                                        <div class="info-content">
                                            <span>{{ $psfPhones[0]['label'] ?: translate('customer_support') }}</span>
                                            <h6 class="title mb-0">{{ $psfPhones[0]['number'] }}</h6>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            @if ($psfLanguages->count() > 1)
                                <li class="nav-item dropdown psf-lang ms-3">
                                    <a class="nav-link dropdown-toggle" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ strtoupper(getDefaultLanguage()) }}
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($psfLanguages as $psfLanguage)
                                            <li class="change-language" data-action="{{ route('change-language') }}" data-language-code="{{ $psfLanguage['code'] }}">
                                                <a class="dropdown-item text-capitalize" href="javascript:void(0);">{{ $psfLanguage['name'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                            @if ($psfCurrencyModel === 'multi_currency')
                                <li class="nav-item dropdown psf-currency ms-3">
                                    <a class="nav-link dropdown-toggle" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ session('currency_code') }}
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach (\App\Models\Currency::where('status', 1)->get() as $psfCurrency)
                                            <li class="dropdown-item cursor-pointer get-currency-change-function" data-code="{{ $psfCurrency['code'] }}">
                                                {{ $psfCurrency->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif

                        </ul>
                    </div>
                </div>
            @endif

            <div class="header-search-nav">
                <form class="header-item-search search_form" action="{{ route('products') }}" method="get">
                    <div class="input-group search-input">
                        <select class="default-select" name="category_id" aria-label="{{ translate('categories') }}">
                            <option value="">{{ translate('all_categories') }}</option>
                            @foreach ($psfCategories as $psfCategory)
                                <option value="{{ $psfCategory['id'] }}" {{ (string) request('category_id') === (string) $psfCategory['id'] ? 'selected' : '' }}>
                                    {{ $psfCategory['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="search" class="form-control search-bar-input" name="name"
                               value="{{ request('name') }}" autocomplete="off" data-given-value=""
                               placeholder="{{ translate('search_for_products') }}"
                               aria-label="{{ translate('search_for_products') }}">
                        <input type="hidden" name="data_from" value="search">
                        <input type="hidden" name="global_search_input" value="1">
                        <input type="hidden" name="page" value="1">
                        <button class="btn" type="submit" aria-label="{{ translate('search') }}">
                            <i class="iconly-Light-Search text-secondary"></i>
                        </button>
                    </div>
                    <div class="card search-card">
                        <div class="card-body">
                            <div class="search-result-box"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- sticky bar: categories, menu, account, search, wishlist, cart --}}
    <div class="sticky-header main-bar-wraper navbar-expand-lg">
        <div class="main-bar clearfix">
            <div class="container clearfix d-lg-flex d-block">

                <div class="logo-header logo-dark">
                    <a href="{{ route('home') }}">
                        <img src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{ $web_config['company_name'] }}">
                    </a>
                </div>

                <button class="navbar-toggler collapsed navicon justify-content-end" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                        aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="{{ translate('menu') }}">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="header-nav w3menu navbar-collapse collapse justify-content-start" id="navbarNavDropdown">
                    <div class="logo-header">
                        <a href="{{ route('home') }}">
                            <img src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{ $web_config['company_name'] }}">
                        </a>
                    </div>

                    @if ($psfCategories->count() > 0)
                        <div class="browse-category-menu">
                            <a href="javascript:void(0);" class="category-btn">
                                <div class="category-menu me-3">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <span class="category-btn-title">{{ translate('browse_categories') }}</span>
                                <span class="toggle-arrow ms-auto">
                                    <i class="icon feather icon-chevron-down"></i>
                                </span>
                            </a>
                            <div class="category-menu-items" style="display: none;">
                                <ul class="nav navbar-nav">
                                    @foreach ($psfCategories->take(10) as $psfCategory)
                                        @if ($psfCategory->childes->count() > 0)
                                            <li class="has-mega-menu cate-drop">
                                                <a href="{{ route('category-products', ['slug' => $psfCategory['slug']]) }}">
                                                    <i class="icon feather icon-arrow-right"></i>
                                                    <span>{{ $psfCategory['name'] }}</span>
                                                    <span class="menu-icon">
                                                        <i class="icon feather icon-chevron-right"></i>
                                                    </span>
                                                </a>
                                                <div class="mega-menu">
                                                    <div class="row">
                                                        @foreach ($psfCategory->childes as $psfSub)
                                                            <div class="col-md-3 col-sm-4 col-6">
                                                                <a href="{{ route('category-products', ['slug' => $psfSub['slug']]) }}" class="menu-title">{{ $psfSub['name'] }}</a>
                                                                @if ($psfSub->childes->count() > 0)
                                                                    <ul>
                                                                        @foreach ($psfSub->childes as $psfSubSub)
                                                                            <li>
                                                                                <a href="{{ route('category-products', ['slug' => $psfSubSub['slug']]) }}">{{ $psfSubSub['name'] }}</a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ route('category-products', ['slug' => $psfCategory['slug']]) }}">
                                                    <i class="icon feather icon-arrow-right"></i>
                                                    <span>{{ $psfCategory['name'] }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                    <li>
                                        <a href="{{ route('categories') }}">
                                            <i class="icon feather icon-grid"></i>
                                            <span>{{ translate('view_all_categories') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    <ul class="nav navbar-nav">
                        <li class="{{ request()->routeIs('home') ? 'psf-current' : '' }}">
                            <a href="{{ route('home') }}"><span>{{ translate('home') }}</span></a>
                        </li>

                        {{-- shop mega menu (template "Shop"): categories, brands, shop pages,
                             the running flash deal and the menu image from Paramètres PSF --}}
                        @php($psfBrands = getWebConfig(name: 'product_brand') ? \App\Utils\BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting()->filter(fn ($b) => !empty($b['slug']))->take(7) : collect())
                        @php($psfFlash = $web_config['flash_deals'] ?? null)
                        @php($psfMenuImage = psfMenuImage())
                        <li class="has-mega-menu sub-menu-down {{ request()->routeIs('products', 'category-products', 'brand-products', 'brands', 'categories') ? 'psf-current' : '' }}">
                            <a href="{{ route('products') }}"><span>{{ translate('nav_shop') }}</span><i class="fas fa-chevron-down tabindex"></i></a>
                            <div class="mega-menu shop-menu">
                                <ul>
                                    <li class="side-left">
                                        <ul>
                                            @if ($psfCategories->count() > 0)
                                                <li>
                                                    <a href="{{ route('categories') }}" class="menu-title">{{ translate('categories') }}</a>
                                                    <ul>
                                                        @foreach ($psfCategories->take(7) as $psfCategory)
                                                            <li><a href="{{ route('category-products', ['slug' => $psfCategory['slug']]) }}">{{ $psfCategory['name'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                            @if ($psfBrands->count() > 0)
                                                <li>
                                                    <a href="{{ route('brands') }}" class="menu-title">{{ translate('brands') }}</a>
                                                    <ul>
                                                        @foreach ($psfBrands as $psfBrand)
                                                            <li><a href="{{ route('brand-products', ['slug' => $psfBrand['slug']]) }}">{{ $psfBrand['name'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                            <li>
                                                <a href="{{ route('products') }}" class="menu-title">{{ translate('shop_pages') }}</a>
                                                <ul>
                                                    <li><a href="{{ route('products') }}">{{ translate('all_products') }}</a></li>
                                                    @if (count(getFeaturedDealsProductList()) > 0)
                                                        <li><a href="{{ route('featured-deal-products') }}">{{ translate('featured_Deal') }}</a></li>
                                                    @endif
                                                    @if ($web_config['discount_product'] > 0)
                                                        <li><a href="{{ route('discounted-products') }}">{{ translate('discounted_products') }}</a></li>
                                                    @endif
                                                    @if ($web_config['clearance_sale_product_count'] > 0)
                                                        <li><a href="{{ route('clearance-sale-products') }}">{{ translate('clearance_Sale') }}</a></li>
                                                    @endif
                                                    <li><a href="{{ route('shop-cart') }}">{{ translate('cart') }}</a></li>
                                                    <li><a href="{{ route('wishlists') }}">{{ translate('wishlist') }}</a></li>
                                                    <li><a href="{{ route('track-order.index') }}">{{ translate('track_order') }}</a></li>
                                                </ul>
                                            </li>
                                            @if ($psfFlash && count($web_config['flash_deals_products'] ?? []) > 0 && !empty($psfFlash['end_date']))
                                                <li class="month-deal">
                                                    <div class="clearfix me-3">
                                                        <h3>{{ $psfFlash['title'] }}</h3>
                                                        <p class="mb-0">
                                                            <a href="{{ route('flash-deals', ['id' => $psfFlash['id']]) }}" class="text-primary fw-bold">{{ translate('shop_now') }}</a>
                                                        </p>
                                                    </div>
                                                    <div class="sale-countdown">
                                                        <div class="countdown text-center psf-countdown"
                                                             data-end="{{ \Carbon\Carbon::parse($psfFlash['end_date'])->endOfDay()->format('Y/m/d H:i:s') }}">
                                                            <div class="date"><span class="time days text-primary"></span><span class="work-time">{{ translate('days') }}</span></div>
                                                            <div class="date"><span class="time hours text-primary"></span><span class="work-time">{{ translate('hours') }}</span></div>
                                                            <div class="date"><span class="time mins text-primary"></span><span class="work-time">{{ translate('minutes') }}</span></div>
                                                            <div class="date"><span class="time secs text-primary"></span><span class="work-time">{{ translate('seconds') }}</span></div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                    @if ($psfMenuImage)
                                        <li class="side-right">
                                            <div class="adv-media">
                                                @if ($psfMenuImage['url'])
                                                    <a href="{{ $psfMenuImage['url'] }}"><img src="{{ $psfMenuImage['image'] }}" alt="{{ $web_config['company_name'] }}"></a>
                                                @else
                                                    <img src="{{ $psfMenuImage['image'] }}" alt="{{ $web_config['company_name'] }}">
                                                @endif
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>

                        @if (psfAboutMenu() && $web_config['business_pages']?->firstWhere('slug', 'about-us'))
                            <li class="{{ request()->is('business-page/about-us') ? 'psf-current' : '' }}">
                                <a href="{{ route('business-page.view', ['slug' => 'about-us']) }}"><span>{{ translate('nav_about_us') }}</span></a>
                            </li>
                        @endif

                        @if (psfGalleryMenu())
                            <li class="{{ request()->routeIs('psf.gallery.*') ? 'psf-current' : '' }}">
                                <a href="{{ route('psf.gallery.index') }}"><span>{{ translate('nav_realisations') }}</span></a>
                            </li>
                        @endif

                        @if (psfQuoteMenu())
                            <li class="{{ request()->routeIs('psf.quote.*') ? 'psf-current' : '' }}">
                                <a href="{{ route('psf.quote.index') }}"><span>{{ translate('nav_quote') }}</span></a>
                            </li>
                        @endif

                        <li class="{{ request()->routeIs('contacts') ? 'psf-current' : '' }}">
                            <a href="{{ route('contacts') }}"><span>{{ translate('contact') }}</span></a>
                        </li>

                        {{-- account links for the mobile menu (the icons bar is hidden there) --}}
                        @if (auth('customer')->check())
                            <li class="d-lg-none"><a href="{{ route('user-account') }}"><span>{{ translate('my_Profile') }}</span></a></li>
                            <li class="d-lg-none"><a href="{{ route('account-oder') }}"><span>{{ translate('my_Order') }}</span></a></li>
                            <li class="d-lg-none"><a href="{{ route('customer.auth.logout') }}"><span>{{ translate('logout') }}</span></a></li>
                        @else
                            <li class="d-lg-none"><a href="{{ route('customer.auth.login') }}"><span>{{ translate('sign_in') }}</span></a></li>
                            <li class="d-lg-none"><a href="{{ route('customer.auth.sign-up') }}"><span>{{ translate('sign_up') }}</span></a></li>
                        @endif
                    </ul>

                    @if (!empty($web_config['social_media']) && count($web_config['social_media']) > 0)
                        <div class="dz-social-icon">
                            <ul>
                                @foreach ($web_config['social_media'] as $psfSocial)
                                    <li>
                                        <a class="{{ psfSocialIconClass($psfSocial->name) }}" target="_blank" rel="noopener" href="{{ $psfSocial->link }}"
                                           aria-label="{{ $psfSocial->name }}"></a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="extra-nav">
                    <div class="extra-cell">
                        <ul class="header-right">

                            @if (auth('customer')->check())
                                <li class="nav-item login-link dropdown">
                                    <a class="nav-link dropdown-toggle" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ translate('hello') }}, {{ \Illuminate\Support\Str::limit(auth('customer')->user()->f_name, 10) }}
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('user-account') }}">{{ translate('my_Profile') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('account-oder') }}">{{ translate('my_Order') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('customer.auth.logout') }}">{{ translate('logout') }}</a></li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item login-link">
                                    <a class="nav-link" href="{{ route('customer.auth.login') }}">
                                        {{ translate('sign_in') }} / {{ translate('sign_up') }}
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item search-link">
                                <a class="nav-link" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop"
                                   aria-controls="offcanvasTop" aria-label="{{ translate('search') }}">
                                    <i class="iconly-Light-Search"></i>
                                </a>
                            </li>

                            <li class="nav-item wishlist-link">
                                <a class="nav-link" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                   aria-controls="offcanvasRight" aria-label="{{ translate('wishlist') }}">
                                    <i class="iconly-Light-Heart2"></i>
                                    <span class="badge badge-circle countWishlist">{{ session()->has('wish_list') ? count(session('wish_list')) : 0 }}</span>
                                </a>
                            </li>

                            <li class="nav-item cart-link">
                                <a href="javascript:void(0);" class="nav-link cart-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                   aria-controls="offcanvasRight" aria-label="{{ translate('cart') }}">
                                    <i class="iconly-Broken-Buy"></i>
                                    <span class="badge badge-circle psf-cart-count">{{ \App\Utils\CartManager::getCartListQuery()->count() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- side cart + wishlist panel. Kept outside the header so the header's icon
     styles do not reach it; the shop script refreshes #cart_items after every
     cart change. --}}
<div id="cart_items">
    @include('layouts.front-end.partials._cart')
</div>

{{-- offcanvas search --}}
<div class="dz-search-area dz-offcanvas offcanvas offcanvas-top" tabindex="-1" id="offcanvasTop">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ translate('close') }}">&times;</button>
    <div class="container">
        <form class="header-item-search search_form" action="{{ route('products') }}" method="get">
            <div class="input-group search-input">
                <select class="default-select" name="category_id" aria-label="{{ translate('categories') }}">
                    <option value="">{{ translate('all_categories') }}</option>
                    @foreach ($psfCategories as $psfCategory)
                        <option value="{{ $psfCategory['id'] }}">{{ $psfCategory['name'] }}</option>
                    @endforeach
                </select>
                <input type="search" class="form-control search-bar-input-mobile" name="name" autocomplete="off"
                       placeholder="{{ translate('search_for_products') }}" aria-label="{{ translate('search_for_products') }}">
                <input type="hidden" name="data_from" value="search">
                <input type="hidden" name="global_search_input" value="1">
                <input type="hidden" name="page" value="1">
                <button class="btn" type="submit" aria-label="{{ translate('search') }}">
                    <i class="iconly-Light-Search"></i>
                </button>
            </div>
            <div class="card search-card">
                <div class="card-body">
                    <div class="search-result-box"></div>
                </div>
            </div>
            @if ($psfCategories->count() > 0)
                <ul class="recent-tag">
                    <li class="pe-0"><span>{{ translate('quick_search') }} :</span></li>
                    @foreach ($psfCategories->take(6) as $psfCategory)
                        <li><a href="{{ route('category-products', ['slug' => $psfCategory['slug']]) }}">{{ $psfCategory['name'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </form>
    </div>
</div>
