<?php
/**
 * PSF helpers — admin menu visibility + catalogue display rules.
 *
 * Nothing is hard-coded: everything configurable lives in business_settings
 * so the client can change it from the panel.
 *
 * ---------------------------------------------------------------
 * 1. ADMIN MENU
 *    Hidden list: business_settings -> `psf_hidden_menus` (JSON array).
 *    The array below is only the seed used the first time.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfDefaultHiddenMenus')) {
    function psfDefaultHiddenMenus(): array
    {
        return [
            'vendors',              // marketplace only
            'delivery_men',
            'withdraws',
            'wallet',
            'loyalty_points',
            'subscribers',
            'support_tickets',
            'chatting',
            'pos',
            'refunds',
            'vendor_products',      // no vendors -> no vendor product queue
            'product_update_requests',
            'vendor_reports',
            'themes_addons',        // theme stays as-is (client decision)
            'theme_menu',
        ];
    }
}

if (!function_exists('psfMenuOptions')) {
    /**
     * Menu keys the panel offers as toggles.
     *
     * Only keys actually wired into the sidebar are listed, so every switch
     * on the PSF settings page really does something. Wire a new key into
     * `_side-bar.blade.php` with `@if (psfMenu('key'))` and add it here.
     *
     * @return array<int, string>
     */
    function psfMenuOptions(): array
    {
        return [
            'pos',
            'refunds',
            'vendors',
            'vendor_products',
            'delivery_men',
            'subscribers',
            'support_tickets',
            'themes_addons',
            'quote_requests',
            'gallery',
        ];
    }
}

if (!function_exists('psfHiddenMenus')) {
    /**
     * Hidden menu keys, read once per request.
     */
    function psfHiddenMenus(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $row = \Illuminate\Support\Facades\DB::table('business_settings')
                ->where('type', 'psf_hidden_menus')
                ->first();

            if ($row && $row->value !== null && $row->value !== '') {
                $decoded = json_decode($row->value, true);
                if (is_array($decoded)) {
                    return $cached = $decoded;
                }
            }
        } catch (\Throwable $e) {
            // table not ready (install/migrate) -> fall through to defaults
        }

        return $cached = psfDefaultHiddenMenus();
    }
}

if (!function_exists('psfMenu')) {
    /**
     * True when this menu item should be rendered.
     *
     * @param string $key menu key, e.g. 'vendors'
     */
    function psfMenu(string $key): bool
    {
        return !in_array($key, psfHiddenMenus(), true);
    }
}

/**
 * ---------------------------------------------------------------
 * 2. CATALOGUE — the two-path price model (client brief §7 / §8)
 *
 *    price entered  -> show price  -> "Ajouter au panier"
 *    price blank/0  -> "Contactez-nous pour le prix" -> "Demander le prix"
 * ---------------------------------------------------------------
 */

if (!function_exists('psfHasPrice')) {
    /**
     * Does this product have a real, sellable price?
     *
     * Rule (agreed with the client): the main unit price decides.
     * If it is blank or 0 the whole product is "prix sur demande",
     * whatever the variations say.
     *
     * @param object|array|null $product
     */
    function psfHasPrice($product): bool
    {
        if (empty($product)) {
            return false;
        }
        $price = is_array($product) ? ($product['unit_price'] ?? 0) : ($product->unit_price ?? 0);

        return (float)$price > 0;
    }
}

if (!function_exists('psfIsOnOrder')) {
    /**
     * "Sur commande" — the product is sold but not held in stock.
     *
     * @param object|array|null $product
     */
    function psfIsOnOrder($product): bool
    {
        if (empty($product)) {
            return false;
        }
        $value = is_array($product) ? ($product['availability'] ?? null) : ($product->availability ?? null);

        return $value === 'on_order';
    }
}

