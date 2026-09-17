{{-- PSF · Pixio header cart: the cart icon and the side panel (cart + wishlist).
     Whole partial is re-rendered by the shop script after every cart change
     (#cart_items ← cart.nav-cart), so the panel always shows the real cart.
     Quantity buttons keep the shop script's hooks (.action-update-cart-quantity,
     .cartQuantity{id}, data-*), which call the real cart routes. --}}
@php($psfCart = \App\Utils\CartManager::getCartListQuery())
@php($psfCartTotal = \App\Utils\CartManager::getCartListTotalAppliedDiscount($psfCart))
@php($psfCanCheckout = $web_config['guest_checkout_status'] || auth('customer')->check())
@php($psfWishlist = auth('customer')->check()
    ? \App\Models\Wishlist::where('customer_id', auth('customer')->id())->with('wishlistProduct')->latest()->take(10)->get()->filter(fn ($w) => $w->wishlistProduct)
    : collect())

{{-- read by psf-pixio.js to update the header badge after each refresh --}}
<span class="d-none" data-psf-cart-count="{{ $psfCart->count() }}"></span>

<div class="offcanvas dz-offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ translate('close') }}">&times;</button>
    <div class="offcanvas-body">
        <div class="product-description">
            <div class="dz-tabs">
                <ul class="nav nav-tabs center" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="shopping-cart" data-bs-toggle="tab" data-bs-target="#shopping-cart-pane"
                                type="button" role="tab" aria-controls="shopping-cart-pane" aria-selected="true">
                            {{ translate('shopping_cart') }}
                            <span class="badge badge-light">{{ $psfCart->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="wishlist" data-bs-toggle="tab" data-bs-target="#wishlist-pane"
                                type="button" role="tab" aria-controls="wishlist-pane" aria-selected="false">
                            {{ translate('wishlist') }}
                            <span class="badge badge-light countWishlist">{{ auth('customer')->check() ? $psfWishlist->count() : 0 }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4">
                    {{-- cart --}}
                    <div class="tab-pane fade show active" id="shopping-cart-pane" role="tabpanel" aria-labelledby="shopping-cart" tabindex="0">
                        <div class="shop-sidebar-cart">
                            @if ($psfCart->count() > 0)
                                @php($psfSubTotal = 0)
                                <ul class="sidebar-cart-list">
                                    @foreach ($psfCart as $psfItem)
                                        @php($psfProduct = \App\Models\Product::where('id', $psfItem['product_id'])->first())
                                        @php($psfStock = $psfProduct?->current_stock ?? 0)
                                        @if (!empty($psfProduct?->variation))
                                            @foreach (json_decode($psfProduct->variation, true) ?? [] as $psfVariant)
                                                @if (($psfVariant['type'] ?? null) == $psfItem->variant)
                                                    @php($psfStock = $psfVariant['qty'] ?? $psfStock)
                                                @endif
                                            @endforeach
                                        @endif
                                        @php($psfMin = $psfProduct->minimum_order_qty ?? 1)
                                        @php($psfLine = ($psfItem['price'] - $psfItem['discount']) * $psfItem['quantity'])
                                        @php($psfSubTotal += $psfLine)
                                        @php($psfAvailable = $psfProduct && $psfProduct->status == 1)
                                        <li>
                                            <div class="cart-widget {{ $psfAvailable ? '' : 'psf-unavailable' }}">
                                                <div class="dz-media me-3">
                                                    <img src="{{ getStorageImages(path: $psfProduct?->thumbnail_full_url, type: 'backend-product') }}"
                                                         alt="{{ $psfItem['name'] }}">
                                                </div>
                                                <div class="cart-content">
                                                    <h6 class="title">
                                                        <a href="{{ route('product', $psfItem['slug']) }}">{{ $psfItem['name'] }}</a>
                                                    </h6>
                                                    @if (!empty($psfItem['variant']))
                                                        <span class="psf-cart-variant">{{ $psfItem['variant'] }}</span>
                                                    @endif
                                                    <div class="d-flex align-items-center">
                                                        @if ($psfAvailable)
                                                            <div class="btn-quantity light quantity-sm me-3 psf-quantity">
                                                                <button type="button" class="psf-qty-btn action-update-cart-quantity"
                                                                        data-cart-id="{{ $psfItem['id'] }}" data-product-id="{{ $psfItem['product_id'] }}"
                                                                        data-action="-1" data-event="minus" aria-label="{{ translate('decrease') }}">
                                                                    <i class="fa-solid fa-minus"></i>
                                                                </button>
                                                                <input type="text" readonly
                                                                       class="cartQuantity{{ $psfItem['id'] }}"
                                                                       value="{{ $psfItem['quantity'] }}"
                                                                       data-current-stock="{{ $psfStock }}"
                                                                       data-min="{{ $psfMin }}"
                                                                       aria-label="{{ translate('quantity') }}">
                                                                <button type="button" class="psf-qty-btn action-update-cart-quantity"
                                                                        data-cart-id="{{ $psfItem['id'] }}" data-product-id="{{ $psfItem['product_id'] }}"
                                                                        data-action="1" data-event="" aria-label="{{ translate('increase') }}">
                                                                    <i class="fa-solid fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="badge bg-secondary me-3">{{ translate('N/A') }}</span>
                                                        @endif
                                                        <h6 class="dz-price mb-0 discount_price_of_{{ $psfItem['id'] }}">
                                                            {{ webCurrencyConverter(amount: $psfLine) }}
                                                        </h6>
                                                    </div>
                                                </div>
                                                {{-- removes the whole line through the shop's own remove route,
                                                     whatever the quantity (handled in psf-pixio.js) --}}
                                                <button type="button" class="dz-close psf-cart-remove"
                                                        data-cart-id="{{ $psfItem['id'] }}" aria-label="{{ translate('remove') }}">
                                                    <i class="ti-close"></i>
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="cart-total">
                                    <h5 class="mb-0">{{ translate('subtotal') }} :</h5>
                                    <h5 class="mb-0 cart_total_amount">{{ webCurrencyConverter(amount: $psfSubTotal) }}</h5>
                                </div>

                                <div class="mt-auto">
                                    @php($psfFreeDelivery = \App\Utils\OrderManager::getFreeDeliveryOrderAmountArray($psfCart[0]->cart_group_id))
                                    @if ($psfFreeDelivery['status'] && (session()->missing('coupon_type') || session('coupon_type') != 'free_delivery'))
                                        <div class="shipping-time">
                                            <div class="dz-icon">
                                                <i class="flaticon flaticon-ship"></i>
                                            </div>
                                            <div class="shipping-content">
                                                <h6 class="title pe-4">
                                                    @if ($psfFreeDelivery['amount_need'] <= 0)
                                                        {{ translate('you_Get_Free_Delivery_Bonus') }}
                                                    @else
                                                        {{ webCurrencyConverter(amount: $psfFreeDelivery['amount_need']) }}
                                                        {{ translate('add_more_for_free_delivery') }}
                                                    @endif
                                                </h6>
                                                <div class="progress">
                                                    <div class="progress-bar progress-animated border-0" role="progressbar"
                                                         style="width: {{ $psfFreeDelivery['percentage'] }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <a href="{{ $psfCanCheckout ? route('checkout-details') : route('customer.auth.login') }}"
                                       class="btn btn-outline-secondary btn-block m-b20">{{ translate('checkout') }}</a>
                                    <a href="{{ route('shop-cart') }}" class="btn btn-secondary btn-block">{{ translate('view_cart') }}</a>
                                </div>
                            @else
                                <div class="psf-empty text-center">
                                    <i class="iconly-Broken-Buy"></i>
                                    <p class="mb-3">{{ translate('your_cart_is_empty,_and_it_looks_like_you_haven’t_added_anything_yet.') }}</p>
                                    <a href="{{ route('products') }}" class="btn btn-secondary btn-block">{{ translate('continue_shopping') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- wishlist --}}
                    <div class="tab-pane fade" id="wishlist-pane" role="tabpanel" aria-labelledby="wishlist" tabindex="0">
                        <div class="shop-sidebar-cart">
                            @if (!auth('customer')->check())
                                <div class="psf-empty text-center">
                                    <i class="iconly-Light-Heart2"></i>
                                    <p class="mb-3">{{ translate('You_need_to_Sign_in_to_view_this_feature') }}</p>
                                    <a href="{{ route('customer.auth.login') }}" class="btn btn-secondary btn-block">{{ translate('sign_in') }}</a>
                                </div>
                            @elseif ($psfWishlist->count() === 0)
                                <div class="psf-empty text-center">
                                    <i class="iconly-Light-Heart2"></i>
                                    <p class="mb-0">{{ translate('no_product_in_wishlist') }}</p>
                                </div>
                            @else
                                <ul class="sidebar-cart-list">
                                    @foreach ($psfWishlist as $psfWish)
                                        @php($psfWishProduct = $psfWish->wishlistProduct)
                                        <li>
                                            <div class="cart-widget">
                                                <div class="dz-media me-3">
                                                    <img src="{{ getStorageImages(path: $psfWishProduct->thumbnail_full_url, type: 'backend-product') }}"
                                                         alt="{{ $psfWishProduct->name }}">
                                                </div>
                                                <div class="cart-content">
                                                    <h6 class="title">
                                                        <a href="{{ route('product', $psfWishProduct->slug) }}">{{ $psfWishProduct->name }}</a>
                                                    </h6>
                                                    <div class="d-flex align-items-center">
                                                        <h6 class="dz-price mb-0">
                                                            {!! getPriceRangeWithDiscount(product: $psfWishProduct) !!}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-auto">
                                    <a href="{{ route('wishlists') }}" class="btn btn-secondary btn-block">{{ translate('view_wishlist') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
