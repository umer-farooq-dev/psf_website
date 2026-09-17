{{-- PSF · time left on a flash deal, above its product list --}}
<div class="container pt-4 psf-deal-countdown">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h5 class="title mb-0">{{ translate('deal_ends_in') }}</h5>
        <div class="psf-countdown countdown-timer" data-end="{{ $endsAt }}">
            <div class="clock">
                <div class="clock-item"><span class="days">00</span><p>{{ translate('days') }}</p></div>
                <div class="clock-item"><span class="hours">00</span><p>{{ translate('hours') }}</p></div>
                <div class="clock-item"><span class="minutes">00</span><p>{{ translate('minutes') }}</p></div>
                <div class="clock-item"><span class="seconds">00</span><p>{{ translate('seconds') }}</p></div>
            </div>
        </div>
    </div>
</div>
