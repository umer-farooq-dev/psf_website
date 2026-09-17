<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Services\PsfDesignService;
use App\Traits\SettingsTrait;
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
    use SettingsTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {
    }

    public function index(?Request $request = null, ?string $type = null): View
    {
        return view('admin-views.psf.settings.index', [
            'design'           => psfDesign(),
            'pixioColors'      => psfPixioColors(),
            'footerColumns'    => psfFooterColumns(),
            'paymentImage'     => psfFooterPaymentImage(),
            'menuImage'        => psfMenuImage(),
            'homeVideo'        => psfHomeVideo(),
            'siteTexts'        => collect(psfSiteTextKeys())->map(fn ($keys) => collect($keys)->mapWithKeys(fn ($key) => [$key => psfTranslationsOf($key)])->all())->all(),
            'homeVideoRaw'     => psfSettingArray('psf_home_video'),
            'footerColumnsRaw' => $this->footerColumnsForForm(),
            'menuImageUrl'     => (string) (psfSettingArray('psf_menu_image')['url'] ?? ''),
            'whatsappNumber'   => psfWhatsappNumber(),
            'chatGreeting'     => psfWhatsappTemplate('psf_chat_greeting', ''),
            'priceTemplate'    => psfWhatsappTemplate('psf_price_request_template', ''),
            'orderTemplate'    => psfWhatsappTemplate('psf_order_request_template', ''),
            'quoteMenu'        => psfQuoteMenu(),
            'quoteRecipients'  => psfQuoteRecipients(),
            'clientTypes'      => $this->clientTypesForForm(),
            'galleryMenu'      => psfGalleryMenu(),
            'galleryCategories' => $this->galleryCategoriesForForm(),
            'galleryPerPage'   => (int)(getWebConfig(name: 'psf_gallery_per_page') ?: 12),
            'accentColor'      => psfAccentColor(),
            'slogan'           => psfTextArray(getWebConfig(name: 'psf_slogan')),
            'googleBusinessUrl' => psfGoogleBusinessUrl(),
            'aboutMenu'        => psfAboutMenu(),
            'searchIndexing'   => psfSearchIndexing(),
            'homeSections'     => psfHomeSectionOptions(),
            'hiddenSections'   => psfHiddenHomeSections(),
            'heroCtas'         => $this->rowsForForm('psf_hero_ctas', ['label']),
            'contactAddress'   => psfContactAddress(),
            'contactPhones'    => $this->phonesForForm(),
            'openingHours'     => $this->rowsForForm('psf_opening_hours', ['day', 'hours']) ?: $this->rowsForForm(psfDefaultOpeningHours(), ['day', 'hours']),
            'mapEmbed'         => (string)(getWebConfig(name: 'psf_map_embed') ?: ''),
            'mapDirections'    => (string)(getWebConfig(name: 'psf_map_directions') ?: ''),
            'hiddenMenus'      => psfHiddenMenus(),
            'menuOptions'      => psfMenuOptions(),
        ]);
    }

    /**
     * Storefront design switch and the design's colour palette.
     *
     * The design lives in .env (PSF_FRONTEND_DESIGN) because the view folder is
     * chosen while the app boots, before settings can be read — the same way
     * 6valley stores WEB_THEME. Colours live in business_settings.
     */
    public function updateDesign(Request $request): RedirectResponse
    {
        $request->validate([
            'design'              => 'required|in:classic,pixio',
            'colors'              => 'nullable|array',
            'colors.primary'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.secondary'    => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.title'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.light'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.light_dark'   => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.body_text'    => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.border'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.whatsapp'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $colors = [];
        foreach (array_keys(psfPixioColors()) as $key) {
            $value = trim((string) ($request['colors'][$key] ?? ''));
            if ($value !== '') {
                $colors[$key] = $value;
            }
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_pixio_colors', value: json_encode($colors));

        $this->setEnvironmentValue(envKey: 'PSF_FRONTEND_DESIGN', envValue: $request['design']);

        Toastr::success(translate('updated_successfully'));

        return back();
    }

    /**
     * Content of the new design that is not a shop record: footer link
     * columns, the "we accept" image and the shop menu image.
     */
    public function updateDesignContent(Request $request, PsfDesignService $designService): RedirectResponse
    {
        $request->validate([
            'footer_type'          => 'nullable|array|max:3',
            'footer_type.*'        => 'nullable|in:' . implode(',', PsfDesignService::FOOTER_COLUMN_TYPES),
            'footer_title'         => 'nullable|array',
            'footer_title.*'       => 'nullable|array',
            'footer_title.*.*'     => 'nullable|string|max:60',
            'footer_links'         => 'nullable|array',
            'footer_links.*'       => 'nullable|array',
            'footer_links.*.*'     => 'nullable|string|max:3000',
            'payment_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'menu_image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'menu_image_url'       => 'nullable|string|max:500',
            'video_file'           => 'nullable|file|mimes:mp4,webm|max:102400',
            'video_link'           => ['nullable', 'string', 'max:500', 'regex:#^https://#i'],
            'video_background'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'shop_banner'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'shop_view'            => 'nullable|in:' . implode(',', PSF_SHOP_VIEWS),
            'shop_per_page'        => ['nullable', 'string', 'max:100', 'regex:/^[\d\s,;]*$/'],
            'shop_per_page_default' => 'nullable|integer|min:1|max:200',
        ], [
            'shop_per_page.regex' => translate('Write_numbers_separated_by_commas'),
            'video_link.regex'    => translate('The_video_link_must_start_with_https'),
            'payment_image.image' => translate('The_file_must_be_an_image'),
            'menu_image.image'    => translate('The_file_must_be_an_image'),
        ]);

        $this->businessSettingRepo->updateOrInsert(
            type: 'psf_footer_columns',
            value: json_encode($designService->footerColumns($request))
        );

        // "we accept" image: replace, remove, or keep
        $paymentImage = (string) (getWebConfig(name: 'psf_footer_payment_image') ?: '');
        $uploaded = $designService->storeImage($request, 'payment_image');
        if ($uploaded || $request->has('remove_payment_image')) {
            $designService->removeImage($paymentImage);
            $paymentImage = (string) $uploaded;
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_footer_payment_image', value: $paymentImage);

        // shop menu image and where it links to
        $menu = getWebConfig(name: 'psf_menu_image');
        $menu = is_string($menu) ? (json_decode($menu, true) ?: []) : (array) $menu;
        $menuImage = (string) ($menu['image'] ?? '');
        $uploaded = $designService->storeImage($request, 'menu_image');
        if ($uploaded || $request->has('remove_menu_image')) {
            $designService->removeImage($menuImage);
            $menuImage = (string) $uploaded;
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_menu_image', value: json_encode([
            'image' => $menuImage,
            'url'   => trim((string) $request['menu_image_url']),
        ]));

        // homepage video: uploaded file or a YouTube / Vimeo link, plus its background picture
        $video = getWebConfig(name: 'psf_home_video');
        $video = is_string($video) ? (json_decode($video, true) ?: []) : (array) $video;
        $videoFile = (string) ($video['file'] ?? '');
        $uploaded = $designService->storeVideo($request, 'video_file');
        if ($uploaded || $request->has('remove_video_file')) {
            $designService->removeImage($videoFile);
            $videoFile = (string) $uploaded;
        }
        $videoBackground = (string) ($video['background'] ?? '');
        $uploaded = $designService->storeImage($request, 'video_background');
        if ($uploaded || $request->has('remove_video_background')) {
            $designService->removeImage($videoBackground);
            $videoBackground = (string) $uploaded;
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_home_video', value: json_encode([
            'file'       => $videoFile,
            'link'       => trim((string) $request['video_link']),
            'background' => $videoBackground,
        ]));

        // shop pages: banner picture, default display, products per page
        $shopPage = psfSettingArray('psf_shop_page');
        $shopBanner = (string) ($shopPage['banner'] ?? '');
        $uploaded = $designService->storeImage($request, 'shop_banner');
        if ($uploaded || $request->has('remove_shop_banner')) {
            $designService->removeImage($shopBanner);
            $shopBanner = (string) $uploaded;
        }
        $perPage = array_values(array_unique(array_filter(
            array_map('intval', preg_split('/[\s,;]+/', (string) $request['shop_per_page'], -1, PREG_SPLIT_NO_EMPTY)),
            fn ($value) => $value > 0 && $value <= 200
        )));
        sort($perPage);
        $this->businessSettingRepo->updateOrInsert(type: 'psf_shop_page', value: json_encode([
            'banner'           => $shopBanner,
            'view'             => in_array($request['shop_view'], PSF_SHOP_VIEWS, true) ? $request['shop_view'] : 'list',
            'per_page'         => $perPage ?: PSF_SHOP_PER_PAGE_OPTIONS,
            'per_page_default' => (int) $request['shop_per_page_default'],
        ]));

        Toastr::success(translate('updated_successfully'));

        return back();
    }

    /**
     * Storefront texts (headings, menu labels…) in every language. They are
     * language keys, written to the language files the same way
     * Languages → Translate does, so both screens stay in sync.
     */
    public function updateTexts(Request $request): RedirectResponse
    {
        $request->validate([
            'texts'     => 'required|array',
            'texts.*'   => 'array',
            'texts.*.*' => 'nullable|string|max:500',
        ]);

        $allowed = array_merge(...array_values(psfSiteTextKeys()));
        foreach (psfLanguages() as $language) {
            $file = base_path('resources/lang/' . $language['code'] . '/messages.php');
            if (!is_file($file)) {
                continue;
            }
            $messages = include $file;
            foreach ($allowed as $key) {
                $text = trim(strip_tags((string) ($request['texts'][$key][$language['code']] ?? '')));
                if ($text !== '') {
                    $messages[$key] = $text;
                }
            }
            file_put_contents($file, '<?php return ' . var_export($messages, true) . ';');
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
            }
        }

        Toastr::success(translate('updated_successfully'));

        return back();
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
            'client_type_label.*'      => 'nullable|array',
            'client_type_label.*.*'    => 'nullable|string|max:120',
            'hidden_menus'             => 'nullable|array',
            'hidden_menus.*'           => 'nullable|string|max:60',
            'gallery_categories'       => 'nullable|array',
            'gallery_categories.*'     => 'nullable|string|max:1000',
            'gallery_per_page'         => 'nullable|integer|min:1|max:60',
            'contact_address'          => 'nullable|string|max:500',
            'phone_label'              => 'nullable|array',
            'phone_label.*'            => 'nullable|array',
            'phone_label.*.*'          => 'nullable|string|max:80',
            'phone_number'             => 'nullable|array',
            'phone_number.*'           => 'nullable|string|max:40',
            'hours_day'                => 'nullable|array',
            'hours_day.*'              => 'nullable|array',
            'hours_day.*.*'            => 'nullable|string|max:40',
            'hours_value'              => 'nullable|array',
            'hours_value.*'            => 'nullable|array',
            'hours_value.*.*'          => 'nullable|string|max:80',
            'map_embed'                => 'nullable|string|max:2000',
            'map_directions'           => 'nullable|string|max:1000',
            'hidden_home_sections'     => 'nullable|array',
            'hidden_home_sections.*'   => 'nullable|string|max:60',
            'cta_label'                => 'nullable|array',
            'cta_label.*'              => 'nullable|array',
            'cta_label.*.*'            => 'nullable|string|max:60',
            'cta_url'                  => 'nullable|array',
            'cta_url.*'                => 'nullable|string|max:500',
            'accent_color'             => 'nullable|string|max:9',
            'slogan'                   => 'nullable|array',
            'slogan.*'                 => 'nullable|string|max:191',
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
            $label = psfTextFromInput($request['client_type_label'][$index] ?? '');
            if ($key === '' || psfTextIsEmpty($label)) {
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

        $slogan = psfTextFromInput($request['slogan']);
        $this->businessSettingRepo->updateOrInsert(type: 'psf_slogan', value: psfTextIsEmpty($slogan) ? '' : json_encode($slogan));
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
            $label = psfTextFromInput($label);
            $url = trim((string)(($request['cta_url'][$index]) ?? ''));
            if (psfTextIsEmpty($label) || $url === '') {
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
                'label'    => psfTextFromInput($request['phone_label'][$index] ?? ''),
                'number'   => $number,
                'whatsapp' => (bool)(($request['phone_whatsapp'][$index]) ?? false),
            ];
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_contact_phones', value: json_encode($phones));

        // Opening hours: a blank time means "Fermé", so the client can close a
        // day just by emptying the field.
        $hours = [];
        foreach ((array)$request['hours_day'] as $index => $day) {
            $day = psfTextFromInput($day);
            if (psfTextIsEmpty($day)) {
                continue;
            }
            $value = psfTextFromInput($request['hours_value'][$index] ?? '');
            $hours[] = [
                'day'    => $day,
                'hours'  => $value,
                'closed' => psfTextIsEmpty($value),
            ];
        }
        $this->businessSettingRepo->updateOrInsert(type: 'psf_opening_hours', value: json_encode($hours));

        $this->businessSettingRepo->updateOrInsert(type: 'psf_map_embed', value: trim((string)$request['map_embed']));
        $this->businessSettingRepo->updateOrInsert(type: 'psf_map_directions', value: trim((string)$request['map_directions']));

        // Gallery: categories arrive as one comma-separated line — easier for
        // the client than a repeater, and blanks/duplicates are dropped.
        $this->businessSettingRepo->updateOrInsert(type: 'psf_gallery_menu', value: $request->has('gallery_menu') ? '1' : '0');
        $this->businessSettingRepo->updateOrInsert(type: 'psf_gallery_per_page', value: (string)((int)($request['gallery_per_page'] ?: 12)));

        // one comma list per language, matched by position; the default
        // language's label is the key saved on each réalisation
        $categoryLists = [];
        foreach (psfLanguages() as $language) {
            $categoryLists[$language['code']] = array_values(array_filter(array_map(
                static fn ($label) => trim(strip_tags((string)$label)),
                explode(',', (string)($request['gallery_categories'][$language['code']] ?? ''))
            ), static fn ($label) => $label !== ''));
        }
        $categories = [];
        $seen = [];
        foreach ($categoryLists[psfDefaultLanguageCode()] ?? [] as $position => $defaultLabel) {
            if (isset($seen[mb_strtolower($defaultLabel)])) {
                continue;
            }
            $seen[mb_strtolower($defaultLabel)] = true;
            $category = [];
            foreach ($categoryLists as $code => $list) {
                $category[$code] = $list[$position] ?? '';
            }
            $categories[] = $category;
        }
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
    /* ---- panel values, one text per language ------------------------ */

    /**
     * Rows of a JSON setting (or given rows) with their text fields as
     * [code => text] for the per-language inputs.
     */
    private function rowsForForm(string|array $setting, array $textFields): array
    {
        $rows = is_array($setting) ? $setting : psfSettingArray($setting);

        return array_map(function ($row) use ($textFields) {
            $row = (array) $row;
            foreach ($textFields as $field) {
                $row[$field] = psfTextArray($row[$field] ?? '');
            }
            $row['closed'] = (bool) ($row['closed'] ?? false);

            return $row;
        }, array_values($rows));
    }

    private function phonesForForm(): array
    {
        $rows = $this->rowsForForm('psf_contact_phones', ['label']);
        if ($rows === []) {
            $fallback = trim((string) (getWebConfig(name: 'company_phone') ?: ''));
            if ($fallback !== '') {
                $rows[] = ['label' => psfTextArray(''), 'number' => $fallback, 'whatsapp' => false];
            }
        }

        return array_map(fn ($row) => $row + ['number' => '', 'whatsapp' => false], $rows);
    }

    private function clientTypesForForm(): array
    {
        $rows = psfSettingArray('psf_client_types') ?: psfClientTypes();

        return array_map(fn ($row) => [
            'key'   => (string) ($row['key'] ?? ''),
            'label' => psfTextArray($row['label'] ?? ''),
        ], $rows);
    }

    /**
     * [code => "Label, Label"] for the gallery category inputs.
     */
    private function galleryCategoriesForForm(): array
    {
        $stored = psfSettingArray('psf_gallery_categories') ?: psfGalleryCategories();
        $lists = [];
        foreach (psfLanguages() as $language) {
            $lists[$language['code']] = [];
        }
        foreach ($stored as $category) {
            foreach (psfTextArray($category) as $code => $label) {
                $lists[$code][] = $label;
            }
        }

        return array_map(fn ($labels) => implode(', ', array_filter($labels, fn ($label) => $label !== '')), $lists);
    }

    /**
     * Footer columns with the title as [code => text] and the custom links as
     * [code => "Label | URL" lines].
     */
    private function footerColumnsForForm(): array
    {
        $columns = [];
        foreach (array_slice(psfSettingArray('psf_footer_columns'), 0, 3) as $column) {
            $links = (array) ($column['links'] ?? []);
            // older values: one list shared by every language
            $perLanguage = array_is_list($links) ? [psfDefaultLanguageCode() => $links] : $links;
            $lines = [];
            foreach (psfLanguages() as $language) {
                $lines[$language['code']] = collect($perLanguage[$language['code']] ?? [])
                    ->map(fn ($link) => ($link['label'] ?? '') . ' | ' . ($link['url'] ?? ''))
                    ->implode("\n");
            }
            $columns[] = [
                'type'  => (string) ($column['type'] ?? 'custom'),
                'title' => psfTextArray($column['title'] ?? ''),
                'links' => $lines,
            ];
        }

        return $columns;
    }
}