if (!function_exists('psfWhatsappNumber')) {
    /**
     * WhatsApp number from the panel (Social Media Chat setup).
     * Digits only, so it can be dropped straight into a wa.me link.
     */
    function psfWhatsappNumber(): string
    {
        try {
            $row = \Illuminate\Support\Facades\DB::table('business_settings')
                ->where('type', 'whatsapp')->first();
            if ($row) {
                $config = json_decode($row->value, true);
                if (!empty($config['number'])) {
                    return preg_replace('/\D+/', '', (string)$config['number']);
                }
                if (!empty($config['phone'])) {
                    return preg_replace('/\D+/', '', (string)$config['phone']);
                }
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return '';
    }
}

/**
 * ---------------------------------------------------------------
 * 3. DEVIS — quote requests (client brief §16)
 * ---------------------------------------------------------------
 */

if (!function_exists('psfQuoteMenu')) {
    /**
     * Whether the "Demande de devis" entry is shown in the site menus.
     *
     * Managed from the panel (business_settings -> `psf_quote_menu`),
     * so PSF can hide the link without touching a template. Defaults to on.
     */
    function psfQuoteMenu(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $value = getWebConfig(name: 'psf_quote_menu');
            $cached = $value === null || $value === '' || (bool)$value;
        }

        return $cached;
    }
}

if (!function_exists('psfGalleryMenu')) {
    /**
     * Whether "Nos réalisations" is shown in the site menus.
     *
     * business_settings -> `psf_gallery_menu`. Defaults to on.
     */
    function psfGalleryMenu(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $value = getWebConfig(name: 'psf_gallery_menu');
            $cached = $value === null || $value === '' || (bool)$value;
        }

        return $cached;
    }
}

if (!function_exists('psfGalleryCategories')) {
    /**
     * Project categories offered in the gallery.
     *
     * business_settings -> `psf_gallery_categories` (JSON array of labels),
     * so PSF adds a new kind of project from the panel.
     *
     * @return array<int, string>
     */
    function psfGalleryCategories(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_gallery_categories');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        if (!is_array($decoded) || $decoded === []) {
            $decoded = ['Plomberie', 'Sanitaire', 'Chauffage', 'Chantier'];
        }

        $cached = array_values(array_filter(array_map(
            static fn ($label) => trim((string)$label),
            $decoded
        )));

        return $cached;
    }
}

/**
 * ---------------------------------------------------------------
 * 6. SEO — indexing default and structured data (client brief §20)
 * ---------------------------------------------------------------
 */

if (!function_exists('psfSearchIndexing')) {
    /**
     * Whether search engines may index the site.
     *
     * business_settings -> `psf_search_indexing`. Defaults to on: a live shop
     * wants to be found. Switch it off while the site is still being built.
     */
    function psfSearchIndexing(): bool
    {
        static $cached = null;
        if ($cached === null) {
            $value = getWebConfig(name: 'psf_search_indexing');
            $cached = $value === null || $value === '' || (bool)$value;
        }

        return $cached;
    }
}

if (!function_exists('psfRobotsContent')) {
    /**
     * Site-wide default for <meta name="robots">, used when a page has no
     * record of its own in the panel's SEO section.
     */
    function psfRobotsContent(): string
    {
        return psfSearchIndexing()
            ? 'index, follow, max-image-preview:large'
            : 'noindex, nofollow';
    }
}

if (!function_exists('psfJsonLd')) {
    /**
     * Encodes one JSON-LD block for a <script type="application/ld+json">.
     *
     * JSON_HEX_TAG escapes "<" so a value containing "</script>" cannot break
     * out of the tag; the other flags keep URLs and accents readable.
     *
     * @param array<string, mixed> $data
     */
    function psfJsonLd(array $data): string
    {
        return (string)json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}

if (!function_exists('psfLocalBusinessSchema')) {
    /**
     * LocalBusiness structured data, built from the panel settings that the
     * contact page already uses — so the client maintains one set of values.
     *
     * @return array<string, mixed>
     */
    function psfLocalBusinessSchema(): array
    {
        $phones = psfContactPhones();
        $address = psfContactAddress();

        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'HardwareStore',
            'name'     => (string)(getWebConfig(name: 'company_name') ?: ''),
            'url'      => url('/'),
        ];

        if ($email = getWebConfig(name: 'company_email')) {
            $schema['email'] = (string)$email;
        }

        if ($address !== '') {
            $schema['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $address,
                'addressLocality' => 'Ouagadougou',
                'addressCountry'  => 'BF',
            ];
        }

        if ($phones !== []) {
            $schema['telephone'] = $phones[0]['number'];
        }

        // "Mo 07:30-18:00" style rows; days with no hours are simply omitted.
        $map = [
            'lundi' => 'Mo', 'mardi' => 'Tu', 'mercredi' => 'We', 'jeudi' => 'Th',
            'vendredi' => 'Fr', 'samedi' => 'Sa', 'dimanche' => 'Su',
        ];
        $hours = [];
        foreach (psfOpeningHours() as $row) {
            if ($row['closed'] || $row['hours'] === '') {
                continue;
            }
            $key = mb_strtolower($row['day']);
            if (!isset($map[$key])) {
                continue;
            }
            $times = preg_replace('/\s*-\s*/', '-', trim($row['hours']));
            $hours[] = $map[$key] . ' ' . $times;
        }
        if ($hours !== []) {
            $schema['openingHours'] = $hours;
        }

        return $schema;
    }
}

