{{-- PSF · quick view in the Pixio design (template "quick-view-modal").
     Same data and the same shop hooks as the classic partial, so price per
     variation, stock, cart, buy now, wishlist and restock work unchanged:
     .add-to-cart-details-form, .product-cart-option-container, .btn-number,
     .product-details-cart-qty, .discounted-unit-price, .product-add-to-cart-button… --}}
@php
    $overallRating = getOverallRating($product?->reviews);
    $psfHasPrice = psfHasPrice($product);
    $psfImages = ($product->product_type === 'physical' && !empty($product->color_image) && count($product->color_images_full_url) > 0)
        ? $product->color_images_full_url
        : $product->images_full_url;
    $psfImageUrl = fn ($photo) => isset($photo['image_name'])
        ? getStorageImages(path: $photo['image_name'], type: 'backend-product')
        : getStorageImages(path: $photo, type: 'backend-product');
    $psfColors = json_decode($product->colors) ?? [];
    $psfShopClosed = ($product->added_by == 'admin' && (checkVendorAbility(type: 'inhouse', status: 'temporary_close') || checkVendorAbility(type: 'inhouse', status: 'vacation_status')))
        || ($product->added_by == 'seller' && (checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $product->seller->shop) || checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $product->seller->shop)));
    $psfProductUrl = route('product', $product->slug);
    $psfCategory = \App\Models\Category::find($product->category_id);
@endphp

<button type="button" class="btn-close close-quick-view-modal" data-bs-dismiss="modal" aria-label="{{ translate('close') }}">
    <i class="icon feather icon-x"></i>
