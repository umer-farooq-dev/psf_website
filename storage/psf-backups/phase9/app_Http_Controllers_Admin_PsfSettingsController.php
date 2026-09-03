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
