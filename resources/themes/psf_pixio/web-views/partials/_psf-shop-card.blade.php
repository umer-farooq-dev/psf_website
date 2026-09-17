{{-- PSF · Pixio product card (template "shop-card"), shared by every product list.
     $product   : Product model
     $wowDelay  : optional entrance delay, e.g. "0.3s"
     Actions use the shop's own routes through psf-pixio.js:
       quick view → productQuickView(), heart → addWishlist(),
       basket → cart.add (or the quick view when a colour/variation must be chosen),
       products without a price → WhatsApp price request (Paramètres PSF template). --}}
@if (isset($product))
    @php($psfHasPrice = psfHasPrice($product))
    @php($psfDiscount = $psfHasPrice ? getProductPriceByType(product: $product, type: 'discount', result: 'value') : 0)
    @php($psfOutOfStock = $product->product_type === 'physical' && $product->current_stock <= 0 && !psfIsOnOrder($product))
    @php($psfInWishlist = in_array((int) $product->id, psfWishlistIds(), true))
    <div class="shop-card {{ isset($wowDelay) ? 'wow fadeInUp' : '' }}" @isset($wowDelay) data-wow-delay="{{ $wowDelay }}" @endisset>
        <div class="dz-media">
            <a href="{{ route('product', $product->slug) }}" aria-label="{{ $product->name }}">
                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                     alt="{{ $product->name }}" loading="lazy">
            </a>
            <div class="shop-meta">
                <a href="javascript:void(0);" class="btn btn-secondary btn-md btn-rounded psf-quick-view"
                   data-product-id="{{ $product->id }}" data-url="{{ route('product', $product->slug) }}">
                    <i class="fa-solid fa-eye d-md-none d-block"></i>
                    <span class="d-md-block d-none">{{ translate('quick_view') }}</span>
                </a>
                <button type="button" class="btn btn-primary meta-icon dz-wishicon psf-wishlist {{ $psfInWishlist ? 'active' : '' }}"
                        data-product-id="{{ $product->id }}" aria-label="{{ translate('add_to_wishlist') }}">
                    <i class="icon feather icon-heart dz-heart"></i>
                    <i class="icon feather icon-heart-on dz-heart-fill"></i>
                </button>
                @if ($psfHasPrice && !$psfOutOfStock)
                    <button type="button" class="btn btn-primary meta-icon dz-carticon psf-add-cart"
                            data-product-id="{{ $product->id }}"
                            data-min="{{ max(1, (int) $product->minimum_order_qty) }}"
                            data-options="{{ psfProductHasOptions($product) ? 1 : 0 }}"
                            data-url="{{ route('product', $product->slug) }}"
                            aria-label="{{ translate('add_to_cart') }}">
                        <i class="flaticon flaticon-basket"></i>
                        <i class="flaticon flaticon-basket-on dz-heart-fill"></i>
                    </button>
                @else
                    <a href="{{ psfProductWhatsappUrl($product) }}" target="_blank" rel="noopener"
                       class="btn btn-primary meta-icon psf-meta-whatsapp"
                       aria-label="{{ $psfHasPrice ? translate('Order_on_WhatsApp') : translate('Request_the_price') }}"
                       title="{{ $psfHasPrice ? translate('Order_on_WhatsApp') : translate('Request_the_price') }}">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                @endif
            </div>
        </div>
        <div class="dz-content">
            <h5 class="title"><a href="{{ route('product', $product->slug) }}">{{ $product->name }}</a></h5>
            @if ($psfHasPrice)
                <h5 class="price">
                    @if ($psfDiscount > 0)
                        <del>{{ webCurrencyConverter(amount: $product->unit_price) }}</del>
                    @endif
                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                </h5>
            @else
                <h6 class="price psf-price-on-request">{{ translate('Contact_us_for_the_price') }}</h6>
            @endif
        </div>
        @if ($psfDiscount > 0 || $psfOutOfStock || psfIsOnOrder($product))
            <div class="product-tag">
                @if ($psfOutOfStock)
                    <span class="badge psf-badge-muted">{{ translate('out_of_stock') }}</span>
                @elseif (psfIsOnOrder($product))
                    <span class="badge psf-badge-muted">{{ translate('On_Order') }}</span>
                @else
                    <span class="badge">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                @endif
            </div>
        @endif
    </div>
@endif
