<?php
/**
 * PSF — storefront design helpers (Pixio redesign).
 *
 * Loaded from psf-menu.php, so no composer change is needed to deploy it.
 *
 * Nothing visual is fixed here: which design is live comes from .env
 * (PSF_FRONTEND_DESIGN, written by Paramètres PSF), colours and texts come
 * from business_settings, and every default below only applies until the
 * panel saves a value.
 */

if (!function_exists('psfDesign')) {
    /**
     * The storefront design in use: "pixio" or "classic".
     */
    function psfDesign(): string
    {
        return env('PSF_FRONTEND_DESIGN') === 'pixio'
            && theme_root_path() === 'default'
            && is_file(base_path('resources/themes/psf_pixio/file_names.php'))
            ? 'pixio'
            : 'classic';
    }
}

if (!function_exists('psfDesignAsset')) {
    /**
     * URL of a file shipped with the Pixio design, e.g. 'assets/css/style.css'.
     *
     * Mirrors theme_asset() for non-default themes: when the web root is the
     * public/ folder the files are expected under public/themes/psf_pixio.
     */
    function psfDesignAsset(string $path): string
    {
        $path = ltrim($path, '/');

        return DOMAIN_POINTED_DIRECTORY == 'public'
            ? dynamicAsset(path: 'public/themes/psf_pixio/public/' . $path)
            : dynamicAsset(path: 'resources/themes/psf_pixio/public/' . $path);
    }
}

if (!function_exists('psfHexToRgb')) {
    /**
     * "#245287" → [36, 82, 135]. Accepts 3 or 6 hex digits; null if invalid.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    function psfHexToRgb(?string $hex): ?array
    {
        $hex = ltrim(trim((string) $hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return null;
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}

if (!function_exists('psfShadeHex')) {
    /**
     * Mixes a colour towards black (negative amount) or white (positive),
     * amount in -1…1. Used to derive hover and dark shades from one colour.
     */
    function psfShadeHex(string $hex, float $amount): string
    {
        $rgb = psfHexToRgb($hex) ?? [0, 0, 0];
        $target = $amount < 0 ? 0 : 255;
        $amount = abs($amount);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($rgb[0] + ($target - $rgb[0]) * $amount),
            (int) round($rgb[1] + ($target - $rgb[1]) * $amount),
            (int) round($rgb[2] + ($target - $rgb[2]) * $amount)
        );
    }
}

if (!function_exists('psfPixioColors')) {
    /**
     * The design palette, from business_settings → `psf_pixio_colors`.
     *
     * Primary defaults to the shop's own primary colour (Business setup), so a
     * fresh switch to the new design already carries the brand. The others
     * default to the template's values until the panel saves its own.
     *
     * @return array{primary:string,secondary:string,title:string,light:string,light_dark:string,body_text:string,border:string,whatsapp:string}
     */
    function psfPixioColors(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $shopColors = getWebConfig(name: 'colors');
        $shopPrimary = is_array($shopColors) ? ($shopColors['primary'] ?? null) : null;

        $defaults = [
            'primary'    => psfHexToRgb($shopPrimary) ? $shopPrimary : '#CC0D39',
            'secondary'  => '#000000',
            'title'      => '#000000',
            'light'      => '#FFFAF3',
            'light_dark' => '#FEEB9D',
            'body_text'  => '#5E626F',
            'border'     => '#D7D7D7',
            'whatsapp'   => '#25D366',
        ];

        $stored = getWebConfig(name: 'psf_pixio_colors');
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;
        $stored = is_array($stored) ? $stored : [];

        $colors = [];
        foreach ($defaults as $key => $fallback) {
            $value = trim((string) ($stored[$key] ?? ''));
            $colors[$key] = psfHexToRgb($value) ? $value : $fallback;
        }

        return $cached = $colors;
    }
}

