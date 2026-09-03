{{-- PSF: Product + Breadcrumb structured data for one product page.
     Follows the PSF price rule: a product with no price gets no `offers`
     block, because an offer without a price is invalid structured data —
     and we must not invent a price we do not have. --}}

@if (psfSearchIndexing() && !empty($product))
    @php($psfCategory = $product->category ?? null)
    {{-- thumbnail_full_url is an array (key/path/status); `path` is null when
         the file is missing, so a broken image URL is never advertised. --}}
    @php($psfThumb = $product->thumbnail_full_url ?? null)
    @php($psfImage = is_array($psfThumb) ? ($psfThumb['path'] ?? null) : ($psfThumb->path ?? null))

    @php($psfProductSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => (string) ($product['name'] ?? ''),
        'description' => trim(strip_tags((string) ($product['short_description'] ?? $product['details'] ?? ''))) ?: null,
        'sku' => (string) ($product['code'] ?? '') ?: null,
        'image' => $psfImage ?: null,
        'brand' => !empty($product->brand?->name) ? [
            '@type' => 'Brand',
            'name' => $product->brand->name,
        ] : null,
        'offers' => psfHasPrice($product) ? array_filter([
            '@type' => 'Offer',
            'url' => url()->current(),
            'price' => (string) round((float) $product['unit_price'], 2),
            'priceCurrency' => (string) (getWebConfig(name: 'currency_model') === 'multi_currency'
                ? (getWebConfig(name: 'system_default_currency_code') ?: 'XOF')
                : (getWebConfig(name: 'system_default_currency_code') ?: 'XOF')),
            'availability' => psfIsOnOrder($product)
                ? 'https://schema.org/PreOrder'
                : 'https://schema.org/InStock',
        ]) : null,
    ]))

    <script type="application/ld+json">{!! psfJsonLd($psfProductSchema) !!}</script>

    @php($psfCrumbs = [['name' => translate('home'), 'item' => url('/')]])
    @if ($psfCategory)
        @php($psfCrumbs[] = ['name' => $psfCategory->name, 'item' => route('products', ['category_id' => $psfCategory->id])])
    @endif
    @php($psfCrumbs[] = ['name' => (string) ($product['name'] ?? ''), 'item' => url()->current()])

    <script type="application/ld+json">{!! psfJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($psfCrumbs)->values()->map(fn ($crumb, $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['name'],
            'item' => $crumb['item'],
        ])->all(),
    ]) !!}</script>
@endif
