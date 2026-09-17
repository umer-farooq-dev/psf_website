{{-- PSF · homepage hero (template index-2 "main-slider style-2").
     Content: the latest published Main Banner (Promotions → Banners): title,
     sub title, button text + link and image. Words written between [brackets]
     in the title are highlighted. Extra buttons: Paramètres PSF → hero buttons.
     Without a banner, the shop name and slogan are shown. --}}
@php
    $psfTitle = psfRecordText($psfHeroBanner, 'title');
    $psfTitle = $psfTitle !== '' ? $psfTitle : ($web_config['company_name'] ?? '');
    $psfStar = view('web-views.partials._psf-icon', ['icon' => 'star', 'class' => 'm-r10'])->render();
    $psfTitleHtml = preg_replace(
        '/\[(.+?)\]/u',
        '<span class="text-primary d-flex align-items-center">' . $psfStar . ' $1</span>',
        e($psfTitle)
    );
    $psfSubTitle = psfRecordText($psfHeroBanner, 'sub_title') ?: psfSlogan();

    $psfButtons = [];
    $psfButtonText = psfRecordText($psfHeroBanner, 'button_text');
    if ($psfHeroBanner && $psfButtonText !== '' && ($psfHeroBanner['url'] ?? '') !== '') {
        $psfButtons[] = ['label' => $psfButtonText, 'url' => $psfHeroBanner['url']];
    }
    if (psfHomeSection('hero_ctas')) {
        $psfButtons = array_merge($psfButtons, psfHeroCtas());
    }
    $psfButtons = array_slice($psfButtons, 0, 3);

    $psfHeroImage = $psfHeroBanner ? getStorageImages(path: $psfHeroBanner->photo_full_url, type: 'banner') : null;

    // real customers who left a review with a photo — the box is hidden otherwise
    $psfReviewers = \App\Models\Review::with('customer')->where('status', 1)->latest()->take(30)->get()
        ->pluck('customer')->filter(fn ($customer) => $customer && !empty($customer->image) && $customer->image !== 'def.png')
        ->unique('id')->take(3);
@endphp
<div class="main-slider style-2">
    <div class="main-swiper2">
        <div class="container">
            <div class="banner-content">
                <div class="row">
                    <div class="{{ $psfHeroImage ? 'col-xl-7 col-lg-7' : 'col-12' }} col-md-12 align-self-center">
                        <div class="swiper-content">
                            <div class="content-info">
                                <h1 class="offer-title mb-0">{!! $psfTitleHtml !!}</h1>
                                @if ($psfSubTitle)
                                    <p class="sub-title mb-0">{{ $psfSubTitle }}</p>
                                @endif
                            </div>
                            @if (count($psfButtons) > 0)
                                <div class="content-btn">
                                    @foreach ($psfButtons as $psfIndex => $psfButton)
                                        <a class="btn {{ $psfIndex === 0 ? 'btn-secondary' : 'btn-outline-secondary' }} me-3 mb-2"
                                           href="{{ $psfButton['url'] }}">{{ $psfButton['label'] }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @if ($psfHeroImage)
                        <div class="col-xl-5 col-lg-5 col-md-12">
                            <div class="banner-media">
                                <div class="shap"></div>
                                <div class="border-shap"></div>
                                <div class="border-shap2"></div>

                                <div class="img-preview">
                                    <img src="{{ $psfHeroImage }}" alt="{{ $psfTitle }}">
                                </div>

                                @if ($recommendedProduct)
                                    <div class="bnr-content-bx slideskew">
                                        <div class="dz-media">
                                            <a href="{{ route('product', $recommendedProduct->slug) }}">
                                                <img src="{{ getStorageImages(path: $recommendedProduct->thumbnail_full_url, type: 'product') }}"
                                                     alt="{{ $recommendedProduct->name }}">
                                            </a>
                                        </div>
                                        <div class="dz-info">
                                            <h5 class="dz-title">
                                                <a href="{{ route('product', $recommendedProduct->slug) }}">{{ \Illuminate\Support\Str::limit($recommendedProduct->name, 28) }}</a>
                                            </h5>
                                            @if (psfHasPrice($recommendedProduct))
                                                <h6 class="price text-primary">{{ getProductPriceByType(product: $recommendedProduct, type: 'discounted_unit_price', result: 'string') }}</h6>
                                                <button type="button" class="btn btn-primary meta-icon dz-carticon psf-add-cart"
                                                        data-product-id="{{ $recommendedProduct->id }}"
                                                        data-min="{{ max(1, (int) $recommendedProduct->minimum_order_qty) }}"
                                                        data-options="{{ psfProductHasOptions($recommendedProduct) ? 1 : 0 }}"
                                                        aria-label="{{ translate('add_to_cart') }}">
                                                    <i class="flaticon flaticon-basket"></i>
                                                    <i class="flaticon flaticon-basket-on dz-heart-fill"></i>
                                                </button>
                                            @else
                                                <h6 class="price text-primary fs-14">{{ translate('Contact_us_for_the_price') }}</h6>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($psfReviewers->count() >= 3)
                                    <div class="bnr-customer-bx slideskew">
                                        <i class="icon feather icon-heart-on dz-heart"></i>
                                        <ul>
                                            @foreach ($psfReviewers as $psfReviewer)
                                                <li class="customer-image">
                                                    <img src="{{ getStorageImages(path: $psfReviewer->image_full_url, type: 'avatar') }}" alt="{{ $psfReviewer->f_name }}">
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <ul class="star-list">
                                    @foreach ([1, 2, 3] as $psfStarIndex)
                                        <li class="star-{{ $psfStarIndex }}">
                                            @include('web-views.partials._psf-icon', ['icon' => 'star', 'size' => 57, 'fill' => 'var(--rgba-primary-2)'])
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if ($psfSocials->count() > 0)
            <div class="banner-social-media style-2 left">
                <ul>
                    @foreach ($psfSocials->take(4) as $psfSocial)
                        <li>
                            <a href="{{ $psfSocial->link }}" target="_blank" rel="noopener">{{ ucfirst(str_replace('-', ' ', $psfSocial->name)) }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        <a href="{{ route('contacts') }}" class="service-btn btn-dark">{{ translate('lets_talk') }}</a>
    </div>
</div>
