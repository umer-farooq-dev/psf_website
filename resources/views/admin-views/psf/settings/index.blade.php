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

        {{-- Storefront design — its own form, so saving it never resets the
             rest of this page (and saving the rest never resets the design). --}}
        <form action="{{ route('admin.psf-settings.design') }}" method="post" class="mb-3">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Site_Design') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Choose_the_storefront_design_Switching_back_is_instant_and_nothing_is_lost') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            @foreach (['classic' => 'Classic_design', 'pixio' => 'New_design_Pixio'] as $designKey => $designLabel)
                                <div class="col-md-6">
                                    <label class="border rounded p-3 bg-white d-flex align-items-center gap-3 h-100 mb-0 cursor-pointer"
                                           for="design-{{ $designKey }}">
                                        <input type="radio" class="form-check-input mt-0" name="design"
                                               id="design-{{ $designKey }}" value="{{ $designKey }}"
                                            {{ $design === $designKey ? 'checked' : '' }}>
                                        <span class="fw-medium text-dark fs-14">{{ translate($designLabel) }}</span>
                                        @if ($design === $designKey)
                                            <span class="badge badge-soft-success ms-auto">{{ translate('Active') }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach

                            <div class="col-12">
                                <label class="form-label mb-2">{{ translate('Design_Colours') }}</label>
                                <p class="fs-12 text-muted">{{ translate('Used_by_the_new_design_buttons_links_highlights_and_backgrounds') }}</p>
                                <div class="row g-3">
                                    @foreach ([
                                        'primary'    => 'Primary_colour',
                                        'secondary'  => 'Buttons_colour',
                                        'title'      => 'Headings_colour',
                                        'light'      => 'Page_background',
                                        'light_dark' => 'Highlight_background',
                                        'body_text'  => 'Text_colour',
                                        'border'     => 'Borders_colour',
                                        'whatsapp'   => 'WhatsApp_button_colour',
                                    ] as $colorKey => $colorLabel)
                                        <div class="col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label fs-12" for="pixio-color-{{ $colorKey }}">{{ translate($colorLabel) }}</label>
                                            <div class="d-flex gap-2">
                                                <input type="color" class="form-control form-control-color psf-color-picker"
                                                       data-target="#pixio-color-{{ $colorKey }}"
                                                       value="{{ $pixioColors[$colorKey] }}">
                                                <input type="text" class="form-control" id="pixio-color-{{ $colorKey }}"
                                                       name="colors[{{ $colorKey }}]" value="{{ $pixioColors[$colorKey] }}"
                                                       pattern="^#[0-9a-fA-F]{6}$">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- New design content: its own form, so saving it never touches other settings --}}
        <form action="{{ route('admin.psf-settings.design-content') }}" method="post" enctype="multipart/form-data" class="mb-3">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('New_Design_Content') }}</h3>
                        <p class="mb-0 fs-12">{{ translate('Footer_links_and_images_used_by_the_new_design') }}</p>
                    </div>

                    <div class="bg-section-sm mb-3">
                        <label class="form-label mb-1">{{ translate('Footer_Link_Columns') }}</label>
                        <p class="fs-12 text-muted">
                            {{ translate('For_custom_links_write_one_link_per_line') }} : <code>{{ translate('Label') }} | https://…</code>
                        </p>
                        <div class="row g-3">
                            @for ($columnIndex = 0; $columnIndex < 3; $columnIndex++)
                                @php($column = $footerColumns[$columnIndex] ?? ['title' => '', 'type' => 'custom', 'links' => []])
                                @php($storedColumn = $footerColumnsRaw[$columnIndex] ?? ['title' => '', 'links' => []])
                                <div class="col-lg-4">
                                    <div class="border rounded p-3 bg-white h-100">
                                        <label class="form-label fs-12" for="footer-type-{{ $columnIndex }}">
                                            {{ translate('Column') }} {{ $columnIndex + 1 }}
                                        </label>
                                        <select class="form-control mb-2 psf-footer-type" id="footer-type-{{ $columnIndex }}"
                                                name="footer_type[{{ $columnIndex }}]" data-links="#footer-links-{{ $columnIndex }}">
                                            @foreach (['categories' => 'Shop_categories', 'pages' => 'Business_pages', 'site' => 'Site_links', 'custom' => 'Custom_links'] as $typeKey => $typeLabel)
                                                <option value="{{ $typeKey }}" {{ $column['type'] === $typeKey ? 'selected' : '' }}>{{ translate($typeLabel) }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mb-2">
                                            @include('admin-views.psf.partials._lang-input', [
                                                'name' => 'footer_title[' . $columnIndex . ']',
                                                'values' => $storedColumn['title'],
                                                'placeholder' => translate('Title_leave_empty_for_default'),
                                            ])
                                        </div>
                                        <div id="footer-links-{{ $columnIndex }}" class="{{ $column['type'] === 'custom' ? '' : 'd-none' }}">
                                            @include('admin-views.psf.partials._lang-input', [
                                                'name' => 'footer_links[' . $columnIndex . ']',
                                                'values' => $storedColumn['links'],
                                                'textarea' => 5,
                                                'placeholder' => translate('Label') . ' | https://…',
                                            ])
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-section-sm h-100">
                                <label class="form-label mb-1" for="payment_image">{{ translate('We_accept_image') }}</label>
                                <p class="fs-12 text-muted">{{ translate('Shown_at_the_bottom_of_the_footer_eg_payment_methods') }}</p>
                                @if ($paymentImage)
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <img src="{{ $paymentImage }}" alt="" class="border rounded bg-white p-1" style="max-height: 40px;">
                                        <label class="d-flex align-items-center gap-2 mb-0 fs-12">
                                            <input type="checkbox" class="form-check-input mt-0" name="remove_payment_image" value="1">
                                            {{ translate('remove') }}
                                        </label>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="payment_image" name="payment_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-section-sm h-100">
                                <label class="form-label mb-1" for="menu_image">{{ translate('Shop_menu_image') }}</label>
                                <p class="fs-12 text-muted">{{ translate('Shown_on_the_right_of_the_shop_menu') }} ({{ translate('recommended_size') }} 300 × 420 px)</p>
                                @if ($menuImage)
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <img src="{{ $menuImage['image'] }}" alt="" class="border rounded bg-white p-1" style="max-height: 60px;">
                                        <label class="d-flex align-items-center gap-2 mb-0 fs-12">
                                            <input type="checkbox" class="form-check-input mt-0" name="remove_menu_image" value="1">
                                            {{ translate('remove') }}
                                        </label>
                                    </div>
                                @endif
                                <input type="file" class="form-control mb-2" id="menu_image" name="menu_image" accept=".jpg,.jpeg,.png,.webp">
                                <input type="text" class="form-control" name="menu_image_url" value="{{ $menuImageUrl }}"
                                       placeholder="{{ translate('Link_optional') }} : https://… {{ translate('or') }} /products"
                                       aria-label="{{ translate('Link_optional') }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="bg-section-sm">
                                <label class="form-label mb-1">{{ translate('Homepage_video') }}</label>
                                <p class="fs-12 text-muted">{{ translate('Upload_a_video_or_paste_a_YouTube_or_Vimeo_link_The_uploaded_video_is_used_first') }}</p>
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="video_file">{{ translate('Video_file') }} (MP4, WebM)</label>
                                        @if (!empty($homeVideoRaw['file']))
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <a href="{{ $homeVideo['url'] ?? '#' }}" target="_blank" rel="noopener" class="fs-12">{{ $homeVideoRaw['file'] }}</a>
                                                <label class="d-flex align-items-center gap-2 mb-0 fs-12">
                                                    <input type="checkbox" class="form-check-input mt-0" name="remove_video_file" value="1">
                                                    {{ translate('remove') }}
                                                </label>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="video_file" name="video_file" accept=".mp4,.webm">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="video_link">{{ translate('Video_link') }}</label>
                                        <input type="text" class="form-control" id="video_link" name="video_link"
                                               value="{{ $homeVideoRaw['link'] ?? '' }}" placeholder="https://www.youtube.com/watch?v=…">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="video_background">{{ translate('Video_background_image') }} <small class="text-muted">({{ translate('recommended_size') }} 1920 × 830 px)</small></label>
                                        @if (!empty($homeVideo['background']))
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <img src="{{ $homeVideo['background'] }}" alt="" class="border rounded bg-white p-1" style="max-height: 60px;">
                                                <label class="d-flex align-items-center gap-2 mb-0 fs-12">
                                                    <input type="checkbox" class="form-check-input mt-0" name="remove_video_background" value="1">
                                                    {{ translate('remove') }}
                                                </label>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="video_background" name="video_background" accept=".jpg,.jpeg,.png,.webp">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            @php($shopPage = psfShopSettings())
                            <div class="bg-section-sm">
                                <label class="form-label mb-1">{{ translate('Shop_page') }}</label>
                                <p class="fs-12 text-muted">{{ translate('Product_lists_banner_display_and_products_per_page') }}</p>
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="shop_banner">{{ translate('Page_banner_image') }} <small class="text-muted">({{ translate('recommended_size') }} 1920 × 600 px)</small></label>
                                        @if ($shopPage['banner'])
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <img src="{{ $shopPage['banner'] }}" alt="" class="border rounded bg-white p-1" style="max-height: 60px;">
                                                <label class="d-flex align-items-center gap-2 mb-0 fs-12">
                                                    <input type="checkbox" class="form-check-input mt-0" name="remove_shop_banner" value="1">
                                                    {{ translate('remove') }}
                                                </label>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="shop_banner" name="shop_banner" accept=".jpg,.jpeg,.png,.webp">
                                        <p class="fs-12 text-muted mb-0 mt-1">{{ translate('Also_used_on_the_other_inner_pages_Without_image_the_banner_uses_the_light_colour') }}</p>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="shop_view">{{ translate('Default_product_display') }}</label>
                                        <select class="form-control" id="shop_view" name="shop_view">
                                            @foreach (PSF_SHOP_VIEWS as $viewKey)
                                                <option value="{{ $viewKey }}" {{ $shopPage['view'] === $viewKey ? 'selected' : '' }}>{{ translate('shop_view_' . $viewKey) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label fs-12" for="shop_per_page">{{ translate('Products_per_page_choices') }}</label>
                                        <input type="text" class="form-control mb-2" id="shop_per_page" name="shop_per_page"
                                               value="{{ implode(', ', $shopPage['per_page']) }}" placeholder="12, 20, 40">
                                        <label class="form-label fs-12" for="shop_per_page_default">{{ translate('Products_per_page_by_default') }}</label>
                                        <input type="number" min="1" max="200" class="form-control" id="shop_per_page_default" name="shop_per_page_default"
                                               value="{{ $shopPage['per_page_default'] }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Site texts of the new design, every language (language keys) --}}
        <form action="{{ route('admin.psf-settings.texts') }}" method="post" class="mb-3">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Site_Texts') }}</h3>
                        <p class="mb-0 fs-12">{{ translate('Headings_menu_labels_and_buttons_of_the_site_in_every_language') }}</p>
                    </div>
                    @foreach ($siteTexts as $group => $texts)
                        <div class="bg-section-sm mb-3">
                            <label class="form-label mb-3">{{ translate($group) }}</label>
                            <div class="row g-3">
                                @foreach ($texts as $key => $values)
                                    <div class="col-lg-4 col-md-6">
                                        <label class="form-label fs-12 text-muted mb-1"><code>{{ $key }}</code></label>
                                        @include('admin-views.psf.partials._lang-input', ['name' => 'texts[' . $key . ']', 'values' => $values])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                    </div>
                </div>
            </div>
        </form>

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
                                    @foreach ($clientTypes as $typeIndex => $type)
                                        <div class="row g-2 mb-2 psf-client-type-row">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="client_type_key[{{ $typeIndex }}]"
                                                       value="{{ $type['key'] }}"
                                                       placeholder="{{ translate('key') }} (ex: revendeur)">
                                            </div>
                                            <div class="col-md-6">
                                                @include('admin-views.psf.partials._lang-input', [
                                                    'name' => 'client_type_label[' . $typeIndex . ']',
                                                    'values' => $type['label'],
                                                    'placeholder' => translate('label'),
                                                ])
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

            {{-- Brand --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Brand_Identity') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Slogan_accent_colour_and_Google_page') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">{{ translate('Slogan') }}</label>
                                @include('admin-views.psf.partials._lang-input', ['name' => 'slogan', 'values' => $slogan])
                                <small class="text-muted">{{ translate('Shown_in_the_footer_under_the_logo') }}</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="accent_color">
                                    {{ translate('Accent_Colour') }}
                                </label>
                                <div class="d-flex gap-2">
                                    <input type="color" class="form-control form-control-color"
                                           id="accent_color_picker" value="{{ $accentColor }}"
                                           title="{{ translate('Accent_Colour') }}">
                                    <input type="text" class="form-control" id="accent_color"
                                           name="accent_color" value="{{ $accentColor }}" placeholder="#f5c518">
                                </div>
                                <small class="text-muted">
                                    {{ translate('Used_for_the_most_important_buttons') }}
                                </small>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label" for="google_business_url">
                                    {{ translate('Google_Business_Profile_Link') }}
                                </label>
                                <input type="text" class="form-control" id="google_business_url"
                                       name="google_business_url" value="{{ $googleBusinessUrl }}"
                                       placeholder="https://...">
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-2 h-100">
                                    <div>
                                        <div class="fw-medium text-dark fs-14 mb-1">
                                            {{ translate('Show_About_us_in_the_menu') }}
                                        </div>
                                    </div>
                                    <label class="switcher" for="about_menu">
                                        <input class="switcher_input" type="checkbox" value="1"
                                               name="about_menu" id="about_menu" {{ $aboutMenu ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="note-inline fs-12 text-muted">
                                    {{ translate('Google_Analytics_is_set_up_in_Third_Party_Analytics') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Search_Engines') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Controls_whether_Google_may_list_the_site') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-medium text-dark fs-14 mb-1">
                                    {{ translate('Allow_search_engines_to_index_the_site') }}
                                </div>
                                <p class="mb-0 fs-12">
                                    {{ translate('Turn_this_off_only_while_the_site_is_still_being_built_Off_means_the_shop_will_not_appear_in_Google') }}
                                </p>
                            </div>
                            <label class="switcher" for="search_indexing">
                                <input class="switcher_input" type="checkbox" value="1"
                                       name="search_indexing" id="search_indexing"
                                    {{ $searchIndexing ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Homepage --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Home_Page') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Buttons_under_the_slider_and_which_blocks_appear_on_the_home_page') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    {{ translate('Buttons_under_the_slider') }}
                                    <small class="text-muted">({{ translate('up_to_three') }})</small>
                                </label>
                                @for ($psfCtaRow = 0; $psfCtaRow < 3; $psfCtaRow++)
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-5">
                                            @include('admin-views.psf.partials._lang-input', [
                                                'name' => 'cta_label[' . $psfCtaRow . ']',
                                                'values' => $heroCtas[$psfCtaRow]['label'] ?? psfTextArray(''),
                                                'placeholder' => translate('Label'),
                                            ])
                                        </div>
                                        <div class="col-md-7">
                                            <input type="text" class="form-control" name="cta_url[{{ $psfCtaRow }}]"
                                                   value="{{ $heroCtas[$psfCtaRow]['url'] ?? '' }}"
                                                   placeholder="{{ route('psf.quote.index') }}">
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ translate('Home_Page_Blocks') }}</label>
                                <p class="fs-12 text-muted">
                                    {{ translate('Tick_a_block_to_hide_it_from_the_home_page') }}
                                </p>
                                <div class="row g-3">
                                    @foreach ($homeSections as $sectionKey)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-2">
                                                <span class="fs-14 text-dark">{{ translate($sectionKey) }}</span>
                                                <label class="switcher" for="home-{{ $sectionKey }}">
                                                    <input class="switcher_input" type="checkbox"
                                                           value="{{ $sectionKey }}" name="hidden_home_sections[]"
                                                           id="home-{{ $sectionKey }}"
                                                        {{ in_array($sectionKey, $hiddenSections, true) ? 'checked' : '' }}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact page --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Contact_Page') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('Address_phone_numbers_opening_hours_and_map_shown_on_the_contact_page') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="contact_address">{{ translate('address') }}</label>
                                <input type="text" class="form-control" id="contact_address" name="contact_address"
                                       value="{{ $contactAddress }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ translate('Phone_Numbers') }}</label>
                                <div id="psf-phones">
                                    @foreach ($contactPhones as $index => $phone)
                                        <div class="row g-2 mb-2 psf-phone-row align-items-center">
                                            <div class="col-md-4">
                                                @include('admin-views.psf.partials._lang-input', [
                                                    'name' => 'phone_label[' . $index . ']',
                                                    'values' => $phone['label'],
                                                    'placeholder' => translate('Label'),
                                                ])
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="phone_number[{{ $index }}]"
                                                       value="{{ $phone['number'] }}"
                                                       placeholder="+226 70 00 00 00">
                                            </div>
                                            {{-- a select, not a checkbox: an unticked box submits nothing
                                                 and the rows would stop lining up with phone_number[] --}}
                                            <div class="col-md-3">
                                                <select class="form-control" name="phone_whatsapp[{{ $index }}]">
                                                    <option value="0" {{ $phone['whatsapp'] ? '' : 'selected' }}>
                                                        {{ translate('Without_WhatsApp') }}
                                                    </option>
                                                    <option value="1" {{ $phone['whatsapp'] ? 'selected' : '' }}>
                                                        {{ translate('With_WhatsApp') }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-1 d-grid">
                                                <button type="button"
                                                        class="btn btn-outline-danger psf-remove-phone">&times;</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="psf-add-phone">
                                    + {{ translate('Add') }}
                                </button>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    {{ translate('Opening_Hours') }}
                                    <small class="text-muted">({{ translate('leave_empty_to_show_Closed') }})</small>
                                </label>
                                @foreach ($openingHours as $dayIndex => $day)
                                    <div class="row g-2 mb-3 align-items-start">
                                        <div class="col-md-4">
                                            @include('admin-views.psf.partials._lang-input', [
                                                'name' => 'hours_day[' . $dayIndex . ']',
                                                'values' => $day['day'],
                                            ])
                                        </div>
                                        <div class="col-md-8">
                                            @include('admin-views.psf.partials._lang-input', [
                                                'name' => 'hours_value[' . $dayIndex . ']',
                                                'values' => $day['closed'] ? psfTextArray('') : $day['hours'],
                                                'placeholder' => '07:30 - 18:00',
                                            ])
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="map_embed">
                                    {{ translate('Google_Maps') }}
                                    <small class="text-muted">({{ translate('paste_the_embed_code_or_the_link_from_Google_Maps') }})</small>
                                </label>
                                <textarea class="form-control" id="map_embed" name="map_embed"
                                          rows="3">{{ $mapEmbed }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="map_directions">
                                    {{ translate('Directions_Link') }}
                                    <small class="text-muted">({{ translate('optional_built_from_the_address_if_left_empty') }})</small>
                                </label>
                                <input type="text" class="form-control" id="map_directions" name="map_directions"
                                       value="{{ $mapDirections }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gallery --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3 mb-sm-20">
                        <h3>{{ translate('Our_Realisations') }}</h3>
                        <p class="mb-0 fs-12">
                            {{ translate('The_public_gallery_of_completed_projects') }}
                        </p>
                    </div>
                    <div class="bg-section-sm">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">
                                    {{ translate('Project_Categories') }}
                                    <small class="text-muted">({{ translate('separated_by_commas_same_order_in_every_language') }})</small>
                                </label>
                                @include('admin-views.psf.partials._lang-input', [
                                    'name' => 'gallery_categories',
                                    'values' => $galleryCategories,
                                ])
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="gallery_per_page">
                                    {{ translate('Projects_per_page') }}
                                </label>
                                <input type="number" class="form-control" id="gallery_per_page"
                                       name="gallery_per_page" min="1" max="60" value="{{ $galleryPerPage }}">
                            </div>

                            <div class="col-12">
                                <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center gap-3">
                                    <div>
                                        <div class="fw-medium text-dark fs-14 mb-1">
                                            {{ translate('Show_Our_Realisations_in_the_site_menu') }}
                                        </div>
                                        <p class="mb-0 fs-12">
                                            {{ translate('Adds_the_link_to_the_top_menu_and_the_footer') }}
                                        </p>
                                    </div>
                                    <label class="switcher" for="gallery_menu">
                                        <input class="switcher_input" type="checkbox" value="1"
                                               name="gallery_menu" id="gallery_menu" {{ $galleryMenu ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
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

        // one input per site language for a text of a new row
        const psfLanguages = @json(psfLanguages());
        const psfEscape = (text) => $('<div>').text(text).html();
        const psfLangInputs = function (name, placeholder) {
            return '<div class="d-flex flex-column gap-1 psf-lang-input">' + psfLanguages.map(function (language) {
                return '<div class="input-group">' +
                    '<span class="input-group-text text-uppercase fs-12 fw-semibold" title="' + psfEscape(language.name) + '">' + psfEscape(language.code) + '</span>' +
                    '<input type="text" class="form-control" name="' + name + '[' + language.code + ']" placeholder="' + psfEscape(placeholder || '') + '">' +
                    '</div>';
            }).join('') + '</div>';
        };
        let psfRowIndex = Date.now();

        $(document).on('click', '#psf-add-client-type', function () {
            const index = psfRowIndex++;
            $('#psf-client-types').append(
                '<div class="row g-2 mb-2 psf-client-type-row">' +
                '<div class="col-md-5"><input type="text" class="form-control" name="client_type_key[' + index + ']" placeholder="{{ translate('key') }}"></div>' +
                '<div class="col-md-6">' + psfLangInputs('client_type_label[' + index + ']', @json(translate('label'))) + '</div>' +
                '<div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger psf-remove-row">&times;</button></div>' +
                '</div>'
            );
        });

        // design colours: each picker writes into its hex field
        $(document).on('input', '.psf-color-picker', function () {
            $($(this).data('target')).val($(this).val());
        });

        // footer columns: the link list is only typed for "custom" columns
        $(document).on('change', '.psf-footer-type', function () {
            $($(this).data('links')).toggleClass('d-none', $(this).val() !== 'custom');
        });

        // keep the colour picker and the hex field showing the same value
        $(document).on('input', '#accent_color_picker', function () {
            $('#accent_color').val($(this).val());
        });
        $(document).on('input', '#accent_color', function () {
            var value = $(this).val();
            if (/^#[0-9a-fA-F]{6}$/.test(value)) {
                $('#accent_color_picker').val(value);
            }
        });

        $(document).on('click', '#psf-add-phone', function () {
            const index = psfRowIndex++;
            $('#psf-phones').append(
                '<div class="row g-2 mb-2 psf-phone-row align-items-center">' +
                '<div class="col-md-4">' + psfLangInputs('phone_label[' + index + ']', @json(translate('Label'))) + '</div>' +
                '<div class="col-md-4"><input type="text" class="form-control" name="phone_number[' + index + ']" placeholder="+226 70 00 00 00"></div>' +
                '<div class="col-md-3"><select class="form-control" name="phone_whatsapp[' + index + ']">' +
                '<option value="0">{{ translate('Without_WhatsApp') }}</option>' +
                '<option value="1">{{ translate('With_WhatsApp') }}</option>' +
                '</select></div>' +
                '<div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger psf-remove-phone">&times;</button></div>' +
                '</div>'
            );
        });

        $(document).on('click', '.psf-remove-phone', function () {
            $(this).closest('.psf-phone-row').remove();
        });

        $(document).on('click', '.psf-remove-row', function () {
            if ($('.psf-client-type-row').length > 1) {
                $(this).closest('.psf-client-type-row').remove();
            }
        });
    </script>
@endpush