if (!function_exists('psfPixioCssVariables')) {
    /**
     * The --psf-* custom properties the tokenised template CSS reads.
     * Every value has been validated as a hex colour, so this is safe to
     * print inside a <style> block.
     */
    function psfPixioCssVariables(): string
    {
        $c = psfPixioColors();
        $rgb = fn (string $hex) => implode(', ', psfHexToRgb($hex) ?? [0, 0, 0]);

        $vars = [
            '--psf-primary'       => $c['primary'],
            '--psf-primary-rgb'   => $rgb($c['primary']),
            '--psf-primary-hover' => psfShadeHex($c['primary'], -0.15),
            '--psf-primary-dark'  => psfShadeHex($c['primary'], -0.7),
            '--psf-secondary'     => $c['secondary'],
            '--psf-secondary-rgb' => $rgb($c['secondary']),
            '--psf-title'         => $c['title'],
            '--psf-light'         => $c['light'],
            '--psf-light-dark'    => $c['light_dark'],
            '--psf-body-text'     => $c['body_text'],
            '--psf-border'        => $c['border'],
            '--psf-whatsapp'      => $c['whatsapp'],
            '--psf-whatsapp-rgb'  => $rgb($c['whatsapp']),
            '--psf-accent'        => psfAccentColor(),
            '--psf-accent-text'   => psfAccentTextColor(),
            // the template reads these two names directly as well
            '--light-dark'        => $c['light_dark'],
            '--bg-light'          => $c['light'],
            // names from the template's skin file, derived from the same palette
            '--secondary-hover'   => $c['secondary'],
            '--secondary-light'   => psfShadeHex($c['primary'], 0.6),
            '--theme-text-color'  => $c['title'],
            '--dark'              => $c['title'],
            '--dark-light'        => psfShadeHex($c['light'], -0.02),
            '--gradient1'         => 'linear-gradient(307deg, ' . $c['light_dark'] . ' 1.9%, ' . psfShadeHex($c['primary'], 0.88) . ' 67.57%)',
            '--gradient2'         => 'linear-gradient(307deg, ' . $c['light_dark'] . ' 1.9%, ' . psfShadeHex($c['primary'], 0.65) . ' 85.96%)',
        ];

        $lines = [];
        foreach ($vars as $name => $value) {
            $lines[] = '    ' . $name . ': ' . $value . ';';
        }

        return ":root {\n" . implode("\n", $lines) . "\n}";
    }
}

if (!function_exists('psfSocialIconClass')) {
    /**
     * Font Awesome 6 brand icon for a social network saved in the panel.
     *
     * 6valley stores Font Awesome 4 class names ("fa fa-twitter"), which the
     * new design's icon set does not draw. The link itself still comes from the
     * panel; only the glyph is picked here, by network name.
     */
    function psfSocialIconClass(string $name): string
    {
        $map = [
            // the design ships Font Awesome 6.0, which has no X logo yet
            'twitter'     => 'fa-brands fa-twitter',
            'x'           => 'fa-brands fa-twitter',
            'facebook'    => 'fa-brands fa-facebook-f',
            'instagram'   => 'fa-brands fa-instagram',
            'linkedin'    => 'fa-brands fa-linkedin-in',
            'pinterest'   => 'fa-brands fa-pinterest-p',
            'youtube'     => 'fa-brands fa-youtube',
            'tiktok'      => 'fa-brands fa-tiktok',
            'whatsapp'    => 'fa-brands fa-whatsapp',
            'google-plus' => 'fa-brands fa-google',
            'telegram'    => 'fa-brands fa-telegram',
            'snapchat'    => 'fa-brands fa-snapchat',
        ];

        return $map[strtolower(trim($name))] ?? 'fa-solid fa-link';
    }
}