/**
 * ---------------------------------------------------------------
 * 5. HOMEPAGE — section visibility, hero buttons, location block
 *    (client brief §19). Everything below is panel-managed.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfHomeSectionOptions')) {
    /**
     * Homepage blocks the panel can switch off, in the order they appear.
     *
     * Keys map to `@if (psfHomeSection('key'))` guards in home.blade.php —
     * add a key here only once it is actually wired there.
     *
     * @return array<int, string>
     */
    function psfHomeSectionOptions(): array
    {
        return [
            'hero_ctas',
            'flash_deal',
            'featured_products',
            'categories',
            'featured_deals',
            'clearance_sale',
            'section_banner',
            'deal_of_the_day',
            'new_arrivals',
            'footer_banners',
            'brands',
            'category_products',
            'company_reliability',
            'location',
        ];
    }
}

if (!function_exists('psfHiddenHomeSections')) {
    /**
     * Homepage blocks switched off in the panel.
     *
     * business_settings -> `psf_hidden_home_sections` (JSON array).
     * Empty by default: a fresh install shows the site exactly as before.
     *
     * @return array<int, string>
     */
    function psfHiddenHomeSections(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_hidden_home_sections');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return $cached = is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('psfHomeSection')) {
    /**
     * True when this homepage block should be rendered.
     */
    function psfHomeSection(string $key): bool
    {
        return !in_array($key, psfHiddenHomeSections(), true);
    }
}

if (!function_exists('psfHeroCtas')) {
    /**
     * Up to three call-to-action buttons under the homepage slider.
     *
     * business_settings -> `psf_hero_ctas`, a JSON list of {label, url}.
     * Returns [] when nothing is set, so the block does not render.
     *
     * @return array<int, array{label:string,url:string}>
     */
    function psfHeroCtas(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_hero_ctas');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        $ctas = [];
        if (is_array($decoded)) {
            foreach ($decoded as $row) {
                $label = trim((string)($row['label'] ?? ''));
                $url = trim((string)($row['url'] ?? ''));
                if ($label === '' || $url === '') {
                    continue;
                }
                $ctas[] = ['label' => $label, 'url' => $url];
            }
        }

        return $cached = array_slice($ctas, 0, 3);
    }
}

/**
 * ---------------------------------------------------------------
 * 4. CONTACT — address, phones, opening hours, map (client brief §18)
 *    Every value below is panel-managed; none of it is written in a template.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfContactAddress')) {
    /**
     * Shop address — reuses 6valley's own `shop_address` so PSF only ever
     * types it in one place.
     */
    function psfContactAddress(): string
    {
        return trim((string)(getWebConfig(name: 'shop_address') ?: ''));
    }
}

if (!function_exists('psfContactPhones')) {
    /**
     * Phone numbers shown on the contact page.
     *
     * business_settings -> `psf_contact_phones`, a JSON list of
     * {label, number, whatsapp}. Falls back to the single `company_phone`
     * so the page is never empty on a fresh install.
     *
     * @return array<int, array{label:string,number:string,whatsapp:bool}>
     */
    function psfContactPhones(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_contact_phones');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        $phones = [];
        if (is_array($decoded)) {
            foreach ($decoded as $row) {
                $number = trim((string)($row['number'] ?? ''));
                if ($number === '') {
                    continue;
                }
                $phones[] = [
                    'label'    => trim((string)($row['label'] ?? '')),
                    'number'   => $number,
                    'whatsapp' => (bool)($row['whatsapp'] ?? false),
                ];
            }
        }

        if ($phones === []) {
            $fallback = trim((string)(getWebConfig(name: 'company_phone') ?: ''));
            if ($fallback !== '') {
                $phones[] = ['label' => '', 'number' => $fallback, 'whatsapp' => false];
            }
        }

        return $cached = $phones;
    }
}

