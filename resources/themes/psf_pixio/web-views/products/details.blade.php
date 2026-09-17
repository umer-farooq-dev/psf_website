@extends('layouts.front-end.app')

@section('title', $product['name'])

@push('css_or_js')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
    {{-- PSF: Product + Breadcrumb structured data (client brief §20) --}}
    @include('web-views.partials._psf-product-schema', ['product' => $product])
@endpush

@section('content')
    @php
        $psfHasPrice = psfHasPrice($product);
        $psfDiscount = $psfHasPrice ? getProductPriceByType(product: $product, type: 'discount', result: 'value') : 0;
        $psfOnOrder = psfIsOnOrder($product);
        $psfProductUrl = route('product', $product->slug);
        $psfImages = ($product->product_type === 'physical' && !empty($product->color_image) && count($product->color_images_full_url) > 0)
            ? $product->color_images_full_url
            : $product->images_full_url;
        $psfImageUrl = fn ($photo) => isset($photo['image_name'])
            ? getStorageImages(path: $photo['image_name'], type: 'backend-product')
            : getStorageImages(path: $photo, type: 'backend-product');
        $psfColors = json_decode((string) $product->colors) ?? [];
        $psfChoices = json_decode((string) $product->choice_options) ?? [];
        $psfCategory = $product->category_id ? \App\Models\Category::find($product->category_id) : null;
        $psfParentCategory = $psfCategory && $psfCategory->parent_id ? \App\Models\Category::find($psfCategory->parent_id) : null;
        $psfBrand = ($web_config['brand_setting'] ?? false) && $product->brand_id ? \App\Models\Brand::find($product->brand_id) : null;
        $psfTags = $product->tags()->get(['tags.id', 'tags.tag']);
        $psfAuthorType = $product->added_by == 'admin' ? 'inhouse' : ($product->added_by == 'seller' ? 'vendor' : '');
        $psfShopClosed = $psfAuthorType === 'vendor'
            ? (checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $product->seller->shop) || checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $product->seller->shop))
            : (checkVendorAbility(type: 'inhouse', status: 'temporary_close') || checkVendorAbility(type: 'inhouse', status: 'vacation_status'));
        $psfExcerpt = \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) $product->details))), 260);
        $psfHasVideo = $product->video_url != null && str_contains($product->video_url, 'youtube.com/embed/');
        $psfReliability = collect(getWebConfig('company_reliability') ?? [])->filter(fn ($item) => ($item['status'] ?? 0) == 1 && !empty($item['title']));
        $psfReliabilityIcons = ['delivery_info' => 'flaticon-fast-delivery', 'safe_payment' => 'flaticon-wallet', 'return_policy' => 'flaticon-money-back', 'authentic_product' => 'flaticon-medal'];
        $psfSocials = collect($web_config['social_media'] ?? []);
        $psfWhatsapp = $psfHasPrice ? psfWhatsappOrderData($product) : null;
    @endphp

    <div class="page-content bg-light psf-product-page">
        <div class="container py-3">
            <nav aria-label="breadcrumb" class="breadcrumb-row">
                <ul class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products') }}">{{ translate('nav_shop') }}</a></li>
                    @if ($psfParentCategory)
                        <li class="breadcrumb-item"><a href="{{ route('category-products', ['slug' => $psfParentCategory->slug]) }}">{{ $psfParentCategory->name }}</a></li>
                    @endif
                    @if ($psfCategory)
                        <li class="breadcrumb-item"><a href="{{ route('category-products', ['slug' => $psfCategory->slug]) }}">{{ $psfCategory->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item" aria-current="page">{{ $product->name }}</li>
                </ul>
            </nav>
        </div>

        <section class="content-inner py-0">
            <div class="container">
                <div class="row">
                    {{-- gallery --}}
                    <div class="col-xl-4 col-md-5">
                        <div class="dz-product-detail sticky-top psf-product-gallery">
                            <div class="swiper-btn-center-lr">
                                <div class="swiper product-gallery-swiper2 rounded">
                                    <div class="swiper-wrapper" id="lightgallery2">
                                        @foreach ($psfImages as $photo)
                                            <div class="swiper-slide" data-color="{{ $photo['color'] ?? '' }}">
                                                <div class="dz-media DZoomImage">
                                                    <a class="mfp-link lg-item" href="{{ $psfImageUrl($photo) }}" data-src="{{ $psfImageUrl($photo) }}" aria-label="{{ translate('view') }}">
                                                        <i class="feather icon-maximize dz-maximize top-left"></i>
                                                    </a>
                                                    <img src="{{ $psfImageUrl($photo) }}" alt="{{ $product->name }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if (count($psfImages) > 1)
                                    <div class="swiper product-gallery-swiper thumb-swiper-lg">
                                        <div class="swiper-wrapper">
                                            @foreach ($psfImages as $photo)
                                                <div class="swiper-slide">
                                                    <img src="{{ $psfImageUrl($photo) }}" alt="{{ $product->name }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- details + cart box: one form, the shop scripts look everything up inside it --}}
                    <div class="col-xl-8 col-md-7 product-cart-option-container">
                        <form class="addToCartDynamicForm add-to-cart-details-form row">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">

                            <div class="col-xl-7">
                                <div class="dz-product-detail style-2 p-t20 ps-0">
                                    <div class="dz-content">
                                        <div class="dz-content-footer">
                                            <div class="dz-content-start">
                                                @if ($psfDiscount > 0)
                                                    <span class="badge bg-secondary mb-2">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                                                @endif
                                                @if ($psfOnOrder)
                                                    <span class="badge bg-secondary mb-2">{{ translate('On_Order') }}</span>
                                                @endif
                                                <h1 class="title mb-1 psf-product-title">{{ $product->name }}</h1>
                                                <div class="review-num">
                                                    @if ($overallRating[0] != 0)
                                                        <ul class="dz-rating me-2">
                                                            @for ($psfStar = 1; $psfStar <= 5; $psfStar++)
                                                                <li class="{{ $psfStar <= round($overallRating[0]) ? 'star-fill' : '' }}"><i class="flaticon-star-1"></i></li>
                                                            @endfor
                                                        </ul>
                                                        <span class="text-secondary me-2">{{ $overallRating[0] }}</span>
                                                        <a href="#psf-reviews" class="psf-open-reviews me-2">({{ $overallRating[1] }} {{ translate('reviews') }})</a>
                                                    @endif
                                                    <span class="text-secondary me-2">{{ $countOrder }} {{ translate('orders') }}</span>
                                                    <span class="text-secondary"><span class="countWishlist-{{ $product->id }}">{{ $countWishlist }}</span> {{ translate('wish_listed') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($psfExcerpt !== '')
                                            <p class="para-text">{{ $psfExcerpt }}</p>
                                        @endif

                                        @if ($product['product_type'] == 'digital' && (count($productPublishingHouseInfo['data']) > 0 || count($productAuthorsInfo['data']) > 0))
                                            <div class="dz-info mb-3">
                                                @if (count($productPublishingHouseInfo['data']) > 0)
                                                    <ul>
                                                        <li><strong>{{ translate('Publishing_House') }}:</strong></li>
                                                        @foreach ($productPublishingHouseInfo['data'] as $publishingHouse)
                                                            <li><a href="{{ route('products', ['publishing_house_id' => $publishingHouse['id'], 'product_type' => 'digital', 'page' => 1]) }}">{{ $publishingHouse['name'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                                @if (count($productAuthorsInfo['data']) > 0)
                                                    <ul>
                                                        <li><strong>{{ translate('Author') }}:</strong></li>
                                                        @foreach ($productAuthorsInfo['data'] as $productAuthor)
                                                            <li><a href="{{ route('products', ['author_id' => $productAuthor['id'], 'product_type' => 'digital', 'page' => 1]) }}">{{ $productAuthor['name'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="product-num flex-wrap gap-4 m-b20">
                                            <div class="btn-quantity light me-0">
                                                <label class="form-label" for="product-quantity">{{ translate('quantity') }}</label>
                                                <div class="input-group bootstrap-touchspin">
                                                    <input type="text" name="quantity" id="product-quantity"
                                                           class="form-control input-number product-details-cart-qty"
                                                           value="{{ $initialProductConfig['quantity'] ?? 1 }}"
                                                           data-producttype="{{ $product->product_type }}"
                                                           min="{{ $product->minimum_order_qty ?? 1 }}"
                                                           max="{{ $product['product_type'] == 'physical' ? $product->current_stock : 100 }}">
                                                    <span class="input-group-btn-vertical">
                                                        <button class="btn btn-number" type="button" data-type="plus" data-field="quantity"
                                                                data-producttype="{{ $product->product_type }}" aria-label="{{ translate('increase') }}">
                                                            <i class="fa-solid fa-plus"></i>
                                                        </button>
                                                        <button class="btn btn-number" type="button" data-type="minus" data-field="quantity"
                                                                disabled="disabled" aria-label="{{ translate('decrease') }}">
                                                            <i class="fa-solid fa-minus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>

                                            @foreach ($psfChoices as $choice)
                                                <div class="d-block">
                                                    <label class="form-label">{{ translate($choice->title) }}</label>
                                                    <div class="btn-group product-size psf-size-pills m-0 flex-wrap">
                                                        @foreach ($choice->options as $index => $option)
                                                            <input type="radio" class="btn-check" name="{{ $choice->name }}"
                                                                   id="pd-{{ $choice->name }}-{{ $index }}" value="{{ $option }}"
                                                                   data-psf-label="{{ translate($choice->title) }}" @if ($index == 0) checked @endif>
                                                            <label class="btn" for="pd-{{ $choice->name }}-{{ $index }}">{{ $option }}</label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if (count($psfColors) > 0)
                                                <div class="meta-content">
                                                    <label class="form-label">{{ translate('color') }}</label>
                                                    <div class="d-flex align-items-center color-filter">
                                                        @foreach ($psfColors as $key => $color)
                                                            <div class="form-check" title="{{ psfColorName($color) }}">
                                                                <input class="form-check-input" type="radio" name="color"
                                                                       id="pd-color-{{ $key }}" value="{{ $color }}"
                                                                       data-psf-label="{{ translate('color') }}" data-psf-value="{{ psfColorName($color) }}"
                                                                       aria-label="{{ psfColorName($color) }}" @if ($key == 0) checked @endif>
                                                                <span style="background-color: {{ $color }};"></span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        @php($extensionIndex = 0)
                                        @if ($product['product_type'] == 'digital' && $product['digital_product_file_types'] && count($product['digital_product_file_types']) > 0 && $product['digital_product_extensions'])
                                            @foreach ($product['digital_product_extensions'] as $extensionKey => $extensionGroup)
                                                @if (count($extensionGroup) > 0)
                                                    <div class="d-block m-b20">
                                                        <label class="form-label">{{ translate($extensionKey) }}</label>
                                                        <div class="btn-group product-size psf-size-pills m-0 flex-wrap">
                                                            @foreach ($extensionGroup as $extension)
                                                                <input type="radio" class="btn-check" name="variant_key"
                                                                       id="pd-extension-{{ $extensionIndex }}"
                                                                       value="{{ $extensionKey . '-' . preg_replace('/\s+/', '-', $extension) }}"
                                                                       data-psf-label="{{ translate($extensionKey) }}"
                                                                    {{ $extensionIndex == 0 ? 'checked' : '' }}>
                                                                <label class="btn" for="pd-extension-{{ $extensionIndex }}">{{ $extension }}</label>
                                                                @php($extensionIndex++)
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product['id'] }}">
                                        <input type="hidden" value="" class="product-exist-in-cart-list" name="key">

                                        <div class="dz-info">
                                            @if ($product->code)
                                                <ul>
                                                    <li><strong>{{ translate('SKU') }}:</strong></li>
                                                    <li>{{ $product->code }}</li>
                                                </ul>
                                            @endif
                                            @if ($psfCategory)
                                                <ul>
                                                    <li><strong>{{ translate('category') }}:</strong></li>
                                                    <li><a href="{{ route('category-products', ['slug' => $psfCategory->slug]) }}">{{ $psfCategory->name }}</a></li>
                                                </ul>
                                            @endif
                                            @if ($psfBrand)
                                                <ul>
                                                    <li><strong>{{ translate('brand') }}:</strong></li>
                                                    <li><a href="{{ route('brand-products', ['slug' => $psfBrand->slug]) }}">{{ $psfBrand->name }}</a></li>
                                                </ul>
                                            @endif
                                            @if ($psfTags->count() > 0)
                                                <ul>
                                                    <li><strong>{{ translate('tags') }}:</strong></li>
                                                    @foreach ($psfTags as $tag)
                                                        <li><a href="{{ route('products', ['tag_id' => $tag->id]) }}">{{ $tag->tag }}{{ $loop->last ? '' : ',' }}</a></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <ul class="social-icon">
                                                <li><strong>{{ translate('share') }}:</strong></li>
                                                <li><a target="_blank" rel="noopener" aria-label="Facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-facebook-f"></i></a></li>
                                                <li><a target="_blank" rel="noopener" aria-label="WhatsApp" href="https://api.whatsapp.com/send?text={{ urlencode($product->name . ' ' . $psfProductUrl) }}"><i class="fa-brands fa-whatsapp"></i></a></li>
                                                <li><a target="_blank" rel="noopener" aria-label="LinkedIn" href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                                <li><a target="_blank" rel="noopener" aria-label="Twitter" href="https://twitter.com/intent/tweet?url={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-twitter"></i></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    @if ($psfSocials->count() > 0)
                                        <div class="banner-social-media">
                                            <ul>
                                                @foreach ($psfSocials->take(3) as $psfSocial)
                                                    <li><a href="{{ $psfSocial->link }}" target="_blank" rel="noopener">{{ ucfirst(str_replace('-', ' ', $psfSocial->name)) }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="cart-detail">
                                    <div class="psf-detail-price m-b20">
                                        <span class="form-label d-block mb-1">{{ translate('price') }}</span>
                                        @if ($psfHasPrice)
                                            <span class="price">
                                                <span class="discounted-unit-price">{{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}</span>
                                                <del class="product-total-unit-price">{{ $psfDiscount > 0 ? webCurrencyConverter(amount: $product->unit_price) : '' }}</del>
                                            </span>
                                        @else
                                            <span class="price psf-price-on-request">{{ translate('Contact_us_for_the_price') }}</span>
                                        @endif
                                    </div>

                                    @foreach ($psfReliability as $psfReliable)
                                        <div class="icon-bx-wraper style-4 {{ $loop->last ? 'm-b30' : 'm-b15' }}">
                                            <div class="icon-bx">
                                                @if (!empty($psfReliable['image']))
                                                    <img src="{{ getStorageImages(path: imagePathProcessing(imageData: $psfReliable['image'], path: 'company-reliability'), type: 'source', source: theme_asset(path: 'public/assets/front-end/img/' . $psfReliable['item'] . '.png')) }}" alt="">
                                                @else
                                                    <i class="flaticon {{ $psfReliabilityIcons[$psfReliable['item']] ?? 'flaticon-check-circle' }}"></i>
                                                @endif
                                            </div>
                                            <div class="icon-content">
                                                <h6 class="dz-title mb-0">{{ translate($psfReliable['title']) }}</h6>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($psfHasPrice && $psfDiscount > 0)
                                        <div class="save-text">
                                            <i class="icon feather icon-check-circle"></i>
                                            <span class="m-l10">{{ psfTranslate('You_save_amount_per_unit', ['amount' => getProductPriceByType(product: $product, type: 'discount', result: 'string')]) }}</span>
                                        </div>
                                    @endif

                                    @if ($psfHasPrice)
                                        <table class="product-details-chosen-price-section">
                                            <tbody>
                                            <tr class="total">
                                                <td><h6 class="mb-0">{{ translate('total_price') }}</h6></td>
                                                <td class="price product-details-chosen-price-amount">{{ webCurrencyConverter(amount: $initialProductConfig['total_quantity_price']) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    @endif

                                    <button type="button" class="btn btn-outline-secondary btn-icon m-b20 w-100 psf-wishlist psf-detail-wishlist {{ $wishlistStatus == 1 ? 'active' : '' }}"
                                            data-product-id="{{ $product['id'] }}">
                                        <i class="icon feather icon-heart dz-heart"></i>
                                        <i class="icon feather icon-heart-on dz-heart-fill"></i>
                                        {{ translate('add_to_wishlist') }}
                                    </button>

                                    @if ($psfShopClosed)
                                        <button class="btn btn-secondary w-100 text-uppercase" type="button" disabled>{{ translate('add_to_cart') }}</button>
                                        <div class="alert alert-danger mt-3 mb-0" role="alert">
                                            {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
                                        </div>
                                    @elseif (!$psfHasPrice)
                                        <a href="{{ psfPriceRequestUrl($product) }}" target="_blank" rel="noopener" class="btn btn-secondary w-100 psf-accent-btn">
                                            <i class="fa-brands fa-whatsapp me-2"></i>{{ translate('Request_the_price') }}
                                        </a>
                                    @else
                                        <div class="product-add-and-buy-section flex-column gap-2 {{ $firstVariationQuantity <= 0 ? '' : 'd-flex' }}"
                                             {!! $firstVariationQuantity <= 0 ? 'style="display: none;"' : '' !!}>
                                            <button class="btn btn-secondary w-100 text-uppercase product-add-to-cart-button" type="button"
                                                    data-form=".add-to-cart-details-form"
                                                    data-update="{{ translate('update_cart') }}"
                                                    data-add="{{ translate('add_to_cart') }}">
                                                {{ $initialProductConfig['first_variant_in_cart'] ? translate('update_cart') : translate('add_to_cart') }}
                                            </button>
                                            <button class="btn btn-outline-secondary w-100 text-uppercase product-buy-now-button" type="button"
                                                    data-form=".add-to-cart-details-form"
                                                    data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check()) ? 'true' : 'false' }}"
                                                    data-route="{{ route('shop-cart') }}">
                                                {{ translate('buy_now') }}
                                            </button>
                                            <a href="{{ psfOrderRequestUrl($product) }}" target="_blank" rel="noopener"
                                               data-psf-wa-number="{{ $psfWhatsapp['number'] }}"
                                               data-psf-wa-template="{{ $psfWhatsapp['template'] }}"
                                               class="btn w-100 psf-whatsapp-btn psf-order-whatsapp-btn">
                                                <i class="fa-brands fa-whatsapp me-2"></i>{{ translate('Order_on_WhatsApp') }}
                                            </a>
                                        </div>
                                    @endif

                                    @if ($product['product_type'] == 'physical' && $psfHasPrice && !$psfShopClosed)
                                        <div class="product-restock-request-section collapse" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                            <button type="button" class="btn btn-outline-secondary w-100 product-restock-request-button"
                                                    data-auth="{{ auth('customer')->check() }}"
                                                    data-form=".addToCartDynamicForm"
                                                    data-default="{{ translate('Request_Restock') }}"
                                                    data-requested="{{ translate('Request_Sent') }}">
                                                {{ translate('Request_Restock') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- description / reviews --}}
        <section class="content-inner-3 pb-0" id="psf-reviews-anchor">
            <div class="container">
                <div class="product-description">
                    <div class="dz-tabs">
                        <ul class="nav nav-tabs center" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="psf-description-tab" data-bs-toggle="tab" data-bs-target="#psf-description" type="button" role="tab" aria-controls="psf-description" aria-selected="true">{{ translate('description') }}</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="psf-reviews-tab" data-bs-toggle="tab" data-bs-target="#psf-reviews" type="button" role="tab" aria-controls="psf-reviews" aria-selected="false">{{ translate('reviews') }} ({{ $overallRating[1] }})</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="psf-description" role="tabpanel" aria-labelledby="psf-description-tab" tabindex="0">
                                <div class="detail-bx">
                                    @if ($psfHasVideo)
                                        <div class="ratio ratio-16x9 m-b30 psf-product-video">
                                            <iframe src="{{ $product->video_url }}" title="{{ $product->name }}" loading="lazy"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                        </div>
                                    @endif
                                    @if ($product['details'])
                                        <div class="para-text rich-editor-html-content psf-product-details-text">{!! $product['details'] !!}</div>
                                    @elseif (!$psfHasVideo)
                                        <p class="para-text text-center">{{ translate('product_details_not_found') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="psf-reviews" role="tabpanel" aria-labelledby="psf-reviews-tab" tabindex="0">
                                <div class="clear" id="comment-list">
                                    <div class="post-comments comments-area style-1 clearfix">
                                        @if ($overallRating[1] > 0)
                                            <div class="row align-items-center m-b30 psf-rating-summary">
                                                <div class="col-md-4 text-center">
                                                    <div class="psf-rating-average">{{ $overallRating[0] }}</div>
                                                    <ul class="dz-rating justify-content-center">
                                                        @for ($psfStar = 1; $psfStar <= 5; $psfStar++)
                                                            <li class="{{ $psfStar <= round($overallRating[0]) ? 'star-fill' : '' }}"><i class="flaticon-star-1"></i></li>
                                                        @endfor
                                                    </ul>
                                                    <p class="mb-0">{{ $overallRating[1] }} {{ translate('ratings') }}</p>
                                                </div>
                                                <div class="col-md-8">
                                                    @foreach (['excellent', 'good', 'average', 'below_Average', 'poor'] as $psfLevel => $psfLevelLabel)
                                                        <div class="d-flex align-items-center gap-3 mb-2">
                                                            <span class="psf-rating-label">{{ translate($psfLevelLabel) }}</span>
                                                            <div class="progress flex-grow-1 psf-rating-bar">
                                                                <div class="progress-bar" role="progressbar"
                                                                     style="width: {{ $overallRating[1] > 0 ? round(($rating[$psfLevel] ?? 0) / $overallRating[1] * 100) : 0 }}%;"
                                                                     aria-valuenow="{{ $rating[$psfLevel] ?? 0 }}" aria-valuemin="0" aria-valuemax="{{ $overallRating[1] }}"></div>
                                                            </div>
                                                            <span class="psf-rating-count">{{ $rating[$psfLevel] ?? 0 }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if (count($product->reviews) == 0 && $productReviews->total() == 0)
                                            <p class="dz-title-text text-center py-4">{{ translate('No_review_given_yet') }}</p>
                                        @else
                                            <ol class="comment-list" id="product-review-list">
                                                @include('web-views.partials._product-reviews')
                                            </ol>
                                            @if (count($product->reviews) > 2)
                                                <div class="text-center">
                                                    <button type="button" class="btn btn-outline-secondary view_more_button">{{ translate('view_more') }}</button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- related products --}}
        @if (count($relatedProducts) > 0)
            <section class="content-inner-1 overflow-hidden">
                <div class="container">
                    <div class="section-head style-2 d-md-flex justify-content-between align-items-center">
                        <div class="left-content">
                            <h2 class="title mb-0">{{ translate('similar_products') }}</h2>
                        </div>
                        @if ($psfCategory)
                            <a href="{{ route('category-products', ['slug' => $psfCategory->slug]) }}" class="text-secondary font-14 d-flex align-items-center gap-1">
                                {{ translate('view_all') }}
                                <i class="icon feather icon-chevron-right font-18"></i>
                            </a>
                        @endif
                    </div>
                    <div class="swiper-btn-center-lr">
                        <div class="swiper swiper-four">
                            <div class="swiper-wrapper">
                                @foreach ($relatedProducts as $relatedProduct)
                                    <div class="swiper-slide">
                                        @include('web-views.partials._psf-shop-card', ['product' => $relatedProduct])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <div class="modal fade" id="show-modal-view" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="{{ translate('close') }}"></button>
                    <img class="img-fluid" id="attachment-view" src="" alt="">
                </div>
            </div>
        </div>
    </div>

    @if ($product?->preview_file_full_url['path'])
        @include('web-views.partials._product-preview-modal', ['previewFileInfo' => $previewFileInfo])
    @endif

    @if (getWebConfig(name: 'business_mode') == 'multi')
        @include('layouts.front-end.partials.modal._chatting', ['seller' => $product->seller, 'user_type' => $product->added_by])
    @endif

    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $product['id'] }}"></span>
@endsection

@push('script')
    <script>
        "use strict";
        // quantity buttons, price per variation and the input listeners (shop script)
        productQuickViewFunctionalityInitialize();

        (function ($) {
            // picking a colour shows its picture
            const colors = @json($psfColors);
            $(document).on('change', '.add-to-cart-details-form input[name="color"]', function () {
                const index = colors.indexOf($(this).val());
                const gallery = document.querySelector('.product-gallery-swiper2');
                if (index !== -1 && gallery && gallery.swiper) {
                    gallery.swiper.slideTo(index);
                }
            });

            // "(n reviews)" opens the reviews tab
            $(document).on('click', '.psf-open-reviews', function (e) {
                e.preventDefault();
                const tab = document.getElementById('psf-reviews-tab');
                if (tab) {
                    bootstrap.Tab.getOrCreateInstance(tab).show();
                    tab.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            });
            if (window.location.hash === '#psf-reviews') {
                $('.psf-open-reviews').first().trigger('click');
            }

            // more reviews (same endpoint as the classic page)
            let reviewPage = 1;
            $(document).on('click', '.view_more_button', function () {
                const $button = $(this);
                $.post({
                    url: $('#route-review-list-product').data('url'),
                    data: {_token: $('meta[name="_token"]').attr('content'), product_id: $('#products-details-page-data').data('id'), offset: reviewPage},
                    success: function (data) {
                        $('#product-review-list').append(data.productReview);
                        if (data.checkReviews == 0) {
                            $button.addClass('d-none');
                        }
                    }
                });
                reviewPage++;
            });
            $(document).on('click', '.show-instant-image', function () {
                showInstantImage($(this).data('link'));
            });

            /* PSF (client brief §8): the WhatsApp order message carries the
               quantity and the options picked, at click time. The wording comes
               from Paramètres PSF. */
            $(document).on('click', '.psf-order-whatsapp-btn', function () {
                const $btn = $(this);
                const number = $btn.data('psf-wa-number');
                const template = $btn.data('psf-wa-template');
                if (!number || !template) {
                    return;
                }
                const $form = $('.add-to-cart-details-form');
                const quantity = $.trim($form.find('input[name="quantity"]').val() || '') || '1';
                const parts = [];
                $form.find('input[type="radio"]:checked').each(function () {
                    const label = $.trim($(this).data('psf-label') || '');
                    const value = $.trim($(this).data('psf-value') || $(this).val() || '');
                    if (value !== '') {
                        parts.push(label !== '' ? label + ' : ' + value : value);
                    }
                });
                let message = String(template)
                    .split('{quantity}').join(quantity)
                    .split('{variation}').join(parts.join(' · '));
                message = message.split('\n').filter(function (line) {
                    return $.trim(line) !== '';
                }).join('\n');
                $btn.attr('href', 'https://wa.me/' + number + '?text=' + encodeURIComponent(message));
            });
        })(jQuery);
    </script>
@endpush