</button>
<div class="modal-body">
    <div class="row g-xl-4 g-3">
        <div class="col-xl-6 col-md-6">
            <div class="dz-product-detail mb-0">
                <div class="swiper-btn-center-lr">
                    <div class="swiper quick-modal-swiper2">
                        <div class="swiper-wrapper">
                            @foreach ($psfImages as $photo)
                                <div class="swiper-slide" data-color="{{ $photo['color'] ?? '' }}">
                                    <div class="dz-media DZoomImage">
                                        <a class="mfp-link" href="{{ $psfImageUrl($photo) }}" target="_blank" rel="noopener" aria-label="{{ translate('view') }}">
                                            <i class="feather icon-maximize dz-maximize top-right"></i>
                                        </a>
                                        <img src="{{ $psfImageUrl($photo) }}" alt="{{ $product->name }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if (count($psfImages) > 1)
                        <div class="swiper quick-modal-swiper thumb-swiper-lg thumb-sm swiper-vertical">
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

        <div class="col-xl-6 col-md-6">
            <div class="dz-product-detail style-2 ps-xl-3 ps-0 pt-2 mb-0 product-cart-option-container">
                <div class="dz-content">
                    <div class="dz-content-footer">
                        <div class="dz-content-start">
                            @if ($psfHasPrice && getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                <span class="badge bg-secondary mb-2">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                            @endif
                            @if (psfIsOnOrder($product))
                                <span class="badge bg-secondary mb-2">{{ translate('On_Order') }}</span>
                            @endif
                            <h4 class="title mb-1"><a href="{{ $psfProductUrl }}">{{ $product->name }}</a></h4>
                            <div class="review-num">
                                @if ($overallRating[0] != 0)
                                    <ul class="dz-rating me-2">
                                        @for ($psfStar = 1; $psfStar <= 5; $psfStar++)
                                            <li class="{{ $psfStar <= round($overallRating[0]) ? 'star-fill' : '' }}"><i class="flaticon-star-1"></i></li>
                                        @endfor
                                    </ul>
                                    <span class="text-secondary me-2">{{ $overallRating[0] }}</span>
                                    <a href="{{ $psfProductUrl }}">({{ $overallRating[1] }} {{ translate('reviews') }})</a>
                                @endif
                                <span class="text-secondary ms-2">{{ $countOrder }} {{ translate('orders') }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($product->details)
                        <p class="para-text">{{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags($product->details))), 160) }}</p>
                    @endif

                    <form class="addToCartDynamicForm add-to-cart-details-form">
                        @csrf
                        <input type="hidden" name="id" value="{{ $product->id }}">

                        <div class="meta-content m-b20 d-flex align-items-end flex-wrap gap-3">
                            <div class="me-3">
                                <span class="form-label">{{ translate('price') }}</span>
                                @if ($psfHasPrice)
                                    <span class="price">
                                        <span class="discounted-unit-price">{{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}</span>
                                        <del class="product-total-unit-price">{{ getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0 ? webCurrencyConverter(amount: $product->unit_price) : '' }}</del>
                                    </span>
                                @else
                                    <span class="price psf-price-on-request">{{ translate('Contact_us_for_the_price') }}</span>
                                @endif
                            </div>
                            <div class="btn-quantity light me-0">
                                <label class="form-label" for="qv-quantity-{{ $product->id }}">{{ translate('quantity') }}</label>
                                <div class="input-group bootstrap-touchspin">
                                    <input type="text" name="quantity" id="qv-quantity-{{ $product->id }}"
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
                        </div>

                        @if (count($psfColors) > 0 || count(json_decode($product->choice_options) ?? []) > 0)
                            <div class="product-num m-b20 flex-wrap gap-3">
                                @foreach (json_decode($product->choice_options) ?? [] as $choice)
                                    <div class="d-block">
                                        <label class="form-label">{{ translate($choice->title) }}</label>
                                        <div class="btn-group product-size psf-size-pills m-0 flex-wrap">
                                            @foreach ($choice->options as $index => $option)
                                                <input type="radio" class="btn-check" name="{{ $choice->name }}"
                                                       id="qv-{{ $product->id }}-{{ $choice->name }}-{{ $index }}"
                                                       value="{{ $option }}" @if ($index == 0) checked @endif>
                                                <label class="btn" for="qv-{{ $product->id }}-{{ $choice->name }}-{{ $index }}">{{ $option }}</label>
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
                                                    <input class="form-check-input quick-view-preview-image-by-color" type="radio" name="color"
                                                           id="qv-{{ $product->id }}-color-{{ $key }}" value="{{ $color }}"
                                                           data-key="{{ str_replace('#', '', $color) }}"
                                                           aria-label="{{ psfColorName($color) }}" @if ($key == 0) checked @endif>
                                                    <span style="background-color: {{ $color }};"></span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @php($extensionIndex = 0)
                        @if ($product['product_type'] == 'digital' && $product['digital_product_file_types'] && count($product['digital_product_file_types']) > 0 && $product['digital_product_extensions'])
                            @foreach ($product['digital_product_extensions'] as $extensionKey => $extensionGroup)
                                @if (count($extensionGroup) > 0)
                                    <div class="d-block m-b20">
                                        <label class="form-label">{{ translate($extensionKey) }}</label>
                                        <div class="btn-group product-size psf-size-pills m-0 flex-wrap">
                                            @foreach ($extensionGroup as $extension)
                                                <input type="radio" class="btn-check" name="variant_key"
                                                       id="qv-extension-{{ $extensionIndex }}"
                                                       value="{{ $extensionKey . '-' . preg_replace('/\s+/', '-', $extension) }}"
                                                    {{ $extensionIndex == 0 ? 'checked' : '' }}>
                                                <label class="btn" for="qv-extension-{{ $extensionIndex }}">{{ $extension }}</label>
                                                @php($extensionIndex++)
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product['id'] }}">
                        <input type="hidden" value="" class="product-exist-in-cart-list" name="key">

                        @if ($psfHasPrice)
                            <div class="product-details-chosen-price-section m-b20">
                                <span class="form-label d-inline">{{ translate('total_price') }} :</span>
                                <strong class="product-details-chosen-price-amount">{{ webCurrencyConverter(amount: $initialProductConfig['total_quantity_price']) }}</strong>
                            </div>
                        @endif

                        <div class="cart-btn product-add-and-buy-section flex-wrap gap-2" {!! $firstVariationQuantity <= 0 ? 'style="display: none;"' : '' !!}>
                            @if ($psfShopClosed)
                                <button class="btn btn-secondary text-uppercase" type="button" disabled>{{ translate('add_to_cart') }}</button>
                            @elseif (!$psfHasPrice)
                                <a href="{{ psfPriceRequestUrl($product) }}" target="_blank" rel="noopener" class="btn btn-secondary psf-accent-btn">
                                    <i class="fa-brands fa-whatsapp me-2"></i>{{ translate('Request_the_price') }}
                                </a>
                            @else
                                <button class="btn btn-secondary text-uppercase product-add-to-cart-button" type="button"
                                        data-form=".add-to-cart-details-form"
                                        data-update="{{ translate('update_cart') }}"
                                        data-add="{{ translate('add_to_cart') }}">
                                    {{ $initialProductConfig['first_variant_in_cart'] ? translate('update_cart') : translate('add_to_cart') }}
                                </button>
                                <button class="btn btn-outline-secondary text-uppercase product-buy-now-button" type="button"
                                        data-form=".add-to-cart-details-form"
                                        data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check()) ? 'true' : 'false' }}"
                                        data-route="{{ route('shop-cart') }}">
                                    {{ translate('buy_now') }}
                                </button>
                            @endif
                            <button type="button" class="btn btn-md btn-outline-secondary btn-icon psf-wishlist {{ $wishlist_status == 1 ? 'active' : '' }}"
                                    data-product-id="{{ $product['id'] }}" aria-label="{{ translate('add_to_wishlist') }}">
                                <i class="icon feather icon-heart dz-heart"></i>
                                <i class="icon feather icon-heart-on dz-heart-fill"></i>
                                <span class="countWishlist-{{ $product['id'] }}">{{ $countWishlist }}</span>
                            </button>
                        </div>

                        @if ($product['product_type'] == 'physical')
                            <div class="product-restock-request-section collapse mt-2" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                <button type="button" class="btn btn-outline-secondary product-restock-request-button"
                                        data-auth="{{ auth('customer')->check() }}"
                                        data-form=".addToCartDynamicForm"
                                        data-default="{{ translate('Request_Restock') }}"
                                        data-requested="{{ translate('Request_Sent') }}">
                                    {{ translate('Request_Restock') }}
                                </button>
                            </div>
                        @endif

                        @if ($psfShopClosed)
                            <div class="alert alert-danger mt-3" role="alert">
                                {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
                            </div>
                        @endif
                    </form>

                    @if ($psfHasPrice)
                        <a href="{{ psfOrderRequestUrl($product) }}" target="_blank" rel="noopener" class="btn btn-link px-0 mt-2 psf-quick-whatsapp">
                            <i class="fa-brands fa-whatsapp me-2"></i>{{ translate('Order_on_WhatsApp') }}
                        </a>
                    @endif

                    <div class="dz-info mb-0">
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
                        <div class="dz-social-icon">
                            <ul>
                                <li><a target="_blank" rel="noopener" class="text-dark" aria-label="Facebook"
                                       href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a target="_blank" rel="noopener" class="text-dark" aria-label="WhatsApp"
                                       href="https://api.whatsapp.com/send?text={{ urlencode($product->name . ' ' . $psfProductUrl) }}"><i class="fa-brands fa-whatsapp"></i></a></li>
                                <li><a target="_blank" rel="noopener" class="text-dark" aria-label="LinkedIn"
                                       href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                <li><a target="_blank" rel="noopener" class="text-dark" aria-label="Twitter"
                                       href="https://twitter.com/intent/tweet?url={{ urlencode($psfProductUrl) }}"><i class="fa-brands fa-twitter"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    "use strict";
    productQuickViewFunctionalityInitialize();

    (function () {
        const thumbsEl = document.querySelector('#quick-view .quick-modal-swiper');
        const thumbs = thumbsEl ? new Swiper(thumbsEl, {spaceBetween: 15, slidesPerView: 4, freeMode: true, watchSlidesProgress: true}) : null;
        const main = new Swiper('#quick-view .quick-modal-swiper2', {spaceBetween: 0, thumbs: thumbs ? {swiper: thumbs} : undefined});

        // picking a colour shows its picture
        const colors = @json($psfColors);
        $('#quick-view').off('change.psfColor').on('change.psfColor', 'input[name="color"]', function () {
            const index = colors.indexOf($(this).val());
            if (index !== -1) {
                main.slideTo(index);
            }
        });
    })();
</script>
