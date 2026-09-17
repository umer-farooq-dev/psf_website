{{-- PSF · shop sidebar (template shop-list "shop-filter").
     Every control carries its request name; psf-shop.js turns a change into
     the shop's own filtered list request (ProductListController).
     $categories, $activeBrands : lists given by the controller
     $categoryRoute             : fn($category) → link of a category on this page
     $brandRoute                : fn($brand) → link of a brand on this page
     $resetUrl                  : where "reset" leads --}}
@php
    $psfFilterOptions = psfShopFilterOptions();
    $psfMaxPrice = (float) getProductMaxUnitPriceRange(type: 'web');
    $psfPriceFrom = is_numeric(request('min_price')) ? (float) request('min_price') : 0;
    $psfPriceTo = is_numeric(request('max_price')) ? (float) request('max_price') : $psfMaxPrice;
    $psfSelectedColors = array_filter((array) request('color_ids'), 'is_string');
    $psfSelectedValues = array_filter((array) request('attribute_values'), 'is_string');
    $psfOpenCategoryIds = array_filter([request('category_id'), request('sub_category_id'), request('sub_sub_category_id')]);
    $psfCurrency = psfCurrencyFormat();
@endphp

<aside>
    <div class="d-flex align-items-center justify-content-between m-b30">
        <h6 class="title mb-0 fw-normal d-flex">
            <i class="flaticon-filter me-3"></i>
            {{ translate('Filter') }}
        </h6>
    </div>

    <div class="widget widget_search">
        <form class="form-group psf-shop-search" role="search">
            <div class="input-group">
                <input name="product_name" type="search" class="form-control" autocomplete="off"
                       value="{{ request('product_name') ?? request('name') ?? request('search') }}"
                       placeholder="{{ translate('search_for_products') }}" aria-label="{{ translate('search_for_products') }}">
                <div class="input-group-addon">
                    <button type="submit" class="btn" aria-label="{{ translate('search') }}">
                        <i class="icon feather icon-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($psfMaxPrice > 0)
        <div class="widget">
            <h6 class="widget-title">{{ translate('price') }}</h6>
            <div class="price-slide range-slider">
                <div class="price">
                    <div class="range-slider style-1">
                        <div id="psf-price-slider" class="mb-3"
                             data-min="0" data-max="{{ ceil($psfMaxPrice) }}"
                             data-from="{{ max(0, min($psfPriceFrom, $psfMaxPrice)) }}"
                             data-to="{{ max(0, min($psfPriceTo, ceil($psfMaxPrice))) }}"
                             data-symbol="{{ $psfCurrency['symbol'] }}"
                             data-position="{{ $psfCurrency['position'] }}"></div>
                        <span class="example-val" id="psf-price-min" data-label="{{ translate('Min_price') }}"></span>
                        <span class="example-val" id="psf-price-max" data-label="{{ translate('Max_price') }}"></span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (count($psfFilterOptions['colors']) > 0)
        <div class="widget">
            <h6 class="widget-title">{{ translate('color') }}</h6>
            <div class="d-flex align-items-center flex-wrap color-filter ps-2">
                @foreach ($psfFilterOptions['colors'] as $colorCode => $colorName)
                    <div class="form-check" title="{{ $colorName }}">
                        <input class="form-check-input psf-filter-color" type="checkbox" name="color_ids[]"
                               id="psf-color-{{ $loop->index }}" value="{{ $colorCode }}" aria-label="{{ $colorName }}"
                               {{ in_array($colorCode, $psfSelectedColors, true) ? 'checked' : '' }}>
                        <span style="background-color: {{ $colorCode }};"></span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @foreach ($psfFilterOptions['attributes'] as $attributeTitle => $attributeValues)
        <div class="widget">
            <h6 class="widget-title">{{ translate($attributeTitle) }}</h6>
            <div class="btn-group product-size psf-size-pills flex-wrap">
                @foreach ($attributeValues as $attributeValue)
                    <input type="radio" class="btn-check psf-filter-attribute"
                           name="attribute_values[{{ $attributeTitle }}]"
                           id="psf-attr-{{ $loop->parent->index }}-{{ $loop->index }}"
                           value="{{ $attributeValue }}"
                           {{ ($psfSelectedValues[$attributeTitle] ?? null) === $attributeValue ? 'checked' : '' }}>
                    <label class="btn" for="psf-attr-{{ $loop->parent->index }}-{{ $loop->index }}">{{ $attributeValue }}</label>
                @endforeach
            </div>
        </div>
    @endforeach

    @if ($web_config['digital_product_setting'])
        <div class="widget widget_categories">
            <h6 class="widget-title">{{ translate('Product_Type') }}</h6>
            <ul>
                @foreach (['all' => 'All', 'physical' => 'physical', 'digital' => 'digital'] as $typeValue => $typeLabel)
                    <li class="cat-item {{ (request('product_type') ?: 'all') === $typeValue ? 'current-cat' : '' }}">
                        <a href="javascript:void(0);" class="psf-filter-link" data-name="product_type" data-value="{{ $typeValue }}">{{ translate($typeLabel) }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($categories) > 0)
        <div class="widget widget_categories">
            <h6 class="widget-title">{{ translate('categories') }}</h6>
            <ul>
                @foreach ($categories as $category)
                    @php
                        $psfCategoryOpen = in_array($category['id'], $psfOpenCategoryIds)
                            || $category->childes->contains(fn ($child) => in_array($child['id'], $psfOpenCategoryIds)
                                || $child->childes->contains(fn ($grandChild) => in_array($grandChild['id'], $psfOpenCategoryIds)));
                    @endphp
                    <li class="cat-item {{ request('category_id') == $category['id'] ? 'current-cat' : '' }}">
                        <a href="{{ $categoryRoute($category) }}">{{ $category['name'] }}</a>
                        <span class="psf-count">({{ $category['product_count'] ?? 0 }})</span>
                        @if ($psfCategoryOpen && $category->childes->count() > 0)
                            <ul class="children">
                                @foreach ($category->childes as $child)
                                    <li class="cat-item {{ request('sub_category_id') == $child['id'] ? 'current-cat' : '' }}">
                                        <a href="{{ $categoryRoute($child) }}">{{ $child['name'] }}</a>
                                        <span class="psf-count">({{ $child['sub_category_product_count'] ?? 0 }})</span>
                                        @if ($child->childes->count() > 0 && (in_array($child['id'], $psfOpenCategoryIds) || $child->childes->contains(fn ($grandChild) => in_array($grandChild['id'], $psfOpenCategoryIds))))
                                            <ul class="children">
                                                @foreach ($child->childes as $grandChild)
                                                    <li class="cat-item {{ request('sub_sub_category_id') == $grandChild['id'] ? 'current-cat' : '' }}">
                                                        <a href="{{ $categoryRoute($grandChild) }}">{{ $grandChild['name'] }}</a>
                                                        <span class="psf-count">({{ $grandChild['sub_sub_category_product_count'] ?? 0 }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($web_config['brand_setting'] && count($activeBrands) > 0)
        <div class="widget widget_categories">
            <h6 class="widget-title">{{ translate('brands') }}</h6>
            <ul>
                @foreach ($activeBrands as $brand)
                    <li class="cat-item {{ request('brand_id') == $brand['id'] ? 'current-cat' : '' }}">
                        <a href="{{ $brandRoute($brand) }}">{{ $brand['name'] }}</a>
                        <span class="psf-count">({{ $brand['brand_products_count'] ?? 0 }})</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($showDataFrom ?? false)
        <div class="widget widget_categories">
            <h6 class="widget-title">{{ translate('Filter_By') }}</h6>
            <ul>
                @foreach (['' => 'Default', 'best-selling' => 'Best_Selling', 'top-rated' => 'Top_Rated', 'most-favorite' => 'Most_Favorite'] as $fromValue => $fromLabel)
                    <li class="cat-item {{ (string) request('data_from') === $fromValue ? 'current-cat' : '' }}">
                        <a href="javascript:void(0);" class="psf-filter-link" data-name="data_from" data-value="{{ $fromValue }}">{{ translate($fromLabel) }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($psfFilterOptions['tags']->count() > 0)
        <div class="widget widget_tag_cloud">
            <h6 class="widget-title">{{ translate('tags') }}</h6>
            <div class="tagcloud">
                @foreach ($psfFilterOptions['tags'] as $tag)
                    <a href="javascript:void(0);" class="psf-filter-link {{ request('tag_id') == $tag->id ? 'active' : '' }}"
                       data-name="tag_id" data-value="{{ $tag->id }}" data-toggle="1">{{ $tag->tag }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <a href="{{ $resetUrl }}" class="btn btn-sm font-14 btn-secondary btn-sharp text-uppercase">{{ translate('reset') }}</a>
</aside>