if (!function_exists('psfFooterColumns')) {
    /**
     * The three link columns of the footer, from business_settings →
     * `psf_footer_columns`: [{title, type, links:[{label,url}]}].
     *
     * type "categories" lists the shop categories, "pages" the published
     * business pages (policies, about…), "custom" the links typed in the panel.
     * Until the panel saves its own, sensible defaults are used so the footer is
     * never empty. Only http(s) or site-relative URLs are kept.
     *
     * @return array<int, array{title:string,links:array<int,array{label:string,url:string}>}>
     */
    function psfFooterColumns(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $stored = getWebConfig(name: 'psf_footer_columns');
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;

        $config = is_array($stored) && count($stored) > 0 ? $stored : [
            ['title' => '', 'type' => 'categories', 'links' => []],
            ['title' => '', 'type' => 'pages', 'links' => []],
            ['title' => '', 'type' => 'site', 'links' => []],
        ];

        $safeUrl = function (string $url): ?string {
            $url = trim($url);
            if ($url === '') {
                return null;
            }
            if (preg_match('#^https?://#i', $url) || str_starts_with($url, '/')) {
                return str_starts_with($url, '/') ? url($url) : $url;
            }
            return null;
        };

        $columns = [];
        foreach (array_slice($config, 0, 3) as $column) {
            $type = $column['type'] ?? 'custom';
            $links = [];

            if ($type === 'categories') {
                $title = translate('categories');
                foreach (\App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 6) as $category) {
                    $links[] = ['label' => $category['name'], 'url' => route('category-products', ['slug' => $category['slug']])];
                }
            } elseif ($type === 'pages') {
                $title = translate('useful_links');
                foreach (\App\Models\BusinessPage::where('status', 1)->orderBy('id')->get() as $page) {
                    // built-in pages (About us, policies…) are stored in English: show them
                    // through their language key, editable in Languages; own pages keep their title
                    $label = $page->default_status ? translate(str_replace('-', '_', $page->slug)) : $page->title;
                    $links[] = ['label' => $label, 'url' => route('business-page.view', ['slug' => $page->slug])];
                }
                $links[] = ['label' => translate('contact'), 'url' => route('contacts')];
            } elseif ($type === 'site') {
                $title = translate('quick_links');
                $links[] = ['label' => translate('products'), 'url' => route('products')];
                if (psfGalleryMenu()) {
                    $links[] = ['label' => translate('Our_Realisations'), 'url' => route('psf.gallery.index')];
                }
                if (psfQuoteMenu()) {
                    $links[] = ['label' => translate('Quote_Request'), 'url' => route('psf.quote.index')];
                }
                $links[] = ['label' => translate('track_order'), 'url' => route('track-order.index')];
                $links[] = ['label' => translate('faq'), 'url' => route('helpTopic')];
            } else {
                $title = '';
                $stored = (array) ($column['links'] ?? []);
                // links saved per language; older values are one shared list
                if (!array_is_list($stored)) {
                    $stored = $stored[getDefaultLanguage()] ?? [];
                    if ($stored === []) {
                        $stored = (array) ($column['links'][psfDefaultLanguageCode()] ?? []);
                    }
                }
                foreach ($stored as $link) {
                    $url = $safeUrl((string) ($link['url'] ?? ''));
                    $label = trim((string) ($link['label'] ?? ''));
                    if ($url && $label !== '') {
                        $links[] = ['label' => $label, 'url' => $url];
                    }
                }
            }

            $customTitle = psfText($column['title'] ?? '');
            $columns[] = [
                'title' => $customTitle !== '' ? $customTitle : $title,
                'type'  => $type,
                'links' => $links,
            ];
        }

        return $cached = $columns;
    }
}

if (!function_exists('psfFooterPaymentImage')) {
    /**
     * "We accept" image for the footer bottom bar (Paramètres PSF → Design).
     * Null when none is uploaded, so the block is hidden.
     */
    function psfFooterPaymentImage(): ?string
    {
        $file = trim((string) (getWebConfig(name: 'psf_footer_payment_image') ?: ''));
        if ($file === '') {
            return null;
        }

        return dynamicStorage(path: 'storage/app/public/psf-design/' . $file);
    }
}

if (!function_exists('psfMenuImage')) {
    /**
     * Image shown on the right of the shop mega menu (template "adv-media"),
     * from business_settings → `psf_menu_image` {image, url}. Null when unset.
     *
     * @return array{image:string,url:?string}|null
     */
    function psfMenuImage(): ?array
    {
        $stored = getWebConfig(name: 'psf_menu_image');
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;
        $file = is_array($stored) ? trim((string) ($stored['image'] ?? '')) : '';
        if ($file === '') {
            return null;
        }

        $url = trim((string) ($stored['url'] ?? ''));
        $url = preg_match('#^https?://#i', $url) ? $url : (str_starts_with($url, '/') ? url($url) : null);

        return [
            'image' => dynamicStorage(path: 'storage/app/public/psf-design/' . $file),
            'url'   => $url,
        ];
    }
}

if (!function_exists('psfWishlistIds')) {
    /**
     * Product ids in the signed-in customer's wishlist, read once per request,
     * so every product card can show its heart state without its own query.
     *
     * @return array<int, int>
     */
    function psfWishlistIds(): array
    {
        static $ids = null;
        if ($ids !== null) {
            return $ids;
        }
        if (!auth('customer')->check()) {
            return $ids = [];
        }

        return $ids = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->pluck('product_id')->map(fn ($id) => (int) $id)->all();
    }
}

