{{-- PSF: shop location strip on the home page (client brief §19, "Location / Contact").
     Reads the same panel settings as the contact page, so PSF types the address,
     phones and hours once. Kept to a single compact row: three sparse columns
     left a lot of empty space, and the hours print as one folded line rather
     than seven rows. Anything not set simply drops out. --}}
@php($psfAddress = psfContactAddress())
@php($psfPhones = psfContactPhones())
@php($psfHoursLine = psfOpeningHoursSummary())
@php($psfDirections = psfDirectionsUrl())

@if ($psfAddress || count($psfPhones) > 0 || $psfHoursLine !== '')
    <div class="container rtl text-align-direction">
        <div class="card">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

                    <div class="d-flex flex-wrap align-items-center gap-4">
                        @if ($psfAddress)
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa fa-map-marker web-text-primary"></i>
                                <span>{{ $psfAddress }}</span>
                            </span>
                        @endif

                        @foreach ($psfPhones as $psfPhone)
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa fa-phone web-text-primary"></i>
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $psfPhone['number']) }}">
                                    {{ $psfPhone['number'] }}
                                </a>
                            </span>
                        @endforeach

                        @if ($psfHoursLine !== '')
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa fa-clock-o web-text-primary"></i>
                                <span>{{ $psfHoursLine }}</span>
                            </span>
                        @endif
                    </div>

                    @if ($psfDirections)
                        <a href="{{ $psfDirections }}" target="_blank" rel="noopener"
                           class="btn btn-outline-primary btn-sm text-nowrap">
                            {{ translate('Get_Directions') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
