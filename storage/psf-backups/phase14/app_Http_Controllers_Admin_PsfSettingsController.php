<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * PSF — one panel page for every PSF-specific option.
 *
 * Nothing PSF adds is hardcoded: WhatsApp templates, quote recipients,
 * client types and admin-menu visibility all live in `business_settings`
 * and are edited from here.
 */
class PsfSettingsController extends BaseController
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {
    }

    public function index(?Request $request = null, ?string $type = null): View
    {
        return view('admin-views.psf.settings.index', [
            'whatsappNumber'   => psfWhatsappNumber(),
            'chatGreeting'     => psfWhatsappTemplate('psf_chat_greeting', ''),
            'priceTemplate'    => psfWhatsappTemplate('psf_price_request_template', ''),
            'orderTemplate'    => psfWhatsappTemplate('psf_order_request_template', ''),
            'quoteMenu'        => psfQuoteMenu(),
            'quoteRecipients'  => psfQuoteRecipients(),
            'clientTypes'      => psfClientTypes(),
            'galleryMenu'      => psfGalleryMenu(),
            'galleryCategories' => psfGalleryCategories(),
            'galleryPerPage'   => (int)(getWebConfig(name: 'psf_gallery_per_page') ?: 12),
            'accentColor'      => psfAccentColor(),
            'slogan'           => psfSlogan(),
            'googleBusinessUrl' => psfGoogleBusinessUrl(),
            'aboutMenu'        => psfAboutMenu(),
            'searchIndexing'   => psfSearchIndexing(),
            'homeSections'     => psfHomeSectionOptions(),
            'hiddenSections'   => psfHiddenHomeSections(),
            'heroCtas'         => psfHeroCtas(),
            'contactAddress'   => psfContactAddress(),
            'contactPhones'    => psfContactPhones(),
            'openingHours'     => psfOpeningHours() ?: psfDefaultOpeningHours(),
            'mapEmbed'         => (string)(getWebConfig(name: 'psf_map_embed') ?: ''),
            'mapDirections'    => (string)(getWebConfig(name: 'psf_map_directions') ?: ''),
            'hiddenMenus'      => psfHiddenMenus(),
            'menuOptions'      => psfMenuOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'whatsapp_number'          => 'nullable|string|max:30',
            'chat_greeting'            => 'nullable|string|max:1000',
            'price_request_template'   => 'nullable|string|max:1000',
            'order_request_template'   => 'nullable|string|max:1000',
            'quote_email'              => 'nullable|email|max:191',
            'quote_whatsapp'           => 'nullable|string|max:30',
            'client_type_key'          => 'nullable|array',
            'client_type_key.*'        => 'nullable|string|max:60',
            'client_type_label'        => 'nullable|array',
            'client_type_label.*'      => 'nullable|string|max:120',
            'hidden_menus'             => 'nullable|array',
            'hidden_menus.*'           => 'nullable|string|max:60',
            'gallery_categories'       => 'nullable|string|max:1000',
            'gallery_per_page'         => 'nullable|integer|min:1|max:60',
            'contact_address'          => 'nullable|string|max:500',
            'phone_label'              => 'nullable|array',
            'phone_label.*'            => 'nullable|string|max:80',
            'phone_number'             => 'nullable|array',
            'phone_number.*'           => 'nullable|string|max:40',
            'hours_day'                => 'nullable|array',
            'hours_day.*'              => 'nullable|string|max:40',
            'hours_value'              => 'nullable|array',
            'hours_value.*'            => 'nullable|string|max:80',
            'map_embed'                => 'nullable|string|max:2000',
            'map_directions'           => 'nullable|string|max:1000',
            'hidden_home_sections'     => 'nullable|array',
            'hidden_home_sections.*'   => 'nullable|string|max:60',
            'cta_label'                => 'nullable|array',
            'cta_label.*'              => 'nullable|string|max:60',
            'cta_url'                  => 'nullable|array',
            'cta_url.*'                => 'nullable|string|max:500',
            'accent_color'             => 'nullable|string|max:9',
            'slogan'                   => 'nullable|string|max:191',
            'google_business_url'      => 'nullable|string|max:500',
        ]);

        // WhatsApp number keeps 6valley's own {status, phone} shape so the
        // core floating-button setting stays valid.
        $number = preg_replace('/\D+/', '', (string)$request['whatsapp_number']);
        if ($number !== '') {
            $existing = json_decode((string)$this->businessSettingRepo->getFirstWhere(params: ['type' => 'whatsapp'])?->value, true) ?: [];
            $this->businessSettingRepo->updateOrInsert(type: 'whatsapp', value: json_encode([
                'status' => $existing['status'] ?? 1,
                'phone'  => $number,
            ]));
        }

        $this->businessSettingRepo->updateOrInsert(type: 'psf_chat_greeting', value: (string)$request['chat_greeting']);
        $this->businessSettingRepo->updateOrInsert(type: 'psf_price_request_template', value: (string)$request['price_request_template']);
        $this->businessSettingRepo->updateOrInsert(type: 'psf_order_request_template', value: (string)$request['order_request_template']);
        $this->businessSettingRepo->updateOrInsert(type: 'psf_quote_menu', value: $request->has('quote_menu') ? '1' : '0');

        $this->businessSettingRepo->updateOrInsert(type: 'psf_quote_recipients', value: json_encode([
            'email'    => (string)$request['quote_email'],
            'whatsapp' => preg_replace('/\D+/', '', (string)$request['quote_whatsapp']),
        ]));

        // Client types: paired key/label rows, blank rows dropped.
        $types = [];
        foreach ((array)$request['client_type_key'] as $index => $key) {
            $key = trim((string)$key);
            $label = trim((string)(($request['client_type_label'][$index]) ?? ''));
            if ($key === '' || $label === '') {
                continue;
            }
            $types[] = [
                'key'   => preg_replace('/[^a-z0-9_]/', '', strtolower($key)),
                'label' => $label,
            ];
        }
        if ($types) {
            $this->businessSettingRepo->updateOrInsert(type: 'psf_client_types', value: json_encode($types));
        }

        // Brand: accent colour is stored only when it is a real hex value, so a
        // stray paste can never end up inside the page's CSS.
        $accent = trim((string)$request['accent_color']);
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $accent)) {
            $this->businessSettingRepo->updateOrInsert(type: 'psf_accent_color', value: $accent);
        }

        $this->businessSettingRepo->updateOrInsert(type: 'psf_slogan', value: trim((string)$request['slogan']));
        $this->businessSettingRepo->updateOrInsert(type: 'psf_about_menu', value: $request->has('about_menu') ? '1' : '0');

        $businessUrl = trim((string)$request['google_business_url']);
        $this->businessSettingRepo->updateOrInsert(
            type: 'psf_google_business_url',
            value: str_starts_with(strtolower($businessUrl), 'https://') ? $businessUrl : ''
        );

        $this->businessSettingRepo->updateOrInsert(
            type: 'psf_search_indexing',
            value: $request->has('search_indexing') ? '1' : '0'
        );

        // Homepage: only keys this form offers are rewritten.
        $offeredSections = psfHomeSectionOptions();
        $this->businessSettingRepo->updateOrInsert(
            type: 'psf_hidden_home_sections',
            value: json_encode(array_values(array_unique(array_merge(
                array_values(array_diff(psfHiddenHomeSections(), $offeredSections)),
                array_values(array_intersect((array)($request['hidden_home_sections'] ?? []), $offeredSections))
            ))))
        );

        // Hero buttons: a row needs both a label and a link to count.
        $ctas = [];
        foreach ((array)$request['cta_label'] as $index => $label) {
            $label = trim((string)$label);
            $url = trim((string)(($request['cta_url'][$index]) ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $ctas[] = ['label' => $label, 'url' => $url];
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_hero_ctas', value: json_encode(array_slice($ctas, 0, 3)));

        // Contact — the address is 6valley's own `shop_address`, so it stays
        // the single place PSF types it.
        if ($request->filled('contact_address')) {
            $this->businessSettingRepo->updateOrInsert(type: 'shop_address', value: trim((string)$request['contact_address']));
        }

        $phones = [];
        foreach ((array)$request['phone_number'] as $index => $number) {
            $number = trim((string)$number);
            if ($number === '') {
                continue;
            }
            $phones[] = [
                'label'    => trim((string)(($request['phone_label'][$index]) ?? '')),
                'number'   => $number,
                'whatsapp' => (bool)(($request['phone_whatsapp'][$index]) ?? false),
            ];
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_contact_phones', value: json_encode($phones));

        // Opening hours: a blank time means "Fermé", so the client can close a
        // day just by emptying the field.
        $hours = [];
        foreach ((array)$request['hours_day'] as $index => $day) {
            $day = trim((string)$day);
            if ($day === '') {
                continue;
            }
            $value = trim((string)(($request['hours_value'][$index]) ?? ''));
            $hours[] = [
                'day'    => $day,
                'hours'  => $value,
                'closed' => $value === '',
            ];
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_opening_hours', value: json_encode($hours));

        $this->businessSettingRepo->updateOrInsert(type: 'psf_map_embed', value: trim((string)$request['map_embed']));
        $this->businessSettingRepo->updateOrInsert(type: 'psf_map_directions', value: trim((string)$request['map_directions']));

        // Gallery: categories arrive as one comma-separated line — easier for
        // the client than a repeater, and blanks/duplicates are dropped.
        $this->businessSettingRepo->updateOrInsert(type: 'psf_gallery_menu', value: $request->has('gallery_menu') ? '1' : '0');
        $this->businessSettingRepo->updateOrInsert(type: 'psf_gallery_per_page', value: (string)((int)($request['gallery_per_page'] ?: 12)));

        $categories = array_values(array_unique(array_filter(array_map(
            static fn ($label) => trim((string)$label),
            explode(',', (string)$request['gallery_categories'])
        ))));
        if ($categories) {
            $this->businessSettingRepo->updateOrInsert(type: 'psf_gallery_categories', value: json_encode($categories));
        }

        // Only the keys this form offers are rewritten; any other hidden key
        // already stored (wired later, or set by hand) is kept.
        $offered = psfMenuOptions();
        $ticked = array_values(array_intersect((array)($request['hidden_menus'] ?? []), $offered));
        $untouched = array_values(array_diff(psfHiddenMenus(), $offered));

        $this->businessSettingRepo->updateOrInsert(
            type: 'psf_hidden_menus',
            value: json_encode(array_values(array_unique(array_merge($untouched, $ticked))))
        );

        Toastr::success(translate('updated_successfully'));

        return back();
    }
}
