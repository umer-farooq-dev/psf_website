{{-- PSF · inner page banner (template "dz-bnr-inr") with the breadcrumb.
     $bannerTitle : page title
     $breadcrumbs : [[label, url|null], …] after "Home"; the last one is the current page
     Picture from Paramètres PSF → New design content → Shop page; without
     one the banner uses the light colour of the design. --}}
@php($psfBannerImage = psfShopSettings()['banner'])
<div class="dz-bnr-inr {{ $psfBannerImage ? 'bg-secondary overlay-black-light' : '' }} psf-page-banner"
     @if ($psfBannerImage) style="background-image: url('{{ $psfBannerImage }}');" @endif>
    <div class="container">
        <div class="dz-bnr-inr-entry">
            <h1>{{ $bannerTitle }}</h1>
            <nav aria-label="breadcrumb" class="breadcrumb-row">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                    @foreach ($breadcrumbs ?? [] as [$crumbLabel, $crumbUrl])
                        @if ($crumbUrl && !$loop->last)
                            <li class="breadcrumb-item"><a href="{{ $crumbUrl }}">{{ $crumbLabel }}</a></li>
                        @else
                            <li class="breadcrumb-item" @if ($loop->last) aria-current="page" @endif>{{ $crumbLabel }}</li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
</div>
