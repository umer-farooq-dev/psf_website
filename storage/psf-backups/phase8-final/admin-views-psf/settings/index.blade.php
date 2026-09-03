@extends('layouts.admin.app')

@section('title', translate('PSF_Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-20">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('PSF_Settings') }}
            </h2>
            <p class="mb-0 fs-12">{{ translate('All_PSF_options_are_managed_here_nothing_is_fixed_in_the_code') }}</p>
        </div>

        <form action="{{ route('admin.psf-settings.update') }}" method="post">
            @csrf

            {{-- WhatsApp --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>WhatsApp</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('The_number_that_receives_price_requests_and_orders') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="whatsapp_number">
                                    {{ translate('WhatsApp_Number') }}
                                    <small class="text-muted">({{ translate('country_code_without_plus') }} : 22670000000)</small>
                                </label>
                                <input type="text" class="form-control" id="whatsapp_number"
                                       name="whatsapp_number" value="{{ $whatsappNumber }}"
                                       placeholder="22670000000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="chat_greeting">
                                    {{ translate('Floating_Button_Message') }}
                                </label>
                                <input type="text" class="form-control" id="chat_greeting"
                                       name="chat_greeting" value="{{ $chatGreeting }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message templates --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('WhatsApp_Message_Templates') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Use') }} <code>{product}</code> {{ translate('and') }} <code>{url}</code> —
                            {{ translate('they_are_replaced_by_the_product_name_and_its_link') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="price_request_template">
                                    {{ translate('Price_Request_products_without_a_price') }}
                                </label>
                                <textarea class="form-control" id="price_request_template" rows="4"
                                          name="price_request_template">{{ $priceTemplate }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="order_request_template">
                                    {{ translate('Order_Request_products_with_a_price') }}
                                </label>
                                <textarea class="form-control" id="order_request_template" rows="4"
                                          name="order_request_template">{{ $orderTemplate }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quote requests --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Quote_Requests') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Where_new_quote_requests_are_sent_and_which_client_types_are_offered') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="quote_email">
                                    {{ translate('Notification_Email') }}
                                </label>
                                <input type="email" class="form-control" id="quote_email"
                                       name="quote_email" value="{{ $quoteRecipients['email'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="quote_whatsapp">
                                    {{ translate('Forwarding_WhatsApp_Number') }}
                                </label>
                                <input type="text" class="form-control" id="quote_whatsapp"
                                       name="quote_whatsapp" value="{{ $quoteRecipients['whatsapp'] ?? '' }}">
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-3">
                                    <div>
                                        <div class="fw-medium text-dark fs-14 mb-1">
                                            {{ translate('Show_Quote_Request_in_the_site_menu') }}
                                        </div>
                                        <p class="mb-0 fs-12">
                                            {{ translate('Adds_the_link_to_the_top_menu_and_the_footer') }}
                                        </p>
                                    </div>
                                    <label class="switcher" for="quote_menu">
                                        <input class="switcher_input" type="checkbox" value="1"
                                               name="quote_menu" id="quote_menu" {{ $quoteMenu ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ translate('Client_Types') }}</label>
                                <div id="psf-client-types">
                                    @foreach ($clientTypes as $type)
                                        <div class="row g-2 mb-2 psf-client-type-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="client_type_key[]"
                                                       value="{{ $type['key'] }}"
                                                       placeholder="{{ translate('key') }} (ex: revendeur)">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="client_type_label[]"
                                                       value="{{ $type['label'] }}"
                                                       placeholder="{{ translate('label') }} (ex: Revendeur)">
                                            </div>
                                            <div class="col-md-1 d-grid">
                                                <button type="button"
                                                        class="btn btn-outline-danger psf-remove-row">&times;</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="psf-add-client-type">
                                    + {{ translate('Add') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Admin menu visibility --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Admin_Menu_Visibility') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Tick_a_menu_to_hide_it_from_the_sidebar_Nothing_is_deleted_it_can_be_shown_again_at_any_time') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            @foreach ($menuOptions as $menuKey)
                                <div class="col-md-4 col-sm-6">
                                    <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-2">
                                        <span class="fs-14 text-dark">{{ translate($menuKey) }}</span>
                                        <label class="switcher" for="menu-{{ $menuKey }}">
                                            <input class="switcher_input" type="checkbox"
                                                   value="{{ $menuKey }}" name="hidden_menus[]"
                                                   id="menu-{{ $menuKey }}"
                                                   {{ in_array($menuKey, $hiddenMenus, true) ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        $(document).on('click', '#psf-add-client-type', function () {
            $('#psf-client-types').append(
                '<div class="row g-2 mb-2 psf-client-type-row">' +
                '<div class="col-md-5"><input type="text" class="form-control" name="client_type_key[]" placeholder="{{ translate('key') }}"></div>' +
                '<div class="col-md-6"><input type="text" class="form-control" name="client_type_label[]" placeholder="{{ translate('label') }}"></div>' +
                '<div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger psf-remove-row">&times;</button></div>' +
                '</div>'
            );
        });

        $(document).on('click', '.psf-remove-row', function () {
            if ($('.psf-client-type-row').length > 1) {
                $(this).closest('.psf-client-type-row').remove();
            }
        });
    </script>
@endpush
