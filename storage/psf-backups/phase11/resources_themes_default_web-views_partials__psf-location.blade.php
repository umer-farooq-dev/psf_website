{{-- PSF: shop location summary on the homepage (client brief §19).
     Reads the same panel settings as the contact page, so PSF only types
     the address, phones and hours once. --}}
@php($psfAddress = psfContactAddress())
@php($psfPhones = psfContactPhones())
@php($psfHours = psfOpeningHours())
@php($psfDirections = psfDirectionsUrl())

@if ($psfAddress || count($psfPhones) > 0 || count($psfHours) > 0)
    <div class="container rtl text-align-direction">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-start">

                    @if ($psfAddress)
                        <div class="col-md-4">
                            <h2 class="h6 font-semibold text-black mb-2">{{ translate('Visit_Our_Shop') }}</h2>
                            <div class="text-muted">{{ $psfAddress }}</div>
                            @if ($psfDirections)
                                <a href="{{ $psfDirections }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary mt-2">
                                    {{ translate('Get_Directions') }}
                                </a>
                            @endif
                        </div>
                    @endif

                    @if (count($psfPhones) > 0)
                        <div class="col-md-4">
                            <h2 class="h6 font-semibold text-black mb-2">{{ translate('Call_Us') }}</h2>
                            @foreach ($psfPhones as $psfPhone)
                                <div class="mb-1">
                                    @if ($psfPhone['label'])
                                        <span class="text-muted">{{ $psfPhone['label'] }} :</span>
                                    @endif
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $psfPhone['number']) }}">
                                        {{ $psfPhone['number'] }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (count($psfHours) > 0)
                        <div class="col-md-4">
                            <h2 class="h6 font-semibold text-black mb-2">{{ translate('Opening_Hours') }}</h2>
                            @foreach ($psfHours as $psfDay)
                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-muted">{{ $psfDay['day'] }}</span>
                                    <span>
                                        @if ($psfDay['closed'] || $psfDay['hours'] === '')
                                            <span class="text-danger">{{ translate('Closed') }}</span>
                                        @else
                                            {{ $psfDay['hours'] }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
