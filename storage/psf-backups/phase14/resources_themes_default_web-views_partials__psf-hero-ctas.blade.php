{{-- PSF: call-to-action buttons under the homepage slider (client brief §19).
     Labels and links come from the panel (Paramètres PSF), never from here. --}}
<div class="container rtl text-align-direction">
    <div class="d-flex flex-wrap justify-content-center gap-2">
        @foreach (psfHeroCtas() as $psfCtaIndex => $psfCta)
            <a href="{{ $psfCta['url'] }}"
               class="btn {{ $psfCtaIndex === 0 ? 'btn--primary psf-accent-btn' : 'btn-outline-primary' }} text-capitalize">
                {{ $psfCta['label'] }}
            </a>
        @endforeach
    </div>
</div>