if (!function_exists('psfProductHasOptions')) {
    /**
     * Does the customer have to pick a colour or a variation before adding
     * this product to the cart? Then the card opens the quick view instead
     * of adding straight away.
     */
    function psfProductHasOptions($product): bool
    {
        $decode = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }
            $decoded = json_decode((string) $value, true);

            return is_array($decoded) ? $decoded : [];
        };

        return count($decode($product->colors ?? '[]')) > 0
            || count($decode($product->choice_options ?? '[]')) > 0;
    }
}

if (!function_exists('psfHomeVideo')) {
    /**
     * Homepage video block of the new design, from business_settings →
     * `psf_home_video` {file, link, background}. The uploaded file wins over
     * the link. Null when neither is set, so the block is not shown.
     *
     * @return array{url:string,background:?string,file:string,link:string}|null
     */
    function psfHomeVideo(): ?array
    {
        $stored = getWebConfig(name: 'psf_home_video');
        $stored = is_string($stored) ? (json_decode($stored, true) ?: []) : (array) $stored;

        $file = trim((string) ($stored['file'] ?? ''));
        $link = trim((string) ($stored['link'] ?? ''));
        $background = trim((string) ($stored['background'] ?? ''));

        $url = $file !== ''
            ? dynamicStorage(path: 'storage/app/public/psf-design/' . $file)
            : (preg_match('#^https://#i', $link) ? $link : '');

        // the block shows as soon as there is a video or a background picture
        if ($url === '' && $background === '') {
            return null;
        }

        return [
            'url'        => $url,
            'background' => $background !== '' ? dynamicStorage(path: 'storage/app/public/psf-design/' . $background) : null,
            'file'       => $file,
            'link'       => $link,
        ];
    }
}

if (!function_exists('psfSettingArray')) {
    /**
     * A JSON business setting as an array, whatever getWebConfig returns
     * (it decodes JSON itself, but plain strings come back as-is).
     */
    function psfSettingArray(string $name): array
    {
        $value = getWebConfig(name: $name);
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }
}

