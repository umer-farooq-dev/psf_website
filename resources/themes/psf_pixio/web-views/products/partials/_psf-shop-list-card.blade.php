{{-- PSF · product row of the shop list view (template shop-list "dz-shop-card style-2").
     Same actions and price rules as the grid card (_psf-shop-card). --}}
@if (isset($product))
    @php
        $psfHasPrice = psfHasPrice($product);
        $psfDiscount = $psfHasPrice ? getProductPriceByType(product: $product, type: 'discount', result: 'value') : 0;
        $psfOnOrder = psfIsOnOrder($product);
        $psfOutOfStock = $product->product_type === 'physical' && $product->current_stock <= 0 && !$psfOnOrder;
        $psfInWishlist = in_array((int) $product->id, psfWishlistIds(), true);
        $psfProductUrl = route('product', $product->slug);
        $psfReviewCount = (int) ($product->reviews_count ?? $product->reviews?->count() ?? 0);
        $psfRating = $psfReviewCount > 0
            ? (float) ($product->reviews_avg_rating ?? getOverallRating($product->reviews)[0])
            : 0;
        $psfColors = json_decode((string) $product->colors, true) ?: [];
        $psfExcerpt = \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) $product->details))), 260);
    @endphp
    <div class="dz-shop-card style-2 psf-list-card">
        <div class="dz-media">
            <a href="{{ $psfProductUrl }}" aria-label="{{ $product->name }}">
                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}" alt="{{ $product->name }}" loading="lazy">
            </a>
            @if ($psfDiscount > 0 || $psfOutOfStock || $psfOnOrder)
                <div class="product-tag">
                    @if ($psfOutOfStock)
                        <span class="badge psf-badge-muted">{{ translate('out_of_stock') }}</span>
                    @elseif ($psfOnOrder)
                        <span class="badge psf-badge-muted">{{ translate('On_Order') }}</span>
                    @else
                        <span class="badge">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                    @endif
                </div>
            @endif
        </div>
        <div class="dz-content">
            <div class="dz-header">
                <div>
                    <h4 class="title mb-0"><a href="{{ $psfProductUrl }}">{{ $product->name }}</a></h4>
                    @if ($product->category)
                        <ul class="dz-tags">
                            <li><a href="{{ route('category-products', ['slug' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
                        </ul>
                    @endif
                </div>
                @if ($psfReviewCount > 0)
                    <div class="review-num">
                        <ul class="dz-rating">
                            @for ($psfStar = 1; $psfStar <= 5; $psfStar++)
                                <li class="{{ $psfStar <= round($psfRating) ? 'star-fill' : '' }}"><i class="flaticon-star-1"></i></li>
                            @endfor
                        </ul>
                        <span><a href="{{ $psfProductUrl }}#psf-reviews">{{ $psfReviewCount }} {{ translate('reviews') }}</a></span>
                    </div>
                @endif
            </div>
            <div class="dz-body">
                @if ($psfExcerpt !== '')
                    <div class="dz-rating-box">
                        <div>
                            <p class="dz-para">{{ $psfExcerpt }}</p>
                        </div>
                    </div>
                @endif
                <div class="rate">
                    <div class="d-flex align-items-center mb-xl-3 mb-2 flex-wrap">
                        <div class="meta-content">
                            <span class="price-name">{{ translate('price') }}</span>
                            @if ($psfHasPrice)
                                <span class="price">
                                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                    @if ($psfDiscount > 0)
                                        <del>{{ webCurrencyConverter(amount: $product->unit_price) }}</del>
                                    @endif
                                </span>
                            @else
                                <span class="price psf-price-on-request">{{ translate('Contact_us_for_the_price') }}</span>
                            @endif
                        </div>
                        @if (count($psfColors) > 0)
                            <div class="meta-content">
                                <span class="color-name">{{ translate('color') }}</span>
                                <div class="d-flex align-items-center color-filter">
                                    @foreach ($psfColors as $psfColor)
                                        <div class="form-check" title="{{ psfColorName($psfColor) }}">
                                            <span style="background-color: {{ $psfColor }};"></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex">
                        @if ($psfHasPrice && !$psfOutOfStock)
                            <button type="button" class="btn btn-secondary btn-md btn-icon psf-add-cart"
                                    data-product-id="{{ $product->id }}"
                                    data-min="{{ max(1, (int) $product->minimum_order_qty) }}"
                                    data-options="{{ psfProductHasOptions($product) ? 1 : 0 }}" data-url="{{ $psfProductUrl }}">
                                <i class="icon feather icon-shopping-cart d-md-none d-block"></i>
                                <span class="d-md-block d-none">{{ translate('add_to_cart') }}</span>
                            </button>
                        @else
                            <a href="{{ psfProductWhatsappUrl($product) }}" target="_blank" rel="noopener"
                               class="btn btn-secondary btn-md btn-icon psf-accent-btn">
                                <i class="fa-brands fa-whatsapp d-md-none d-block"></i>
                                <span class="d-md-block d-none">{{ $psfHasPrice ? translate('Order_on_WhatsApp') : translate('Request_the_price') }}</span>
                            </a>
                        @endif
                        <button type="button" class="bookmark-btn style-1 psf-wishlist psf-bookmark {{ $psfInWishlist ? 'active' : '' }}"
                                data-product-id="{{ $product->id }}" aria-label="{{ translate('add_to_wishlist') }}">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                        <a href="javascript:void(0);" class="btn btn-outline-secondary btn-md btn-icon ms-2 psf-quick-view"
                           data-product-id="{{ $product->id }}" data-url="{{ $psfProductUrl }}" aria-label="{{ translate('quick_view') }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