if (!function_exists('psfOpeningHours')) {
    /**
     * Opening hours, one row per day, in week order.
     *
     * business_settings -> `psf_opening_hours`, a JSON list of
     * {day, hours, closed}. Returns [] when nothing is set, so the block
     * simply does not render rather than showing empty rows.
     *
     * @return array<int, array{day:string,hours:string,closed:bool}>
     */
    function psfOpeningHours(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_opening_hours');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        $days = [];
        if (is_array($decoded)) {
            foreach ($decoded as $row) {
                $day = trim((string)($row['day'] ?? ''));
                if ($day === '') {
                    continue;
                }
                $days[] = [
                    'day'    => $day,
                    'hours'  => trim((string)($row['hours'] ?? '')),
                    'closed' => (bool)($row['closed'] ?? false),
                ];
            }
        }

        return $cached = $days;
    }
}

if (!function_exists('psfDefaultOpeningHours')) {
    /**
     * Week skeleton offered in the panel the first time, so the client
     * only has to fill in the times.
     *
     * @return array<int, array{day:string,hours:string,closed:bool}>
     */
    function psfDefaultOpeningHours(): array
    {
        $week = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        return array_map(
            static fn (string $day) => ['day' => $day, 'hours' => '', 'closed' => $day === 'Dimanche'],
            $week
        );
    }
}

if (!function_exists('psfMapEmbedUrl')) {
    /**
     * Google Maps embed URL for the contact page.
     *
     * business_settings -> `psf_map_embed`. The client may paste either the
     * bare URL or the whole <iframe> copied from Google; the src is pulled
     * out of the markup. Only https URLs are returned, so nothing else can
     * be injected into the frame.
     */
    function psfMapEmbedUrl(): ?string
    {
        static $cached = false;
        if ($cached !== false) {
            return $cached;
        }

        $value = trim((string)(getWebConfig(name: 'psf_map_embed') ?: ''));
        if ($value === '') {
            return $cached = null;
        }

        if (stripos($value, '<iframe') !== false && preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $value, $matches)) {
            $value = $matches[1];
        }

        return $cached = str_starts_with(strtolower($value), 'https://') ? $value : null;
    }
}

