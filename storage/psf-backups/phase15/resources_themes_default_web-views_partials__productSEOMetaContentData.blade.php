{{-- PSF (client brief §8/§19): this block used to require a hand-made SEO
     record ($metaContentData). With none saved, product pages fell back to the
     site-wide tags, so a product shared on WhatsApp showed the shop name, the
     "about us" text and no image. The record is now optional: it still wins
     when present, otherwise the product's own name, description and thumbnail
     are used. --}}
@if(isset($productDetails))
    @if($metaContentData?->title)
        <meta name="title" content="{{ $metaContentData?->title }}">
        <meta property="og:title" content="{{ $metaContentData?->title }}">
        <meta name="twitter:title" content="{{ $metaContentData?->title }}">
    @else
        <meta name="title" content="{{ $productDetails?->name }}">
        <meta property="og:title" content="{{ $productDetails?->name }}">
        <meta name="twitter:title" content="{{ $productDetails?->name }}">
    @endif

    @if($metaContentData?->description)
        <meta name="description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        <meta property="og:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        <meta name="twitter:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
    @else
        {{-- PSF: a real sentence reads far better in a WhatsApp preview than
             the product name chopped into comma-separated keywords. --}}
        @php($psfMetaDescription = \Illuminate\Support\Str::limit(
            trim(strip_tags((string) ($productDetails['short_description'] ?? '')))
                ?: trim(strip_tags((string) ($productDetails['details'] ?? '')))
                ?: (string) $productDetails['name'],
            160
        ))
        <meta name="description" content="{{ $psfMetaDescription }}">
        <meta property="og:description" content="{{ $psfMetaDescription }}">
        <meta name="twitter:description" content="{{ $psfMetaDescription }}">
    @endif

    <meta name="keywords" content="{{ implode(', ', explode(' ', trim($productDetails['name']))) }}">

    @if($productDetails->added_by == 'seller')
        <meta name="author" content="{{ $productDetails->seller->shop?$productDetails->seller->shop->name:$productDetails->seller->f_name}}">
    @elseif($productDetails->added_by == 'admin')
        <meta name="author" content="{{$web_config['company_name']}}">
    @endif

    {{-- PSF: this image is what WhatsApp shows when the product link is sent.
         The SEO record's own image wins; otherwise the product thumbnail.
         `path` is null when the file is missing, so no broken URL is shared. --}}
    @php($psfOgImage = $metaContentData?->image_full_url['path']
        ?: ($productDetails->thumbnail_full_url['path'] ?? null))
    @if($psfOgImage)
        <meta property="og:image" content="{{ $psfOgImage }}">
        <meta name="twitter:image" content="{{ $psfOgImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ route('product', [$productDetails->slug]) }}">
    <meta name="twitter:url" content="{{ route('product', [$productDetails->slug]) }}">

    {{-- PSF: the robots rules below belong to the hand-made SEO record only.
         Without this guard every product page would emit a second, bare
         "robots: index" tag alongside the site-wide one. --}}
    @if($metaContentData)
    @if($metaContentData?->index != 'noindex')
        <meta name="robots" content="index">
    @endif

    @if($metaContentData?->no_follow || $metaContentData?->no_image_index || $metaContentData?->no_archive || $metaContentData?->no_snippet)
        <meta name="robots" content="{{ ($metaContentData?->no_follow ? 'nofollow' : '') . ($metaContentData?->no_image_index ? ' noimageindex' : '') . ($metaContentData?->no_archive ? ' noarchive' : '') . ($metaContentData?->no_snippet ? ' nosnippet' : '') }}">
    @endif

    @if($metaContentData?->meta_max_snippet)
        <meta name="robots" content="max-snippet{{ $metaContentData?->max_snippet_value ? ': ' . $metaContentData?->max_snippet_value : '' }}">
    @endif

    @if($metaContentData?->max_video_preview)
        <meta name="robots" content="max-video-preview{{ $metaContentData?->max_video_preview_value ? ': ' . $metaContentData?->max_video_preview_value : '' }}">
    @endif

    @if($metaContentData?->max_image_preview)
        <meta name="robots" content="max-image-preview{{ $metaContentData?->max_image_preview_value ? ': ' . $metaContentData?->max_image_preview_value : '' }}">
    @endif
    @endif {{-- PSF: end of the SEO-record-only robots rules --}}
@endif
