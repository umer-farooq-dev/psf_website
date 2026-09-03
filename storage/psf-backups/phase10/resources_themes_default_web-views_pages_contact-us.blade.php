@extends('layouts.front-end.app')

@section('title', translate('contact_us'))

@push('css_or_js')
    <link rel="stylesheet"
        href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')
    <div class="__inline-58">
        <div class="container rtl">
            <div class="row">
                <div class="col-md-12 contact-us-page sidebar_heading text-center mb-2">
                    <h1 class="h3 mb-0 headerTitle">{{ translate('contact_us') }}</h1>
                </div>
            </div>
        </div>

        <div class="container rtl text-align-direction">
            <div class="row no-gutters py-5">
                <div class="col-lg-6 iframe-full-height-wrap ">
                    <img class="for-contact-image" src="{{ theme_asset(path: 'public/assets/front-end/png/contact.png') }}"
                        alt="">
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body for-send-message">
                            <h2 class="h4 mb-4 text-center font-semibold text-black">{{ translate('send_us_a_message') }}
                            </h2>
                            <form action="{{ route('contact.store') }}" method="POST" id="contact-form">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>{{ translate('your_name') }}</label>
                                            <input class="form-control name" name="name" type="text"
                                                value="{{ old('name') }}" placeholder="{{ translate('John_Doe') }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="cf-email">{{ translate('email_address') }}</label>
                                            <input class="form-control email" name="email" type="email"
                                                value="{{ old('email') }}"
                                                placeholder="{{ translate('enter_email_address') }}" required>

                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="cf-phone">{{ translate('your_phone') }}</label>
                                            <input class="form-control mobile_number phone-input-with-country-picker"
                                                type="tel" value="{{ old('mobile_number') }}" name="mobile_number"
                                                placeholder="{{ translate('contact_number') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="cf-subject">{{ translate('subject') }}:</label>
                                            <input class="form-control subject" type="text" name="subject"
                                                value="{{ old('subject') }}" placeholder="{{ translate('short_title') }}"
                                                required>

                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="cf-message">{{ translate('message') }}</label>
                                            <textarea class="form-control message" name="message" rows="6" required>{{ old('subject') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                @php($recaptcha = getWebConfig(name: 'recaptcha'))
                                @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                    <div class="dynamic-default-and-recaptcha-section">
                                        <input type="hidden" name="g-recaptcha-response" class="render-grecaptcha-response"
                                            data-action="contact" data-action="contact"
                                            data-input="#login-default-captcha-section"
                                            data-default-captcha="#login-default-captcha-section">

                                        <div class="default-captcha-container d-none" id="login-default-captcha-section"
                                            data-placeholder="{{ translate('enter_captcha_value') }}"
                                            data-base-url="{{ route('g-recaptcha-session-store') }}"
                                            data-session="{{ 'default_captcha_value_contact' }}">
                                        </div>
                                    </div>
                                @else
                                    <div class="default-captcha-container"
                                        data-placeholder="{{ translate('enter_captcha_value') }}"
                                        data-base-url="{{ route('g-recaptcha-session-store') }}"
                                        data-session="{{ 'default_captcha_value_contact' }}">
                                    </div>
                                @endif
                                <div class=" ">
                                    <button class="btn btn--primary" type="submit"
                                        id="contact-form-btn">{{ translate('send') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PSF: coordonnées, horaires et carte (client brief §18) — all panel-managed --}}
            @php($psfPhones = psfContactPhones())
            @php($psfHours = psfOpeningHours())
            @php($psfAddress = psfContactAddress())
            @php($psfMap = psfMapEmbedUrl())
            @php($psfDirections = psfDirectionsUrl())
            @php($psfEmail = getWebConfig(name: 'company_email'))

            @if ($psfAddress || count($psfPhones) > 0 || count($psfHours) > 0 || $psfMap)
                <div class="row g-3 pb-5">

                    @if ($psfAddress || count($psfPhones) > 0 || $psfEmail)
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h2 class="h5 mb-3 font-semibold text-black">
                                        {{ translate('Our_Contact_Details') }}
                                    </h2>

                                    @if ($psfAddress)
                                        <div class="d-flex gap-2 mb-3">
                                            <i class="fa fa-map-marker mt-1"></i>
                                            <div>
                                                <div class="fw-semibold">{{ translate('address') }}</div>
                                                <div class="text-muted">{{ $psfAddress }}</div>
                                                @if ($psfDirections)
                                                    <a href="{{ $psfDirections }}" target="_blank" rel="noopener"
                                                       class="btn btn-sm btn-outline-primary mt-2">
                                                        {{ translate('Get_Directions') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @foreach ($psfPhones as $psfPhone)
                                        <div class="d-flex gap-2 mb-2">
                                            <i class="fa fa-phone mt-1"></i>
                                            <div>
                                                @if ($psfPhone['label'])
                                                    <div class="fw-semibold">{{ $psfPhone['label'] }}</div>
                                                @endif
                                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $psfPhone['number']) }}">
                                                    {{ $psfPhone['number'] }}
                                                </a>
                                                @if ($psfPhone['whatsapp'])
                                                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $psfPhone['number']) }}"
                                                       target="_blank" rel="noopener"
                                                       class="ms-2 text-success">
                                                        <i class="fa fa-whatsapp"></i> WhatsApp
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @if ($psfEmail)
                                        <div class="d-flex gap-2 mb-2">
                                            <i class="fa fa-envelope-o mt-1"></i>
                                            <div>
                                                <a href="mailto:{{ $psfEmail }}">{{ $psfEmail }}</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (count($psfHours) > 0)
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h2 class="h5 mb-3 font-semibold text-black">
                                        {{ translate('Opening_Hours') }}
                                    </h2>
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                        @foreach ($psfHours as $psfDay)
                                            <tr>
                                                <th class="ps-0 fw-semibold">{{ $psfDay['day'] }}</th>
                                                <td class="pe-0 text-end">
                                                    @if ($psfDay['closed'] || $psfDay['hours'] === '')
                                                        <span class="text-danger">{{ translate('Closed') }}</span>
                                                    @else
                                                        {{ $psfDay['hours'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($psfMap)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h2 class="h5 mb-3 font-semibold text-black">
                                        {{ translate('Find_Us') }}
                                    </h2>
                                    <div class="ratio ratio-21x9">
                                        <iframe src="{{ $psfMap }}"
                                                style="border:0;" allowfullscreen loading="lazy"
                                                referrerpolicy="no-referrer-when-downgrade"
                                                title="{{ translate('Find_Us') }}"></iframe>
                                    </div>
                                    @if ($psfDirections)
                                        <div class="text-center mt-3">
                                            <a href="{{ $psfDirections }}" target="_blank" rel="noopener"
                                               class="btn btn--primary">
                                                {{ translate('Get_Directions') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