if (!function_exists('psfDirectionsUrl')) {
    /**
     * "Obtenir l'itinéraire" link.
     *
     * Uses `psf_map_directions` when the client sets one, otherwise builds a
     * Google Maps directions link from the shop address.
     */
    function psfDirectionsUrl(): ?string
    {
        $custom = trim((string)(getWebConfig(name: 'psf_map_directions') ?: ''));
        if ($custom !== '' && str_starts_with(strtolower($custom), 'https://')) {
            return $custom;
        }

        $address = psfContactAddress();
        if ($address === '') {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($address);
    }
}

if (!function_exists('psfClientTypes')) {
    /**
     * "Type de client" options for the quote form.
     *
     * Managed from the panel (business_settings -> `psf_client_types`),
     * so PSF can add e.g. "Revendeur" later without a developer.
     *
     * @return array<int, array{key:string,label:string}>
     */
    function psfClientTypes(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        try {
            $row = \Illuminate\Support\Facades\DB::table('business_settings')
                ->where('type', 'psf_client_types')->first();
            if ($row && !empty($row->value)) {
                $decoded = json_decode($row->value, true);
                if (is_array($decoded) && $decoded !== []) {
                    return $cached = $decoded;
                }
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return $cached = [
            ['key' => 'particulier', 'label' => 'Particulier'],
            ['key' => 'plombier',    'label' => 'Plombier'],
            ['key' => 'entreprise',  'label' => 'Entreprise'],
        ];
    }
}

if (!function_exists('psfQuoteRecipients')) {
    /**
     * Where a new quote request is announced.
     * Both come from the panel; either may be empty.
     *
     * @return array{email:string,whatsapp:string}
     */
    function psfQuoteRecipients(): array
    {
        $email = '';
        $whatsapp = '';
        try {
            $row = \Illuminate\Support\Facades\DB::table('business_settings')
                ->where('type', 'psf_quote_recipients')->first();
            if ($row && !empty($row->value)) {
                $decoded = json_decode($row->value, true);
                $email = $decoded['email'] ?? '';
                $whatsapp = $decoded['whatsapp'] ?? '';
            }
        } catch (\Throwable $e) {
            // fall through
        }

        if ($email === '') {
            $email = (string)(getWebConfig(name: 'company_email') ?? '');
        }
        if ($whatsapp === '') {
            $whatsapp = psfWhatsappNumber();
        }

        return ['email' => $email, 'whatsapp' => preg_replace('/\D+/', '', (string)$whatsapp)];
    }
}

if (!function_exists('psfWhatsappTemplate')) {
    /**
     * A WhatsApp message template from the panel, with a fallback.
     *
     * @param string $key      business_settings type
     * @param string $fallback used only when the setting was never written
     */
    function psfWhatsappTemplate(string $key, string $fallback): string
    {
        try {
            $row = \Illuminate\Support\Facades\DB::table('business_settings')
                ->where('type', $key)->first();
            $value = $row->value ?? '';
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return $fallback;
    }
}

if (!function_exists('psfWhatsappChatUrl')) {
    /**
     * The floating "chat with us" button.
     * Greeting is editable from the panel (`psf_chat_greeting`).
     */
    function psfWhatsappChatUrl(): string
    {
        $text = psfWhatsappTemplate('psf_chat_greeting', 'Bonjour PSF, j\'ai une question.');

        return 'https://wa.me/' . psfWhatsappNumber() . '?text=' . rawurlencode($text);
    }
}

if (!function_exists('psfProductWhatsappUrl')) {
    /**
     * Per-product WhatsApp link (client brief §8 — every product has one).
     *
     * Which message is used depends on the product:
     *   no price -> ask for the price   (`psf_price_request_template`)
     *   priced   -> order the product   (`psf_order_request_template`)
     *
     * @param object|array|null $product
     */
    function psfProductWhatsappUrl($product): string
    {
        return psfHasPrice($product)
            ? psfOrderRequestUrl($product)
            : psfPriceRequestUrl($product);
    }
}

if (!function_exists('psfOrderRequestUrl')) {
    /**
     * "Commander sur WhatsApp" — for products that do have a price.
     *
     * @param object|array|null $product
     */
    function psfOrderRequestUrl($product): string
    {
        $template = psfWhatsappTemplate(
            'psf_order_request_template',
            'Bonjour PSF, je souhaite commander ce produit : {product}' . PHP_EOL . '{url}'
        );

        return 'https://wa.me/' . psfWhatsappNumber() . '?text=' . rawurlencode(psfFillTemplate($template, $product));
    }
}

if (!function_exists('psfFillTemplate')) {
    /**
     * Replace {product} and {url} in a message template.
     *
     * @param object|array|null $product
     */
    function psfFillTemplate(string $template, $product): string
    {
        $name = '';
        $slug = '';
        if (!empty($product)) {
            $name = is_array($product) ? ($product['name'] ?? '') : ($product->name ?? '');
            $slug = is_array($product) ? ($product['slug'] ?? '') : ($product->slug ?? '');
        }

        $url = $slug !== '' ? route('product', $slug) : url('/');

        return str_replace(['{product}', '{url}'], [$name, $url], $template);
    }
}

if (!function_exists('psfPriceRequestUrl')) {
    /**
     * wa.me link asking PSF for the price of one product.
     *
     * The message template is editable from the panel
     * (business_settings -> `psf_price_request_template`).
     * {product} and {url} are replaced.
     *
     * @param object|array|null $product
     */
    function psfPriceRequestUrl($product): string
    {
        $template = psfWhatsappTemplate(
            'psf_price_request_template',
            'Bonjour PSF, je souhaite connaître le prix et la disponibilité de ce produit : {product}'
                . PHP_EOL . '{url}'
        );

        return 'https://wa.me/' . psfWhatsappNumber() . '?text=' . rawurlencode(psfFillTemplate($template, $product));
    }
}
