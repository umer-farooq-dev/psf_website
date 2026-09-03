{{-- PSF: structured data (client brief §20).
     Invisible to visitors — it tells Google what the shop is, so the address,
     phone and opening hours can appear directly in search results.
     Every value comes from the panel; nothing is written here. --}}

@if (psfSearchIndexing())
    <script type="application/ld+json">{!! psfJsonLd(psfLocalBusinessSchema()) !!}</script>

    <script type="application/ld+json">{!! psfJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => (string) (getWebConfig(name: 'company_name') ?: ''),
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('products') . '?name={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ]) !!}</script>
@endif