/*
 * ---------------------------------------------------------------
 * Texts typed in the panel, in every active language.
 * A text is stored either as a plain string (older values, shown in
 * every language) or as {"fr": "...", "en": "..."}.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfLanguages')) {
    /**
     * Active site languages, default language first.
     *
     * @return array<int, array{code:string,name:string,default:bool}>
     */
    function psfLanguages(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $languages = [];
        foreach ((array) getWebConfig(name: 'language') as $language) {
            $language = (array) $language;
            if (empty($language['code']) || (int) ($language['status'] ?? 0) !== 1) {
                continue;
            }
            $languages[] = [
                'code'    => (string) $language['code'],
                'name'    => ucfirst((string) ($language['name'] ?? $language['code'])),
                'default' => filter_var($language['default'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }
        usort($languages, fn ($a, $b) => (int) $b['default'] <=> (int) $a['default']);

        return $cached = $languages ?: [['code' => 'en', 'name' => 'English', 'default' => true]];
    }
}

if (!function_exists('psfDefaultLanguageCode')) {
    function psfDefaultLanguageCode(): string
    {
        return psfLanguages()[0]['code'];
    }
}

if (!function_exists('psfText')) {
    /**
     * The text for the language the visitor is reading, falling back to the
     * default language, then to any language that has one.
     */
    function psfText(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }
        if (!is_array($value)) {
            return '';
        }

        foreach ([getDefaultLanguage(), psfDefaultLanguageCode()] as $code) {
            if (isset($value[$code]) && trim((string) $value[$code]) !== '') {
                return trim((string) $value[$code]);
            }
        }
        foreach ($value as $text) {
            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
        }

        return '';
    }
}

if (!function_exists('psfTextArray')) {
    /**
     * A stored text as [code => text] for every active language, for the
     * panel's inputs. An old plain string is put in the default language.
     *
     * @return array<string, string>
     */
    function psfTextArray(mixed $value): array
    {
        $texts = [];
        foreach (psfLanguages() as $language) {
            $texts[$language['code']] = '';
        }
        if (is_string($value) || is_numeric($value)) {
            $texts[psfDefaultLanguageCode()] = trim((string) $value);
        } elseif (is_array($value)) {
            foreach ($texts as $code => $unused) {
                $texts[$code] = trim((string) ($value[$code] ?? ''));
            }
        }

        return $texts;
    }
}

if (!function_exists('psfTextFromInput')) {
    /**
     * Cleans a per-language input (e.g. slogan[fr], slogan[en]) for storage.
     * A plain string (single-language form) is kept for the default language.
     *
     * @return array<string, string>
     */
    function psfTextFromInput(mixed $input): array
    {
        $texts = [];
        foreach (psfLanguages() as $language) {
            $code = $language['code'];
            $text = is_array($input) ? ($input[$code] ?? '') : ($language['default'] ? $input : '');
            $texts[$code] = trim(strip_tags((string) $text));
        }

        return $texts;
    }
}

if (!function_exists('psfTextIsEmpty')) {
    function psfTextIsEmpty(array $texts): bool
    {
        return implode('', $texts) === '';
    }
}

if (!function_exists('psfTranslationsOf')) {
    /**
     * A language key in every active language, [code => text], read from the
     * language files (used to prefill the panel, e.g. week days).
     *
     * @return array<string, string>
     */
    function psfTranslationsOf(string $key): array
    {
        $texts = [];
        foreach (psfLanguages() as $language) {
            $file = base_path('resources/lang/' . $language['code'] . '/messages.php');
            $messages = is_file($file) ? include $file : [];
            $texts[$language['code']] = (string) ($messages[$key] ?? ucfirst(str_replace('_', ' ', $key)));
        }

        return $texts;
    }
}

/*
 * ---------------------------------------------------------------
 * Record texts in every language (6valley `translations` table),
 * for records 6valley keeps in one language only (banners,
 * réalisations). The record's own column keeps the default language.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfSaveTranslations')) {
    /**
     * @param array<string, array<string, string>> $fields field => [code => text]
     */
    function psfSaveTranslations(\Illuminate\Database\Eloquent\Model $model, array $fields): void
    {
        foreach ($fields as $field => $texts) {
            foreach ($texts as $code => $text) {
                \App\Models\Translation::updateOrInsert(
                    [
                        'translationable_type' => get_class($model),
                        'translationable_id'   => $model->getKey(),
                        'locale'               => $code,
                        'key'                  => $field,
                    ],
                    ['value' => (string) $text]
                );
            }
        }
    }
}

if (!function_exists('psfRecordTexts')) {
    /**
     * [code => text] of one field of a record, for the panel's inputs. The
     * default language falls back to the record's own column.
     *
     * @return array<string, string>
     */
    function psfRecordTexts(?\Illuminate\Database\Eloquent\Model $model, string $field): array
    {
        $texts = psfTextArray($model?->getAttribute($field) ?? '');
        if (!$model || !$model->getKey()) {
            return $texts;
        }

        // every translated field of the record is read once per request
        static $loaded = [];
        $cacheKey = get_class($model) . '#' . $model->getKey();
        if (!isset($loaded[$cacheKey])) {
            $loaded[$cacheKey] = \App\Models\Translation::where('translationable_type', get_class($model))
                ->where('translationable_id', $model->getKey())
                ->get(['locale', 'key', 'value'])
                ->groupBy('key')
                ->map(fn ($group) => $group->pluck('value', 'locale'));
        }
        $rows = $loaded[$cacheKey][$field] ?? collect();
        foreach ($texts as $code => $text) {
            if (isset($rows[$code]) && trim((string) $rows[$code]) !== '') {
                $texts[$code] = trim((string) $rows[$code]);
            }
        }

        return $texts;
    }
}

if (!function_exists('psfRecordText')) {
    /**
     * One field of a record in the visitor's language.
     */
    function psfRecordText(?\Illuminate\Database\Eloquent\Model $model, string $field): string
    {
        return $model ? psfText(psfRecordTexts($model, $field)) : '';
    }
}

if (!function_exists('psfBannerRatios')) {
    /**
     * Image size hints of the banner form. With the new design the banner
     * places differ, so the hints follow the design's own image sizes.
     */
    function psfBannerRatios(): array
    {
        $ratios = THEME_RATIO;
        if (psfDesign() === 'pixio') {
            $ratios['default']['Main Banner'] = '( 860 × 1044 px · PNG )';
            $ratios['default']['Main Section Banner'] = '( 1320 × 400 px )';
        }

        return $ratios;
    }
}

