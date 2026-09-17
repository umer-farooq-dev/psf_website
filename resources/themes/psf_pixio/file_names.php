<?php
/**
 * PSF — view map for the Pixio storefront.
 *
 * Controllers ask for views by key (VIEW_FILE_NAMES['home'] …). The keys and
 * relative paths are the same as the default theme's, so they are inherited
 * here; this theme folder simply provides its own file at the same path.
 * Anything not (yet) present here falls back to the default theme, because
 * ThemeServiceProvider registers both locations.
 *
 * A key only needs listing below if the new design uses a different path.
 */

$defaultMap = include __DIR__ . '/../default/file_names.php';

$overrides = [
];

return array_merge($defaultMap, $overrides);
