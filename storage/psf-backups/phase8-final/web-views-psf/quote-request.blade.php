@extends('layouts.front-end.app')

@section('title', translate('Request_a_Quote'))

@section('content')
    <div class="container my-4 rtl text-align-direction">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="text-center mb-4">
                    <h2 class="mb-2">{{ translate('Request_a_Quote') }}</h2>
                    <p class="text-muted mb-0">
                        {{ translate('tell_us_what_you_need_and_we_will_get_back_to_you_quickly') }}
                    </p>
                </div>

                <div class="card card-body">
                    <form action="{{ route('psf.quote.store') }}" method="POST" enctype="multipart/form-data"
                          id="psf-quote-form">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="quote-name">
                                    {{ translate('Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="quote-name" name="name"
                                       value="{{ old('name') }}" required maxlength="191"
                                       placeholder="{{ translate('your_name') }}">
                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="quote-phone">
                                    {{ translate('Phone') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="quote-phone" name="phone"
                                       value="{{ old('phone') }}" required maxlength="40"
                                       placeholder="+226 ...">
                                @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="quote-whatsapp">WhatsApp</label>
                                <input type="text" class="form-control" id="quote-whatsapp" name="whatsapp"
                                       value="{{ old('whatsapp') }}" maxlength="40" placeholder="+226 ...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="quote-email">
                                    {{ translate('Email') }}
                                    <small class="text-muted">({{ translate('optional') }})</small>
                                </label>
                                <input type="email" class="form-control" id="quote-email" name="email"
                                       value="{{ old('email') }}" maxlength="191"
                                       placeholder="exemple@exemple.com">
                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="quote-client-type">
                                    {{ translate('Client_Type') }}
                                </label>
                                <select class="form-control" id="quote-client-type" name="client_type">
                                    <option value="">{{ translate('Choose') }}</option>
                                    @foreach ($clientTypes as $type)
                                        <option value="{{ $type['key'] }}"
                                            {{ old('client_type') === $type['key'] ? 'selected' : '' }}>
                                            {{ $type['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="quote-quantity">
                                    {{ translate('Quantity') }}
                                </label>
                                <input type="text" class="form-control" id="quote-quantity" name="quantity"
                                       value="{{ old('quantity') }}" maxlength="60"
                                       placeholder="{{ translate('e_g_50_metres') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="quote-product">
                                    {{ translate('Product_Sought') }}
                                </label>
                                <input type="text" class="form-control" id="quote-product" name="product_sought"
                                       value="{{ old('product_sought') }}" maxlength="500"
                                       placeholder="{{ translate('e_g_tube_ppr_25_mm') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="quote-message">{{ translate('Message') }}</label>
                                <textarea class="form-control" id="quote-message" name="message" rows="4"
                                          maxlength="5000"
                                          placeholder="{{ translate('type_your_message_here') }}">{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="quote-attachment">
                                    {{ translate('Attachment') }}
                                    <small class="text-muted">
                                        ({{ translate('optional') }} — jpg, png, webp, pdf, doc — {{ translate('Max_5_MB') }})
                                    </small>
                                </label>
                                <input type="file" class="form-control" id="quote-attachment" name="attachment"
                                       accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                                @error('attachment')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            {{-- spam protection, same setup as the contact form --}}
                            @if (isset($recaptcha) && $recaptcha['status'] == 1)
                                <div class="col-12">
                                    <div id="recaptcha_element_quote" class="w-100" data-type="image"></div>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label class="form-label" for="quote-captcha">
                                        {{ translate('Captcha') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" class="form-control" id="quote-captcha"
                                               name="default_captcha_value" required
                                               placeholder="{{ translate('Enter_captcha_value') }}">
                                        <img src="{{ URL('/contact/code/captcha/quote') }}"
                                             id="psf_quote_captcha" class="input-field __h-40" alt="captcha">
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <button type="submit" class="btn btn--primary px-4">
                                    {{ translate('SEND_MY_REQUEST') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        @if (isset($recaptcha) && $recaptcha['status'] == 1)
        function onloadPsfQuoteCaptcha() {
            grecaptcha.render('recaptcha_element_quote', {
                'sitekey': '{{ $recaptcha['site_key'] }}',
            });
        }
        @endif
    </script>
    @if (isset($recaptcha) && $recaptcha['status'] == 1)
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadPsfQuoteCaptcha&render=explicit" async defer></script>
    @endif
@endpush