if (!function_exists('psfSiteTextKeys')) {
    /**
     * Storefront texts of the new design that PSF edits from its own panel
     * page (Paramètres PSF → site texts), grouped as shown there. They are
     * ordinary language keys, so Languages → Translate shows them too.
     *
     * @return array<string, array<int, string>>
     */
    function psfSiteTextKeys(): array
    {
        return [
            'Header_and_menu' => ['customer_support', 'search_for_products', 'browse_categories', 'nav_shop', 'nav_about_us', 'nav_realisations', 'nav_quote', 'lets_talk'],
            'Home_Page'       => ['home_categories_badge', 'home_trending_title', 'home_trending_text', 'home_video_badge', 'home_popular_title', 'home_brands_title', 'home_brands_badge', 'home_realisations_text', 'follow_us'],
            'Products'        => ['Contact_us_for_the_price', 'Request_the_price', 'Order_on_WhatsApp', 'On_Order'],
            'Footer'          => ['useful_links', 'quick_links', 'subscribe_to_our_newsletter', 'we_accept'],
        ];
    }
}

if (!function_exists('psfLanguageCacheKey')) {
    /**
     * Cache key for a list of products in the visitor's language.
     *
     * Products carry their translated name and description (loaded with
     * them), so a list cached once is only right for the language it was
     * built in: without this, whoever opens the page first decides the
     * language of every product name for three hours. The key is added to
     * the shop's language-wise key container, which is emptied whenever a
     * product, brand or category changes (cacheRemoveByType).
     */
    function psfLanguageCacheKey(string $key): string
    {
        $languageKey = $key . '_' . getDefaultLanguage();
        $keys = \Illuminate\Support\Facades\Cache::get(CACHE_CONTAINER_FOR_LANGUAGE_WISE_CACHE_KEYS, []);
        if (!in_array($languageKey, $keys, true)) {
            $keys[] = $languageKey;
            \Illuminate\Support\Facades\Cache::put(CACHE_CONTAINER_FOR_LANGUAGE_WISE_CACHE_KEYS, $keys, CACHE_FOR_3_HOURS);
        }

        return $languageKey;
    }
}

/*
 * ---------------------------------------------------------------
 * Shop pages (product lists) of the new design.
 * Settings from Paramètres PSF → New design content → Shop page.
 * ---------------------------------------------------------------
 */

if (!function_exists('psfShopSettings')) {
    /**
     * @return array{banner: ?string, view: string, per_page: array<int, int>, per_page_default: int}
     */
    function psfShopSettings(): array
    {
        static $settings = null;
        if ($settings !== null) {
            return $settings;
        }

        $stored = psfSettingArray('psf_shop_page');

        $perPage = array_values(array_unique(array_filter(
            array_map('intval', (array) ($stored['per_page'] ?? [])),
            fn ($value) => $value > 0 && $value <= 200
        )));
        if (count($perPage) === 0) {
            $perPage = PSF_SHOP_PER_PAGE_OPTIONS;
        }
        sort($perPage);

        $default = (int) ($stored['per_page_default'] ?? 0);
        if (!in_array($default, $perPage, true)) {
            $default = in_array(20, $perPage, true) ? 20 : $perPage[0];
        }

        $banner = trim((string) ($stored['banner'] ?? ''));

        return $settings = [
            'banner'           => $banner !== '' ? dynamicStorage(path: 'storage/app/public/psf-design/' . $banner) : null,
            'banner_file'      => $banner,
            'view'             => in_array($stored['view'] ?? '', PSF_SHOP_VIEWS, true) ? $stored['view'] : 'list',
            'per_page'         => $perPage,
            'per_page_default' => $default,
        ];
    }
}

if (!defined('PSF_SHOP_VIEWS')) {
    // list = one product per row, column = two per row, grid = three per row
    define('PSF_SHOP_VIEWS', ['list', 'column', 'grid']);
}
if (!defined('PSF_SHOP_PER_PAGE_OPTIONS')) {
    define('PSF_SHOP_PER_PAGE_OPTIONS', [12, 20, 40]);
}

