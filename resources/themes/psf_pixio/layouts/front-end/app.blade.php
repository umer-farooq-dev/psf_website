<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- robots is emitted once, by the SEO partial below --}}
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}"/>
    <meta name="google-site-verification" content="{{ getWebConfig('google_search_console_code') }}">
    <meta name="msvalidate.01" content="{{ getWebConfig('bing_webmaster_code') }}">
    <meta name="baidu-site-verification" content="{{ getWebConfig('baidu_webmaster_code') }}">
    <meta name="yandex-verification" content="{{ getWebConfig('yandex_webmaster_code') }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">

    {{-- Pixio design --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/animate/animate.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/magnific-popup/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/lightgallery/dist/css/lightgallery.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/lightgallery/dist/css/lg-thumbnail.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/vendor/lightgallery/dist/css/lg-zoom.css') }}">
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/css/style.css') }}">

    {{-- shop widgets shared with the classic design --}}
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-regular-rounded.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-solid-rounded.css') }}">

    @stack('css_or_js')

    @include(VIEW_FILE_NAMES['robots_meta_content_partials'])
    @include('web-views.partials._psf-schema')

    {{-- Every colour comes from the panel (Paramètres PSF → Design). --}}
    <style>
{!! psfPixioCssVariables() !!}
    </style>
    <link rel="stylesheet" href="{{ psfDesignAsset('assets/css/psf-pixio.css') }}">

    {!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>

<body>
<div class="page-wraper">

    @include('layouts.front-end.partials._modals')
    @include('layouts.front-end.partials._quick-view-modal')
    @include('layouts.front-end.partials.modal._buy-now')

    @include('layouts.front-end.partials._header')
    @include('layouts.front-end.partials._alert-message')

    <span id="authentication-status" data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"></span>

    <div class="loading-parent d-none" id="loading">
        <div class="psf-loader"><span></span></div>
    </div>
    <div id="global-loader" class="global-loader d-none">
        <span class="loader"></span>
    </div>

    <div class="page-content bg-light">
        @yield('content')
    </div>

    <span id="message-otp-sent-again" data-text="{{ translate('OTP_has_been_sent_again.') }}"></span>
    <span id="message-wait-for-new-code" data-text="{{ translate('please_wait_for_new_code.') }}"></span>
    <span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha.') }}"></span>
    <span id="message-please-retype-password" data-text="{{ translate('please_ReType_Password') }}"></span>
    <span id="message-password-not-match" data-text="{{ translate('password_do_not_match') }}"></span>
    <span id="message-password-match" data-text="{{ translate('password_match') }}"></span>
    <span id="message-password-need-longest" data-text="{{ translate('password_Must_Be_6_Character') }}"></span>
    <span id="message-send-successfully" data-text="{{ translate('send_successfully') }}"></span>
    <span id="message-update-successfully" data-text="{{ translate('update_successfully') }}"></span>
    <span id="message-successfully-copied" data-text="{{ translate('successfully_copied') }}"></span>
    <span id="message-copied-failed" data-text="{{ translate('copied_failed') }}"></span>
    <span id="message-select-payment-method" data-text="{{ translate('please_select_a_payment_Methods') }}"></span>
    <span id="message-please-choose-all-options" data-text="{{ translate('please_choose_all_the_options') }}"></span>
    <span id="message-cannot-input-minus-value" data-text="{{ translate('cannot_input_minus_value') }}"></span>
    <span id="message-all-input-field-required" data-text="{{ translate('all_input_field_required') }}"></span>
    <span id="message-no-data-found" data-text="{{ translate('no_data_found') }}"></span>
    <span id="message-minimum-order-quantity-cannot-less-than" data-text="{{ translate('minimum_order_quantity_cannot_be_less_than_') }}"></span>
    <span id="message-item-has-been-removed-from-cart" data-text="{{ translate('item_has_been_removed_from_cart') }}"></span>
    <span id="message-sorry-stock-limit-exceeded" data-text="{{ translate('sorry_stock_limit_exceeded') }}"></span>
    <span id="message-sorry-the-minimum-order-quantity-not-match" data-text="{{ translate('sorry_the_minimum_order_quantity_does_not_match') }}"></span>
    <span id="message-cart" data-text="{{ translate('cart') }}"></span>

    <span id="route-messages-store" data-url="{{ route('messages') }}"></span>
    <span id="route-address-update" data-url="{{ route('address-update') }}"></span>
    <span id="route-coupon-apply" data-url="{{ route('coupon.apply') }}"></span>
    <span id="route-cart-add" data-url="{{ route('cart.add') }}"></span>
    <span id="route-cart-remove" data-url="{{ route('cart.remove') }}"></span>
    <span id="route-cart-variant-price" data-url="{{ route('cart.variant_price') }}"></span>
    <span id="route-cart-nav-cart" data-url="{{ route('cart.nav-cart') }}"></span>
    <span id="route-cart-order-again" data-url="{{ route('cart.order-again') }}"></span>
    <span id="route-cart-updateQuantity" data-url="{{ route('cart.updateQuantity') }}"></span>
    <span id="route-cart-updateQuantity-guest" data-url="{{ route('cart.updateQuantity.guest') }}"></span>
    <span id="route-pay-offline-method-list" data-url="{{ route('pay-offline-method-list') }}"></span>
    <span id="route-customer-auth-sign-up" data-url="{{ route('customer.auth.sign-up') }}"></span>
    <span id="route-searched-products" data-url="{{ url('/searched-products') }}"></span>
    <span id="route-currency-change" data-url="{{ route('currency.change') }}"></span>
    <span id="route-store-wishlist" data-url="{{ route('store-wishlist') }}"></span>
    <span id="route-delete-wishlist" data-url="{{ route('delete-wishlist') }}"></span>
    <span id="route-wishlists" data-url="{{ route('wishlists') }}"></span>
    <span id="route-quick-view" data-url="{{ route('quick-view') }}"></span>
    <span id="route-checkout-details" data-url="{{ route('checkout-details') }}"></span>
    <span id="route-checkout-payment" data-url="{{ route('checkout-payment') }}"></span>
    <span id="route-set-shipping-id" data-url="{{ route('customer.set-shipping-method') }}"></span>
    <span id="route-order-note" data-url="{{ route('order_note') }}"></span>
    <span id="route-product-restock-request" data-url="{{ route('cart.product-restock-request') }}"></span>
    <div id="route-vendor-list" data-url="{{ route('vendors') }}"></div>
    <span id="route-get-session-recaptcha-code" data-route="{{ route('get-session-recaptcha-code') }}" data-mode="{{ env('APP_MODE') }}"></span>
    <span id="password-error-message"
          data-max-character="{{ translate('at_least_8_characters').'.' }}"
          data-uppercase-character="{{ translate('at_least_one_uppercase_letter_').'(A...Z)'.'.' }}"
          data-lowercase-character="{{ translate('at_least_one_lowercase_letter_').'(a...z)'.'.' }}"
          data-number="{{ translate('at_least_one_number').'(0...9)'.'.' }}"
          data-symbol="{{ translate('at_least_one_symbol').'(!...%)'.'.' }}"></span>
    <span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
    <span id="system-session-direction" data-value="{{ session()->get('direction') ?? 'ltr' }}"></span>
    <span id="is-request-customer-auth-sign-up" data-value="{{ Request::is('customer/auth/sign-up*') ? 1 : 0 }}"></span>
    <span id="is-customer-auth-active" data-value="{{ auth('customer')->check() ? 1 : 0 }}"></span>
    <span id="storage-flash-deals" data-value="{{ $web_config['flash_deals']['start_date'] ?? '' }}"></span>
    <span id="exceeds10MBSizeLimit" data-text="{{ translate('File_exceeds_10MB_size_limit') }}"></span>
    <span id="imageUploadMaxSize" data-max-size="{{ getFileUploadMaxSize() }}"></span>
    <span class="d-none" id="text-validate-translate"
          data-required="{{ translate('this_field_is_required') }}"
          data-file-size-larger="{{ translate('file_size_is_larger') }}"
          data-max-limit-crossed="{{ translate('max_limit_crossed') }}"
          data-something-went-wrong="{{ translate('something_went_wrong!') }}"
          data-passwords-do-not-match="{{ translate('passwords_do_not_match') }}"
          data-valid-email="{{ translate('please_enter_a_valid_email') }}"
          data-password-validation="{{ translate('password_must_be_8+_chars_with_upper,_lower,_number_&_symbol') }}"
          data-file-type-not-allowed="{{ translate('Invalid_file_type_selected') }}"></span>
    @php($recaptcha = getWebConfig(name: 'recaptcha'))
    <span id="get-google-recaptcha-key" data-value="{{ isset($recaptcha) && $recaptcha['status'] == 1 ? $recaptcha['site_key'] : '' }}"></span>

    @include('layouts.front-end.partials._footer')
    @include('layouts.front-end.partials.modal._dynamic-modals')

    @php($whatsapp = getWebConfig(name: 'whatsapp'))
    @if (isset($whatsapp['status']) && $whatsapp['status'] == 1)
        {{-- greeting comes from the panel (Paramètres PSF → WhatsApp) --}}
        <a href="{{ psfWhatsappChatUrl() }}" target="_blank" rel="noopener" class="psf-whatsapp-float"
           aria-label="{{ translate('Chat_with_us_on_WhatsApp') }}">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    @endif
    <button class="scroltop" type="button" aria-label="{{ translate('back_to_top') }}"><i class="fas fa-arrow-up"></i></button>

    <div id="cookie-section"></div>
</div>

{{-- Pixio design scripts --}}
<script src="{{ psfDesignAsset('assets/js/jquery.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/wow/wow.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/magnific-popup/magnific-popup.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/imagesloaded/imagesloaded.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/masonry/masonry-4.2.2.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/masonry/isotope.pkgd.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/countdown/jquery.countdown.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/group-slide/group-loop.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/lightgallery/dist/lightgallery.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js') }}"></script>
<script src="{{ psfDesignAsset('assets/js/dz.carousel.js') }}"></script>
<script src="{{ psfDesignAsset('assets/js/pixio.js') }}"></script>

{{-- shop behaviour, shared with the classic design (cart, wishlist, search…) --}}
<script>
    "use strict";
    // The shop script initialises sliders from the classic design on load.
    // They are not part of this design, so empty stand-ins keep the rest of
    // that script running instead of stopping at the first missing plugin.
    ['owlCarousel', 'easyZoom'].forEach(function (name) {
        if (typeof jQuery.fn[name] !== 'function') {
            jQuery.fn[name] = function () { return this; };
        }
    });

    // Bootstrap 5 only adds its jQuery methods ($(el).modal('show')…) once the
    // page has loaded, but the shop script calls them while loading. Register
    // them now; Bootstrap replaces these with its own identical ones later.
    [['modal', 'Modal'], ['tooltip', 'Tooltip'], ['popover', 'Popover'], ['collapse', 'Collapse'],
        ['dropdown', 'Dropdown'], ['tab', 'Tab'], ['offcanvas', 'Offcanvas'], ['toast', 'Toast']
    ].forEach(function (pair) {
        const Plugin = window.bootstrap && window.bootstrap[pair[1]];
        if (Plugin && typeof jQuery.fn[pair[0]] !== 'function') {
            jQuery.fn[pair[0]] = function (config) {
                return this.each(function () {
                    const instance = Plugin.getOrCreateInstance(this, typeof config === 'object' ? config : {});
                    if (typeof config === 'string' && typeof instance[config] === 'function') {
                        instance[config]();
                    }
                });
            };
        }
    });
</script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/toastr.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/front-end/js/sweet_alert.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/front-end/js/custom.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/utils.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInout-validation.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/polyfills.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/just-validate.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/form-advance-validation.js') }}"></script>

{!! Toastr::message() !!}

@include('layouts.front-end.partials._firebase-script')

@if (isset($recaptcha) && $recaptcha['status'] == 1)
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptcha['site_key'] }}"></script>
@endif
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.js') }}"></script>
<script src="{{ psfDesignAsset('assets/js/psf-pixio.js') }}"></script>

<script>
    "use strict";

    @if ($errors->any())
    @foreach ($errors->all() as $error)
    toastr.error({!! json_encode($error) !!}, {CloseButton: true, ProgressBar: true});
    @endforeach
    @endif

    $(document).mouseup(function (e) {
        let container = $(".search-card");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });

    function route_alert(route, message) {
        Swal.fire({
            title: {!! json_encode(translate('are_you_sure') . '?') !!},
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--psf-primary').trim(),
            cancelButtonText: {!! json_encode(translate('no')) !!},
            confirmButtonText: {!! json_encode(translate('yes')) !!},
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = route;
            }
        })
    }

    @php($cookie = $web_config['cookie_setting'] ? json_decode($web_config['cookie_setting']['value'], true) : null)
    @if ($cookie && ($cookie['status'] ?? 0) == 1)
    let cookieContent = `
        <div class="psf-cookie">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h6 class="mb-1">{{ translate('Your_Privacy_Matter') }}</h6>
                        <p class="mb-0">{{ $cookie['cookie_text'] ?? '' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="cookie-reject">{{ translate('no_thanks') }}</button>
                        <button class="btn btn-secondary btn-sm" id="cookie-accept">{{ translate('i_Accept') }}</button>
                    </div>
                </div>
            </div>
        </div>`;
    $(document).on('click', '#cookie-accept', function () {
        document.cookie = '6valley_cookie_consent=accepted; max-age=' + 60 * 60 * 24 * 30;
        $('#cookie-section').hide();
    });
    $(document).on('click', '#cookie-reject', function () {
        document.cookie = '6valley_cookie_consent=reject; max-age=' + 60 * 60 * 24;
        $('#cookie-section').hide();
    });
    $(document).ready(function () {
        if (document.cookie.indexOf("6valley_cookie_consent=") === -1) {
            $('#cookie-section').html(cookieContent).show();
        }
    });
    @endif
</script>

@stack('script')
</body>
</html>