if (!function_exists('psfProductsPerPage')) {
    /**
     * Products per page on the shop lists. The classic design keeps its 20.
     */
    function psfProductsPerPage(): int
    {
        if (psfDesign() !== 'pixio') {
            return 20;
        }

        $settings = psfShopSettings();
        $requested = (int) request('per_page');
        if (in_array($requested, $settings['per_page'], true)) {
            session()->put('psf_shop_per_page', $requested);
            return $requested;
        }

        $remembered = (int) session('psf_shop_per_page');
        return in_array($remembered, $settings['per_page'], true) ? $remembered : $settings['per_page_default'];
    }
}

if (!function_exists('psfShopView')) {
    /**
     * List / two columns / grid, as last picked by the visitor.
     */
    function psfShopView(): string
    {
        $requested = (string) request('view');
        if (in_array($requested, PSF_SHOP_VIEWS, true)) {
            session()->put('psf_shop_view', $requested);
            return $requested;
        }

        $remembered = (string) session('psf_shop_view');
        return in_array($remembered, PSF_SHOP_VIEWS, true) ? $remembered : psfShopSettings()['view'];
    }
}

if (!function_exists('psfShopFilterOptions')) {
    /**
     * What the shop sidebar can filter on, taken from the active products:
     * colours, attribute values (grouped by attribute) and product tags.
     * A group without values is not shown.
     *
     * @return array{colors: array<string, string>, attributes: array<string, array<int, string>>, tags: \Illuminate\Support\Collection}
     */
    function psfShopFilterOptions(): array
    {
        $colors = [];
        foreach (\App\Utils\ProductManager::getProductsColorsArray() as $code) {
            $colors[$code] = psfColorName($code);
        }

        $attributes = [];
        $choiceSets = \App\Models\Product::active()
            ->where('choice_options', '!=', '[]')
            ->whereNotNull('choice_options')
            ->pluck('choice_options');
        foreach ($choiceSets as $choiceJson) {
            foreach ((array) json_decode((string) $choiceJson, true) as $choice) {
                $title = trim((string) ($choice['title'] ?? ''));
                if ($title === '') {
                    continue;
                }
                foreach ((array) ($choice['options'] ?? []) as $option) {
                    $option = trim((string) $option);
                    if ($option !== '') {
                        $attributes[$title][$option] = $option;
                    }
                }
            }
        }
        foreach ($attributes as $title => $options) {
            natcasesort($options);
            $attributes[$title] = array_values($options);
        }

        $tags = \App\Models\Tag::whereHas('items', function ($query) {
            return $query->active();
        })->orderByDesc('visit_count')->orderBy('tag')->take(20)->get(['id', 'tag']);

        return compact('colors', 'attributes', 'tags');
    }
}

if (!function_exists('psfCurrencyFormat')) {
    /**
     * How the shop writes an amount, for scripts that format prices
     * themselves (price slider): symbol and on which side it goes.
     *
     * @return array{symbol: string, position: string, decimals: int}
     */
    function psfCurrencyFormat(): array
    {
        return [
            'symbol'   => (string) getCurrencySymbol(currencyCode: getCurrencyCode(type: 'web'), type: 'web'),
            'position' => getWebConfig(name: 'currency_symbol_position') === 'left' ? 'left' : 'right',
            'decimals' => (int) (getWebConfig(name: 'decimal_point_settings') ?? 0),
        ];
    }
}

if (!function_exists('psfTranslate')) {
    /**
     * translate() for a whole sentence with :placeholders. Sentences must be
     * one key: English translations get their first letter capitalised, so
     * "Showing" . "of" . "results" built from pieces reads "Showing 1 Of 2 Results".
     */
    function psfTranslate(string $key, array $replace = []): string
    {
        $text = (string) translate($key);
        // longest names first, so :to does not eat the start of :total
        uksort($replace, fn ($a, $b) => strlen((string) $b) <=> strlen((string) $a));
        foreach ($replace as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }
}

if (!function_exists('psfColorName')) {
    /**
     * Name of a product colour in the visitor's language. Colour names are
     * stored in English; they go through the language files like any text
     * (a missing one is added to Languages → Translate by translate()).
     */
    function psfColorName(string $code): string
    {
        $name = (string) (getColorNameByCode(code: $code) ?: '');

        return $name !== '' ? (string) translate($name) : $code;
    }
}
